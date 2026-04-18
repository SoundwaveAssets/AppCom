<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\CartValidationService;
use App\Lib\StripeService;
use App\Models\Order;
use App\Models\OrderItem;
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

    public function validateCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
        ]);

        $result = $this->cartService->validate($validated['items']);

        if (! empty($result['errors'])) {
            return response()->json(['valid' => false, 'errors' => $result['errors']], 422);
        }

        return response()->json(['valid' => true, 'cart' => $result]);
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'customer_email' => 'required_without:guest_session_id|email',
            'guest_session_id' => 'nullable|string|max:120',
            'shipping_address' => 'required|array',
            'shipping_address.address' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:120',
            'shipping_address.phone' => 'required|string|max:30',
        ]);

        $cart = $this->cartService->validate($validated['items']);
        if (! empty($cart['errors'])) {
            return response()->json(['message' => 'Panier invalide', 'errors' => $cart['errors']], 422);
        }

        $user = $request->user();
        $customerId = $user ? $this->stripeService->getOrCreateCustomer($user) : null;

        $order = DB::transaction(function () use ($validated, $cart, $user) {
            $order = Order::create([
                'order_number' => 'CMD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'user_id' => $user?->id,
                'guest_session_id' => $validated['guest_session_id'] ?? null,
                'customer_email' => $user?->email ?? $validated['customer_email'],
                'total_amount' => $cart['total'],
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
            ]);

            foreach ($cart['items'] as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                ]);
            }

            return $order;
        });

        $intent = $this->stripeService->createPaymentIntent(
            amount: $cart['total'],
            customerId: $customerId,
            metadata: ['order_id' => (string) $order->id, 'order_number' => $order->order_number],
        );

        $order->update(['payment_intent_id' => $intent->id]);

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'client_secret' => $intent->client_secret,
            'total' => $cart['total'],
        ], 201);
    }
}
