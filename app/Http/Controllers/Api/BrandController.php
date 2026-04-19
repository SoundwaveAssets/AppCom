<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $brands
        ]);
    }

    /**
     * Display a public listing of brands.
     */
    public function publicIndex(): JsonResponse
    {
        $brands = Brand::orderBy('name')
            ->get();

        return response()->json([
            'data' => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
        ]);

        $brand = Brand::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        return response()->json([
            'data' => $brand,
            'message' => 'Marque créée avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $brand = Brand::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'data' => $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $brand = Brand::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $id,
        ]);

        $brand->update($validated);

        return response()->json([
            'data' => $brand,
            'message' => 'Marque mise à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $brand = Brand::where('user_id', Auth::id())
            ->findOrFail($id);

        $brand->delete();

        return response()->json([
            'message' => 'Marque supprimée avec succès'
        ]);
    }
}
