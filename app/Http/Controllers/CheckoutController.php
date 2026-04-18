<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartValidationService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private CartValidationService $cartService,
        private StripeService $stripeService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/checkout/validate-cart
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Valide le panier côté serveur et retourne le total recalculé.
     * Utilisé par Angular avant d'afficher la page de paiement.
     */
    public function validateCart(Request $request): JsonResponse
    {
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1|max:99',
        ]);

        $result = $this->cartService->validate($request->items);

        if (! empty($result['errors'])) {
            return response()->json([
                'valid'  => false,
                'errors' => $result['errors'],
            ], 422);
        }

        return response()->json([
            'valid'   => true,
            'total'   => $result['total'],
            'total_formatted' => number_format($result['total'], 0, ',', ' ') . ' XAF',
            'items'   => collect($result['items'])->map(fn ($i) => [
                'product_id'   => $i['product_id'],
                'product_name' => $i['product_name'],
                'unit_price'   => $i['unit_price'],
                'quantity'     => $i['quantity'],
                'line_total'   => $i['line_total'],
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/checkout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crée la commande et retourne le client_secret Stripe à Angular.
     *
     * Flux :
     *  1. Revalider le panier (prix récupérés depuis la BDD, jamais depuis le front)
     *  2. Obtenir / créer le customer Stripe si utilisateur connecté
     *  3. Créer la commande + les lignes en transaction DB
     *  4. Créer le Payment Intent Stripe
     *  5. Lier le payment_intent_id à la commande
     *  6. Retourner le client_secret à Angular (pour Stripe.js)
     */
    public function placeOrder(CheckoutRequest $request): JsonResponse
    {
        // ── Étape 1 : Revalider le panier (anti-fraude) ───────────────────
        $cartResult = $this->cartService->validate($request->items);

        if (! empty($cartResult['errors'])) {
            return response()->json([
                'message' => 'Votre panier contient des erreurs.',
                'errors'  => $cartResult['errors'],
            ], 422);
        }

        // ── Étape 2 : Identifier l'utilisateur (connecté ou invité) ──────
        $user       = $request->user(); // null si invité
        $customerId = null;

        if ($user) {
            // Utilisateur connecté : récupérer ou créer son customer Stripe
            // et sauvegarder le payment_customer_id sur le User si nouveau
            $customerId = $this->stripeService->getOrCreateCustomer($user);
        }

        // ── Étape 3 : Persistance en transaction ─────────────────────────
        /** @var Order $order */
        $order = DB::transaction(function () use ($request, $cartResult, $user) {
            $order = Order::create([
                'order_number'     => $this->generateOrderNumber(),
                'user_id'          => $user?->id,
                'customer_email'   => $user?->email ?? $request->customer_email,
                'total_amount'     => $cartResult['total'],
                'status'           => 'pending_payment',
                'shipping_address' => $request->shipping_address,
                'payment_intent_id' => null, // rempli après l'appel Stripe
            ]);

            foreach ($cartResult['items'] as $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit_price'   => $item['unit_price'],
                    'quantity'     => $item['quantity'],
                ]);
            }

            return $order;
        });

        // ── Étape 4 : Créer le Payment Intent Stripe ─────────────────────
        // (hors transaction pour ne pas bloquer la DB pendant l'appel réseau)
        $paymentIntent = $this->stripeService->createPaymentIntent(
            amount: $cartResult['total'],
            customerId: $customerId,
            metadata: [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]
        );

        // ── Étape 5 : Lier le Payment Intent à la commande ───────────────
        $order->update(['payment_intent_id' => $paymentIntent->id]);

        // ── Étape 6 : Répondre à Angular ─────────────────────────────────
        return response()->json([
            'message'       => 'Commande créée avec succès.',
            'order'         => new OrderResource($order->load('items')),
            'client_secret' => $paymentIntent->client_secret, // utilisé par Stripe.js côté Angular
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        // Format : CMD-20260417-A3F9KZ
        return 'CMD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}