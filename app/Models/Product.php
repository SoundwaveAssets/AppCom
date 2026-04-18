<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'price',
        'promo_price',
        'weight',
        'stock',
        'images',
        'technical_specs',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'promo_price' => 'integer',
        'weight' => 'float',
        'stock' => 'integer',
        'images' => 'array',
        'technical_specs' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}