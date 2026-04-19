<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CheckoutController extends Controller
{
    /**
     * Récupérer le contenu du panier
     */
    public function getCart(Request $request)
    {
        // Pour l'instant, retourner un panier vide
        // Dans une implémentation complète, cela récupérerait le panier depuis la session ou la base de données
        return response()->json([
            'data' => [
                'items' => [],
                'total' => 0,
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0
            ]
        ]);
    }

    /**
     * Valider le panier avant paiement
     */
    public function validateCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Vérifier les stocks disponibles
        // TODO: Calculer les totaux (prix, taxes, frais de livraison)
        
        return response()->json([
            'message' => 'Cart validated successfully',
            'total_amount' => 10000, // Exemple: 100.00 XAF
            'currency' => 'xaf'
        ]);
    }

    /**
     * Passer une commande
     */
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'payment_method_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Créer le PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => 10000, // 100.00 XAF en centimes
                'currency' => 'xaf',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'user_id' => auth()->id(),
                    'shipping_address' => $request->shipping_address,
                ],
            ]);

            // TODO: Sauvegarder la commande dans la base de données
            // TODO: Mettre à jour les stocks des produits

            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => 'ORD-' . uniqid(),
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
