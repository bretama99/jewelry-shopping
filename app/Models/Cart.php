<?php
// File: app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'weight',
        'price_per_gram',
        'gold_price',
        'karat',
        'subtotal',
        'product_snapshot'
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'price_per_gram' => 'decimal:2',
        'gold_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_snapshot' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSessionOrUser($query, $sessionId, $userId = null)
    {
        return $query->where(function ($q) use ($sessionId, $userId) {
            $q->where('session_id', $sessionId);
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    // Accessors & Mutators
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

    // Methods
    public function calculateSubtotal()
    {
        $this->subtotal = $this->weight * $this->price_per_gram;
        return $this->subtotal;
    }

    public function updateWeight($newWeight)
    {
        $this->weight = $newWeight;
        $this->calculateSubtotal();
        $this->save();
        return $this;
    }

    public function refreshPricing()
    {
        // Get current gold price and recalculate
        $kitcoService = app(\App\Services\KitcoApiService::class);
        $currentGoldPrice = $kitcoService->getCurrentGoldPrice();

        if ($currentGoldPrice && $this->product) {
            $this->gold_price = $currentGoldPrice;
            $this->price_per_gram = $this->product->calculatePricePerGram($currentGoldPrice);
            $this->calculateSubtotal();
            $this->save();
        }

        return $this;
    }

    // Static Methods
    public static function getCartTotal($sessionId, $userId = null)
    {
        return static::forSessionOrUser($sessionId, $userId)->sum('subtotal');
    }

    public static function getCartCount($sessionId, $userId = null)
    {
        return static::forSessionOrUser($sessionId, $userId)->count();
    }

    public static function getCartItems($sessionId, $userId = null)
    {
        return static::with(['product.category'])
            ->forSessionOrUser($sessionId, $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function addItem($productId, $weight, $sessionId, $userId = null)
    {
        $product = Product::findOrFail($productId);

        // Get current gold price
        $kitcoService = app(\App\Services\KitcoApiService::class);
        $goldPrice = $kitcoService->getCurrentGoldPrice();
        $pricePerGram = $product->calculatePricePerGram($goldPrice);

        // Check if item already exists in cart
        $existingItem = static::where('product_id', $productId)
            ->forSessionOrUser($sessionId, $userId)
            ->first();

        if ($existingItem) {
            // Update existing item
            $existingItem->weight += $weight;
            $existingItem->calculateSubtotal();
            $existingItem->save();
            return $existingItem;
        }

        // Create new cart item
        return static::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'product_id' => $productId,
            'weight' => $weight,
            'price_per_gram' => $pricePerGram,
            'gold_price' => $goldPrice,
            'karat' => $product->karat,
            'subtotal' => $weight * $pricePerGram,
            'product_snapshot' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->image_path,
                'category' => $product->category->name ?? 'Unknown',
                'features' => $product->features,
                'description' => $product->description
            ]
        ]);
    }

    public static function clearCart($sessionId, $userId = null)
    {
        return static::forSessionOrUser($sessionId, $userId)->delete();
    }

    public static function transferGuestCartToUser($sessionId, $userId)
    {
        return static::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}
