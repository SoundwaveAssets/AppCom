<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;

class WebhookController extends Controller
{
    public function __construct(private StripeService $stripeService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/webhooks/stripe
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Point d'entrée unique pour les événements Stripe.
     *
     * IMPORTANT :
     *  - Cette route doit être exclue du middleware CSRF (voir VerifyCsrfToken)
     *  - Cette route ne doit PAS avoir le middleware auth.firebase
     *  - Stripe a besoin du corps brut (raw body) pour vérifier la signature
     */
    public function handle(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // ── Étape 1 : Vérification de la signature (sécurité critique) ───
        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (SignatureVerificationException $e) {
            Log::warning('[Stripe Webhook] Signature invalide.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Signature invalide.'], 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('[Stripe Webhook] Payload invalide.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Payload invalide.'], 400);
        }

        // ── Étape 2 : Dispatch par type d'événement ───────────────────────
        match ($event->type) {
            'payment_intent.succeeded'      => $this->onPaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->onPaymentFailed($event->data->object),
            // Ajoutez d'autres événements ici si nécessaire (remboursements, disputes…)
            default => Log::info("[Stripe Webhook] Événement ignoré : {$event->type}"),
        };

        // Stripe attend toujours un 200 pour confirmer la réception
        return response()->json(['received' => true], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Handlers privés
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Paiement réussi → passe la commande en "paid" et décrémente les stocks.
     */
    private function onPaymentSucceeded(PaymentIntent $paymentIntent): void
    {
        $order = Order::where('payment_intent_id', $paymentIntent->id)
            ->with('items')
            ->first();

        if (! $order) {
            Log::error(
                '[Stripe Webhook] Commande introuvable.',
                ['payment_intent_id' => $paymentIntent->id]
            );
            return;
        }

        // Idempotence : si déjà traitée, on ne fait rien
        // (Stripe peut renvoyer le même événement plusieurs fois)
        if ($order->status === 'paid') {
            Log::info("[Stripe Webhook] Commande {$order->order_number} déjà payée. Ignoré.");
            return;
        }

        // Transaction atomique : la commande ne passe "paid" que si
        // tous les stocks sont décrémentés avec succès
        DB::transaction(function () use ($order) {
            $order->update(['status' => 'paid']);

            foreach ($order->items as $item) {
                // La clause WHERE stock >= quantity évite un stock négatif
                $updated = Product::where('id', $item->product_id)
                    ->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);

                if (! $updated) {
                    Log::warning(
                        "[Stripe Webhook] Stock insuffisant lors de la décrémentation.",
                        [
                            'order_number' => $order->order_number,
                            'product_id'   => $item->product_id,
                            'quantity'     => $item->quantity,
                        ]
                    );
                }
            }
        });

        Log::info(
            "[Stripe Webhook] Commande {$order->order_number} payée. Stocks mis à jour."
        );
    }

    /**
     * Paiement échoué → passe la commande en "payment_failed".
     */
    private function onPaymentFailed(PaymentIntent $paymentIntent): void
    {
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if (! $order) {
            return;
        }

        $order->update(['status' => 'payment_failed']);

        Log::warning(
            "[Stripe Webhook] Paiement échoué pour la commande {$order->order_number}."
        );
    }
}