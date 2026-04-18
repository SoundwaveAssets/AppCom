<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json(Brand::all());
    }

    public function store(Request $request)
    {
        if (Auth::id() !== 1) {
            return response()->json(['message' => 'Interdit'], 403);
        }

        $request->validate(['name' => 'required|string|unique:brands']);

        $brand = Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json($brand, 201);
    }

    public function destroy($id)
    {
        if (Auth::id() !== 1) {
            return response()->json(['message' => 'Interdit'], 403);
        }

        Brand::findOrFail($id)->delete();
        return response()->json(['message' => 'Supprimé']);
    }
}