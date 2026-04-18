<?php

namespace App\Services;

use App\Models\User;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Gestion des Customers Stripe
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Récupère le customer_id Stripe d'un utilisateur enregistré,
     * ou en crée un nouveau et le persiste sur l'utilisateur.
     */
    public function getOrCreateCustomer(User $user): string
    {
        if ($user->payment_customer_id) {
            return $user->payment_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email'    => $user->email,
            'name'     => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        // Sauvegarde du customer_id dans le profil utilisateur
        $user->update(['payment_customer_id' => $customer->id]);

        return $customer->id;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Payment Intent
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crée un Payment Intent Stripe.
     *
     * @param  int         $amount      Montant en XAF (devise zéro-décimale, pas de centimes)
     * @param  string|null $customerId  Customer Stripe (null = invité)
     * @param  array       $metadata    Métadonnées (order_id, order_number…)
     */
    public function createPaymentIntent(
        int $amount,
        ?string $customerId = null,
        array $metadata = []
    ): PaymentIntent {
        $params = [
            'amount'                     => $amount,
            'currency'                   => config('services.stripe.currency', 'xaf'),
            'automatic_payment_methods'  => ['enabled' => true],
            'metadata'                   => $metadata,
        ];

        if ($customerId) {
            $params['customer']          = $customerId;
            // Permet d'enregistrer la carte pour les paiements futurs
            $params['setup_future_usage'] = 'off_session';
        }

        return $this->stripe->paymentIntents->create($params);
    }

    /**
     * Récupère un Payment Intent existant.
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Webhook
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Vérifie la signature du webhook et retourne l'événement Stripe.
     *
     * @throws SignatureVerificationException
     * @throws \UnexpectedValueException
     */
    public function constructWebhookEvent(string $rawPayload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent(
            $rawPayload,
            $signature,
            config('services.stripe.webhook_secret')
        );
    }
}