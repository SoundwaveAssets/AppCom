<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\FirebaseStorageService;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function __construct(private FirebaseStorageService $firebaseStorageService) {}

    public function index(): JsonResponse
    {
        return response()->json(Product::query()->with(['category', 'brand'])->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        $product = Product::create($data);
        return response()->json(['product' => $product], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(['product' => $product->load(['category', 'brand'])]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $this->validated($request, true);
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        }
        $product->update($data);
        return response()->json(['product' => $product->fresh()]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Produit supprime']);
    }

    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate(['stock' => 'required|integer|min:0']);
        $product->update(['stock' => $data['stock']]);
        return response()->json(['product' => $product->fresh()]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $url = $this->firebaseStorageService->uploadProductImage($validated['image']);

        return response()->json(['url' => $url], 201);
    }

    private function validated(Request $request, bool $isUpdate = false): array
    {
        $rule = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'category_id' => "{$rule}|integer|exists:categories,id",
            'brand_id' => 'nullable|integer|exists:brands,id',
            'name' => "{$rule}|string|max:180",
            'description' => "{$rule}|string",
            'price' => "{$rule}|integer|min:0",
            'promo_price' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'stock' => "{$rule}|integer|min:0",
            'technical_specs' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'is_active' => 'boolean',
        ]);
    }
}
