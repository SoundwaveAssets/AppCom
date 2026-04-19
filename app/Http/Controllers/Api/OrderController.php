<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Lister les commandes du client connecté
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Afficher une commande spécifique du client
     */
    public function show(Order $order)
    {
        // Vérifier que la commande appartient bien à l'utilisateur connecté
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'order' => $order->load('items.product')
        ]);
    }

    /**
     * Lister toutes les commandes (admin)
     */
    public function adminIndex(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $orders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Mettre à jour le statut d'une commande (admin)
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->fresh()
        ]);
    }
}
