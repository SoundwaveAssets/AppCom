<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Panier;
use App\Models\Product;
use App\Models\User;

class PanierController extends Controller
{
    public function create()
    {
        // Récupérer les produits sélectionnés depuis la requête
        $selectedProducts = request()->input('products', []);
        
        if (empty($selectedProducts)) {
            return redirect()->route('client.home')->with('error', 'Veuillez sélectionner au moins un produit');
        }
        
        $products = Product::whereIn('id', $selectedProducts)->get();
        
        return view('panier.create', compact('products'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
            'payment_mode' => 'required|string',
            'card_number' => 'required|string|min:16|max:19',
        ]);
        
        $user = auth()->user();
        
        foreach ($request->quantities as $productId => $quantity) {
            $product = Product::findOrFail($productId);
            
            // Vérifier que la quantité demandée ne dépasse pas le stock
            if ($quantity > $product->stock) {
                return back()->with('error', "La quantité demandée pour {$product->name} dépasse le stock disponible ({$product->stock})");
            }
            
            Panier::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'user_name' => $user->name,
                'product_name' => $product->name,
                'product_brand' => $product->brand ? $product->brand->name : null,
                'product_stock' => $product->stock,
                'product_price' => $product->price,
                'quantity' => $quantity,
                'payment_mode' => $request->payment_mode,
                'card_number' => str_replace(' ', '', $request->card_number),
                'client_type' => 'anonyme',
            ]);
            
            // Mettre à jour le stock du produit
            $product->stock -= $quantity;
            $product->save();
        }
        
        return redirect()->route('client.home')->with('success', 'Panier créé avec succès! Les produits ont été retirés du stock.');
    }
    
    public function index()
    {
        $paniers = Panier::where('user_id', auth()->id())->with('product')->latest()->get();
        
        return view('panier.index', compact('paniers'));
    }
    
    public function edit($id)
    {
        $panier = Panier::where('user_id', auth()->id())->findOrFail($id);
        
        return view('panier.edit', compact('panier'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'payment_mode' => 'required|string',
            'card_number' => 'required|string|min:16|max:19',
        ]);
        
        $panier = Panier::where('user_id', auth()->id())->findOrFail($id);
        
        // Calculer la différence de quantité pour ajuster le stock
        $oldQuantity = $panier->quantity;
        $newQuantity = $request->quantity;
        $quantityDiff = $newQuantity - $oldQuantity;
        
        // Vérifier que la nouvelle quantité ne dépasse pas le stock disponible
        $currentStock = $panier->product->stock + $oldQuantity; // Stock actuel + quantité précédente
        if ($newQuantity > $currentStock) {
            return back()->with('error', "La quantité demandée ({$newQuantity}) dépasse le stock disponible ({$currentStock})");
        }
        
        // Mettre à jour le panier
        $panier->update([
            'quantity' => $newQuantity,
            'payment_mode' => $request->payment_mode,
            'card_number' => str_replace(' ', '', $request->card_number),
        ]);
        
        // Ajuster le stock du produit
        $panier->product->stock -= $quantityDiff;
        $panier->product->save();
        
        return redirect()->route('panier.index')->with('success', 'Panier mis à jour avec succès!');
    }
    
    public function destroy($id)
    {
        $panier = Panier::where('user_id', auth()->id())->findOrFail($id);
        
        // Remettre la quantité en stock
        $panier->product->stock += $panier->quantity;
        $panier->product->save();
        
        $panier->delete();
        
        return redirect()->route('panier.index')->with('success', 'Panier supprimé avec succès!');
    }
}
