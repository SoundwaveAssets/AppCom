<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dev 3 — Routes : Checkout, Commandes & Paiement
|--------------------------------------------------------------------------
|
| À coller dans routes/api.php de votre projet Laravel.
|
| Dépendances :
|   - Middleware 'auth.firebase'   → implémenté par le Dev 1
|   - Package Stripe               → composer require stripe/stripe-php
|
| Variables d'environnement (.env) à ajouter :
|   STRIPE_SECRET=sk_live_...
|   STRIPE_WEBHOOK_SECRET=whsec_...
|   STRIPE_CURRENCY=xaf
|
| Ajout dans config/services.php :
|   'stripe' => [
|       'secret'         => env('STRIPE_SECRET'),
|       'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
|       'currency'       => env('STRIPE_CURRENCY', 'xaf'),
|   ],
|
| Exclure le webhook du CSRF dans app/Http/Middleware/VerifyCsrfToken.php :
|   protected $except = ['api/webhooks/stripe'];
|
*/

// ─────────────────────────────────────────────────────────────────────────────
// Webhook Stripe
// ⚠  DOIT rester HORS de tout middleware (auth, throttle, etc.)
//    Stripe envoie le corps brut — ne pas modifier le body
// ─────────────────────────────────────────────────────────────────────────────
Route::post('/webhooks/stripe', [WebhookController::class, 'handle'])
    ->name('webhooks.stripe');

// ─────────────────────────────────────────────────────────────────────────────
// Checkout (invités ET utilisateurs connectés)
// ─────────────────────────────────────────────────────────────────────────────
Route::post('/checkout/validate-cart', [CheckoutController::class, 'validateCart'])
    ->name('checkout.validate-cart');

Route::post('/checkout', [CheckoutController::class, 'placeOrder'])
    ->name('checkout.place-order');

// ─────────────────────────────────────────────────────────────────────────────
// Commandes — Utilisateurs authentifiés uniquement
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth.firebase')->group(function () {

    // ── Client : consultation de ses propres commandes ───────────────────
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');

    // ── Admin : gestion de toutes les commandes ───────────────────────────
    // La vérification du rôle 'admin' est faite dans le controller / FormRequest
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/orders', [OrderController::class, 'adminIndex'])
            ->name('orders.index');

        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.update-status');
    });
});