<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'is_active' => 'boolean',
        ]);

        Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => $request->slug,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Catégorie créée avec succès!');
    }

    public function createBrand()
    {
        return view('admin.brands.create');
    }

    public function storeBrand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands',
        ]);

        Brand::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Marque créée avec succès!');
    }

    public function createProduct()
    {
        $categories = Category::where('is_active', true)->where('user_id', auth()->id())->get();
        $brands = Brand::where('user_id', auth()->id())->get();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'warranty' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $imagePath = 'images/products/' . $imageName;
        }

        Product::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'images' => $imagePath,
            'warranty' => $request->warranty,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Produit créé avec succès!');
    }

    public function products(Request $request)
    {
        $query = Product::where('user_id', auth()->id())
            ->with(['category', 'brand']);

        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtre par marque
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->latest()->get();
        $categories = Category::where('user_id', auth()->id())->where('is_active', true)->get();
        $brands = Brand::where('user_id', auth()->id())->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function editProduct($id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);
        $categories = Category::where('is_active', true)->where('user_id', auth()->id())->get();
        $brands = Brand::where('user_id', auth()->id())->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $id,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'warranty' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        // Gérer l'upload d'image si une nouvelle image est fournie
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->images && file_exists(public_path($product->images))) {
                unlink(public_path($product->images));
            }
            
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $imagePath = 'images/products/' . $imageName;
            $product->images = $imagePath;
        }

        $product->update([
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'warranty' => $request->warranty,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour avec succès!');
    }

    public function deleteProduct($id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);
        
        // Supprimer l'image si elle existe
        if ($product->images && file_exists(public_path($product->images))) {
            unlink(public_path($product->images));
        }
        
        $product->delete();
        
        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé avec succès!');
    }
}
