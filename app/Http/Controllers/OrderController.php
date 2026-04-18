<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Routes CLIENT (auth.firebase requis)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/orders
     * Liste les commandes de l'utilisateur connecté.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/orders/{order}
     * Détail d'une commande.
     * Un client ne peut voir que ses propres commandes.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Vérification d'appartenance (le middleware auth garantit $user non null)
        if ($order->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json(new OrderResource($order->load('items')));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Routes ADMIN (auth.firebase + role admin requis)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/orders
     * Liste toutes les commandes avec filtres.
     *
     * Query params disponibles :
     *   ?status=paid
     *   ?from=2026-01-01&to=2026-04-30
     *   ?search=CMD-20260417
     */
    public function adminIndex(Request $request): AnonymousResourceCollection
    {
        $this->authorizeAdmin($request);

        $query = Order::with(['user', 'items'])->latest();

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par plage de dates
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Recherche par numéro de commande ou e-mail
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        return OrderResource::collection($query->paginate(20));
    }

    /**
     * PATCH /api/admin/orders/{order}/status
     * Mise à jour du statut d'une commande (admin uniquement).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        // L'autorisation admin est déjà vérifiée dans UpdateOrderStatusRequest::authorize()
        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'order'   => new OrderResource($order->load('items')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function authorizeAdmin(Request $request): void
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs.');
        }
    }
}