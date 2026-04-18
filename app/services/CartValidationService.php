<?php

namespace App\Services;

use App\Models\Product;

class CartValidationService
{
    /**
     * Valide les articles du panier contre la base de données
     * et recalcule le total côté serveur (anti-fraude).
     *
     * @param  array  $items  [['product_id' => 1, 'quantity' => 2], ...]
     * @return array  ['items' => array, 'total' => int, 'errors' => array]
     */
    public function validate(array $items): array
    {
        $errors        = [];
        $validatedItems = [];
        $total         = 0;

        // Récupération groupée pour éviter les N+1 queries
        $productIds = collect($items)->pluck('product_id')->unique()->values()->toArray();

        $products = Product::whereIn('id', $productIds)
            ->where('is_published', true)
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity  = (int) $item['quantity'];

            // Produit introuvable ou non publié
            if (! isset($products[$productId])) {
                $errors[] = "Le produit ID {$productId} est introuvable ou indisponible.";
                continue;
            }

            $product = $products[$productId];

            // Stock insuffisant
            if ($product->stock < $quantity) {
                $errors[] = "Stock insuffisant pour « {$product->name} ». "
                    . "Stock disponible : {$product->stock}.";
                continue;
            }

            // Calcul du sous-total (les prix sont des entiers : XAF n'a pas de centimes)
            $lineTotal = $product->price * $quantity;
            $total    += $lineTotal;

            $validatedItems[] = [
                'product'      => $product,
                'product_id'   => $productId,
                'product_name' => $product->name,
                'unit_price'   => $product->price,
                'quantity'     => $quantity,
                'line_total'   => $lineTotal,
            ];
        }

        return [
            'items'  => $validatedItems,
            'total'  => $total,
            'errors' => $errors,
        ];
    }
}