<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'brand'])
            ->where('is_active', true);

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', (int) $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', (int) $request->price_max);
        }

        return response()->json($query->latest()->paginate(min($request->integer('per_page', 20), 100)));
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'brand']);

        $similar = Product::query()
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->where('category_id', $product->category_id)
                ->orWhere('brand_id', $product->brand_id))
            ->limit(8)
            ->get();

        return response()->json([
            'product' => $product,
            'similar_products' => $similar,
        ]);
    }
}
