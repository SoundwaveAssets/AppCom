<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);
        $order = Order::findOrFail($request->order_id);

        // Appel direct via namespace pour éviter les bugs d'IDE
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $order->total_amount,
                'currency' => 'xaf',
                'metadata' => ['order_id' => $order->id]
            ]);

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}