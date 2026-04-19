<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = Product::where('user_id', Auth::id())
            ->with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * Display a public listing of products.
     */
    public function publicIndex(): JsonResponse
    {
        $products = Product::where('is_published', true)
            ->with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * Display admin's products with pagination and filtering.
     */
    public function myProductsWithPagination(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        $query = Product::where('user_id', $userId)
            ->with(['category', 'brand']);

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category_id') && $request->input('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Brand filter
        if ($request->has('brand_id') && $request->input('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        // Published status filter
        if ($request->has('published') && $request->input('published')) {
            $isPublished = $request->input('published') === 'published';
            $query->where('is_published', $isPublished);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSortFields = ['name', 'price', 'stock', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        
        $products = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->where('user_id', $userId)
            ],
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => [
                'required',
                'exists:categories,id',
                Rule::exists('categories', 'id')->where('user_id', $userId)
            ],
            'brand_id' => [
                'nullable',
                'exists:brands,id',
                Rule::exists('brands', 'id')->where('user_id', $userId)
            ],
            'is_published' => 'boolean',
            'images' => 'nullable|string',
            'technical_specs' => 'nullable|string',
            'weight' => 'nullable|string',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'material' => 'nullable|string',
            'warranty' => 'nullable|string',
        ]);

        $product = Product::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'] ?? null,
            'is_published' => $validated['is_published'] ?? true,
            'images' => $validated['images'] ?? null,
            'technical_specs' => $validated['technical_specs'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'color' => $validated['color'] ?? null,
            'size' => $validated['size'] ?? null,
            'material' => $validated['material'] ?? null,
            'warranty' => $validated['warranty'] ?? null,
        ]);

        return response()->json([
            'data' => $product->load(['category', 'brand']),
            'message' => 'Produit créé avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::where('user_id', Auth::id())
            ->with(['category', 'brand'])
            ->findOrFail($id);

        return response()->json([
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $userId = Auth::id();
        $product = Product::where('user_id', $userId)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $id,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => [
                'required',
                'exists:categories,id',
                Rule::exists('categories', 'id')->where('user_id', $userId)
            ],
            'brand_id' => [
                'nullable',
                'exists:brands,id',
                Rule::exists('brands', 'id')->where('user_id', $userId)
            ],
            'is_published' => 'boolean',
            'images' => 'nullable|string',
            'technical_specs' => 'nullable|string',
            'weight' => 'nullable|string',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'material' => 'nullable|string',
            'warranty' => 'nullable|string',
        ]);

        $product->update($validated);

        return response()->json([
            'data' => $product->load(['category', 'brand']),
            'message' => 'Produit mis à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::where('user_id', Auth::id())
            ->findOrFail($id);

        $product->delete();

        return response()->json([
            'message' => 'Produit supprimé avec succès'
        ]);
    }
}
