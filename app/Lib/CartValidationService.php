<?php

namespace App\Lib;

use App\Models\Product;

class CartValidationService
{
    public function validate(array $items): array
    {
        $errors = [];
        $validatedItems = [];
        $total = 0;

        $productIds = collect($items)->pluck('product_id')->unique()->values()->toArray();
        $products = Product::query()->whereIn('id', $productIds)->where('is_active', true)->get()->keyBy('id');

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];

            if (! isset($products[$productId])) {
                $errors[] = "Produit {$productId} indisponible.";
                continue;
            }

            $product = $products[$productId];
            if ($product->stock < $quantity) {
                $errors[] = "Stock insuffisant pour {$product->name}.";
                continue;
            }

            $unitPrice = $product->promo_price ?: $product->price;
            $lineTotal = $unitPrice * $quantity;
            $total += $lineTotal;

            $validatedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        return ['items' => $validatedItems, 'total' => $total, 'errors' => $errors];
    }
}
