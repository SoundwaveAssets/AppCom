<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Display a public listing of categories.
     */
    public function publicIndex(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'is_active' => 'boolean',
        ]);

        $category = Category::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'data' => $category,
            'message' => 'Catégorie créée avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'data' => $category,
            'message' => 'Catégorie mise à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::where('user_id', Auth::id())
            ->findOrFail($id);

        $category->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }
}
