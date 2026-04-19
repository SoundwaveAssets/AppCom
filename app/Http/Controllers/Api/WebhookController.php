<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\Stripe;

class WebhookController extends Controller
{
    /**
     * Gère les webhooks Stripe
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Traiter l'événement selon son type
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
            default:
                Log::info('Unhandled webhook event type', ['type' => $event->type]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Gère le succès d'un paiement
     */
    private function handlePaymentSucceeded($paymentIntent)
    {
        Log::info('Payment succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency
        ]);

        // TODO: Mettre à jour le statut de la commande dans la base de données
        // TODO: Envoyer un email de confirmation au client
    }

    /**
     * Gère l'échec d'un paiement
     */
    private function handlePaymentFailed($paymentIntent)
    {
        Log::error('Payment failed', [
            'payment_intent_id' => $paymentIntent->id,
            'error' => $paymentIntent->last_payment_error
        ]);

        // TODO: Mettre à jour le statut de la commande dans la base de données
        // TODO: Envoyer un email d'échec au client
    }
}
