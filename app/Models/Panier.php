<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Panier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'product_name',
        'product_brand',
        'product_stock',
        'product_price',
        'quantity',
        'payment_mode',
        'card_number',
        'user_id',
        'product_id',
        'client_type',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'product_stock' => 'integer',
        'quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
