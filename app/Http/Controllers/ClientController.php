<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;

class ClientController extends Controller
{
    public function home(Request $request)
    {
        $query = Product::where('is_published', true)
            ->with(['category', 'brand', 'user']);

        // Recherche par nom de produit
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Recherche par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Recherche par marque
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Recherche par nom de l'admin
        if ($request->filled('admin_name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->admin_name . '%')
                  ->where('role', 'admin');
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'price', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $products = $query->latest()->get();
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::all();
        $admins = User::where('role', 'admin')->get();

        return view('client.home', compact('products', 'categories', 'brands', 'admins'));
    }
}
