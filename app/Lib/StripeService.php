<?php

namespace App\Lib;

use App\Models\User;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient((string) config('services.stripe.secret'));
    }

    public function getOrCreateCustomer(User $user): string
    {
        if ($user->payment_customer_id) {
            return $user->payment_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);
        $user->update(['payment_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function createPaymentIntent(int $amount, ?string $customerId = null, array $metadata = []): PaymentIntent
    {
        $payload = [
            'amount' => $amount,
            'currency' => config('services.stripe.currency', 'xaf'),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => $metadata,
        ];

        if ($customerId) {
            $payload['customer'] = $customerId;
            $payload['setup_future_usage'] = 'off_session';
        }

        return $this->stripe->paymentIntents->create($payload);
    }

    public function constructWebhookEvent(string $rawPayload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent($rawPayload, $signature, (string) config('services.stripe.webhook_secret'));
    }
}
