<?php
// File: app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'karat',
        'category_name',
        'product_description',
        'product_image',
        'weight',
        'price_per_gram',
        'gold_price',
        'labor_cost',
        'profit_margin',
        'subtotal',
        'product_features',
        'pricing_breakdown',
        'special_instructions'
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'price_per_gram' => 'decimal:2',
        'gold_price' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_features' => 'array',
        'pricing_breakdown' => 'array'
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFormattedWeightAttribute()
    {
        return number_format($this->weight, 3) . 'g';
    }

    public function getFormattedSubtotalAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedPricePerGramAttribute()
    {
        return '$' . number_format($this->price_per_gram, 2);
    }

    public function getFormattedGoldPriceAttribute()
    {
        return '$' . number_format($this->gold_price, 2);
    }

    public function getProductImageUrlAttribute()
    {
        if ($this->product_image) {
            return asset('storage/' . $this->product_image);
        }
        return asset('images/product-placeholder.jpg');
    }

    public function getKaratLabelAttribute()
    {
        return $this->karat . ' Gold';
    }

    public function getKaratPurityAttribute()
    {
        return match($this->karat) {
            '10K' => 0.417,
            '14K' => 0.583,
            '18K' => 0.750,
            '22K' => 0.917,
            '24K' => 1.000,
            default => 0.583 // Default to 14K
        };
    }

    // Methods
    public function calculateSubtotal()
    {
        return $this->weight * $this->price_per_gram;
    }

    public function getPriceBreakdown()
    {
        return [
            'base_gold_value' => $this->weight * $this->gold_price * $this->karat_purity,
            'labor_cost' => $this->weight * $this->labor_cost,
            'profit_margin' => ($this->weight * $this->gold_price * $this->karat_purity + $this->weight * $this->labor_cost) * ($this->profit_margin / 100),
            'total' => $this->subtotal
        ];
    }

    public function getEstimatedMeltValue()
    {
        // Get current gold price for comparison
        $kitcoService = app(\App\Services\KitcoApiService::class);
        $currentGoldPrice = $kitcoService->getCurrentGoldPrice();

        return $this->weight * $currentGoldPrice * $this->karat_purity;
    }

    public function isCustomWeight()
    {
        // Check if weight is different from product's standard weight
        return $this->product &&
               $this->weight != $this->product->weight_min &&
               $this->weight != $this->product->weight_max;
    }

    // Scopes
    public function scopeByKarat($query, $karat)
    {
        return $query->where('karat', $karat);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category_name', $category);
    }

    public function scopeHighValue($query, $threshold = 1000)
    {
        return $query->where('subtotal', '>=', $threshold);
    }

    public function scopeCustomWeights($query)
    {
        return $query->whereRaw('weight NOT BETWEEN
            (SELECT weight_min FROM products WHERE products.id = order_items.product_id)
            AND
            (SELECT weight_max FROM products WHERE products.id = order_items.product_id)'
        );
    }
}
