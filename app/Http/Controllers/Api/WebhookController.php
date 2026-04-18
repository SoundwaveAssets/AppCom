<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\StripeService;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;

class WebhookController extends Controller
{
    public function __construct(private StripeService $stripeService) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $event = $this->stripeService->constructWebhookEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature')
            );
        } catch (SignatureVerificationException|\UnexpectedValueException) {
            return response()->json(['message' => 'Invalid webhook signature'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $this->onPaymentSucceeded($event->data->object);
        } elseif ($event->type === 'payment_intent.payment_failed') {
            $this->onPaymentFailed($event->data->object);
        }

        return response()->json(['received' => true]);
    }

    private function onPaymentSucceeded(PaymentIntent $intent): void
    {
        $order = Order::query()->where('payment_intent_id', $intent->id)->with('items')->first();
        if (! $order || $order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'paid']);
            foreach ($order->items as $item) {
                Product::query()
                    ->where('id', $item->product_id)
                    ->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);
            }
        });
    }

    private function onPaymentFailed(PaymentIntent $intent): void
    {
        Order::query()->where('payment_intent_id', $intent->id)->update(['status' => 'cancelled']);
    }
}
