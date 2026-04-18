<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $monthRevenue = (int) Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        $pendingCount = Order::query()->where('status', 'pending')->count();

        $topProducts = OrderItem::query()
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $criticalStock = Product::query()->where('stock', '<=', 5)->orderBy('stock')->limit(20)->get();

        return response()->json([
            'month_revenue' => $monthRevenue,
            'pending_orders' => $pendingCount,
            'top_products' => $topProducts,
            'critical_stock' => $criticalStock,
        ]);
    }
}
