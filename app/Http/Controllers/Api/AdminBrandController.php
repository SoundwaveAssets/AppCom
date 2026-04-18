<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBrandController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Brand::query()->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'is_active' => 'boolean']);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $brand = Brand::create($data);
        return response()->json(['brand' => $brand], 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json(['brand' => $brand]);
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string|max:120', 'is_active' => 'boolean']);
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        }
        $brand->update($data);
        return response()->json(['brand' => $brand->fresh()]);
    }

    public function toggleActive(Brand $brand): JsonResponse
    {
        $brand->update(['is_active' => ! $brand->is_active]);
        return response()->json(['brand' => $brand->fresh()]);
    }
}
