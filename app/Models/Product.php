<?php
// File: app/Models/Product.php - Complete updated version
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sku',
        'metal_category_id',
        'subcategory_id',
        'weight', // Base weight in grams - now optional
        'min_weight',
        'max_weight',
        'weight_step',
        'karat', // Karat/purity grade - now optional
        'labor_cost', // Override labor cost
        'profit_margin', // Override profit margin
        'stock_quantity', // Now optional
        'min_stock_level', // Now optional
        'is_active',
        'is_featured',
        'image',
        'gallery', // JSON field for multiple images
        'meta_title',
        'meta_description',
        'tags', // JSON field for product tags
        'sort_order'
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'min_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'weight_step' => 'decimal:3',
        'labor_cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'gallery' => 'array',
        'tags' => 'array',
        'sort_order' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'formatted_weight',
        'metal_name',
        'karat_display',
        'image_url'
    ];

    // Relationships

    public function metalCategory()
    {
        return $this->belongsTo(MetalCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Boot method for auto-generating slug and SKU
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = $product->generateUniqueSlug($product->name);
            }

            if (empty($product->sku)) {
                $product->sku = $product->generateUniqueSku();
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = $product->generateUniqueSlug($product->name);
            }
        });
    }

    // Accessors
    public function getFormattedWeightAttribute()
    {
        return $this->weight ? number_format($this->weight, 2) . 'g' : 'Variable';
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path('images/products/' . $this->image))) {
            return asset('images/products/' . $this->image);
        }
        return asset('images/no-image.png');
    }

    public function getGalleryUrlsAttribute()
    {
        if ($this->gallery && is_array($this->gallery)) {
            return array_map(function($image) {
                return asset('images/products/' . $image);
            }, $this->gallery);
        }
        return [];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByMetal($query, $metalSlug)
    {
        return $query->whereHas('metalCategory', function($q) use ($metalSlug) {
            $q->where('slug', $metalSlug);
        });
    }

    public function scopeBySubcategory($query, $subcategorySlug)
    {
        return $query->whereHas('subcategory', function($q) use ($subcategorySlug) {
            $q->where('slug', $subcategorySlug);
        });
    }

    public function scopeByKarat($query, $karat)
    {
        return $query->where('karat', $karat);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
              ->orWhere('tags', 'LIKE', "%{$searchTerm}%");
        });
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level')
                    ->whereNotNull('stock_quantity')
                    ->whereNotNull('min_stock_level');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helper methods
    public function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function generateUniqueSku()
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return 'AUD' . number_format($this->calculateLivePrice(), 2);
    }

    /**
     * Check if weight is within allowed range (only if weight limits are set)
     */
    public function isValidWeight($weight)
    {
        if (!$this->min_weight || !$this->max_weight) {
            return true; // No limits set
        }
        return $weight >= $this->min_weight && $weight <= $this->max_weight;
    }

    /**
     * Check stock availability (only if stock tracking is enabled)
     */
    public function isInStock($quantity = 1)
    {
        if (is_null($this->stock_quantity)) {
            return true; // No stock tracking
        }
        return $this->stock_quantity >= $quantity;
    }

    public function isLowStock()
    {
        if (is_null($this->stock_quantity) || is_null($this->min_stock_level)) {
            return false; // No stock tracking
        }
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function decrementStock($quantity)
    {
        if (is_null($this->stock_quantity)) {
            return true; // No stock tracking
        }
        
        if ($this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    public function incrementStock($quantity)
    {
        if (is_null($this->stock_quantity)) {
            $this->stock_quantity = $quantity;
        } else {
            $this->increment('stock_quantity', $quantity);
        }
        return true;
    }

    // SEO helpers
    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->name . ' - ' . $this->karat_display . ' ' . $this->metal_name;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: Str::limit($this->description ?:
            "Beautiful {$this->karat_display} {$this->metal_name} {$this->subcategory?->name} weighing {$this->formatted_weight}.", 160);
    }

    /**
     * Duplicate product
     */
    public function duplicate()
    {
        $newProduct = $this->replicate();
        $newProduct->name = $this->name . ' (Copy)';
        $newProduct->slug = null; // Will be auto-generated
        $newProduct->sku = null; // Will be auto-generated
        $newProduct->is_active = false; // Deactivate by default
        $newProduct->save();

        return $newProduct;
    }

    /**
     * Get available karat options for this metal category
     */
    public static function getAvailableKaratsForMetal($metalCategoryId)
    {
        $metalCategory = MetalCategory::find($metalCategoryId);
        return $metalCategory ? $metalCategory->getAvailableKarats() : [];
    }

    /**
     * Get price breakdown for transparency using database prices
     */
    public function getPriceBreakdown($customWeight = null)
    {
        $weight = $customWeight ?? $this->weight ?? 1.0; // Default weight if not set

        if (!$this->metalCategory) {
            return null;
        }

        // Use default karat if not set
        $karat = $this->karat;
        if (!$karat) {
            $availableKarats = $this->metalCategory->getAvailableKarats();
            $karat = $availableKarats[0] ?? '18'; // Use first available or default
        }

        $pricePerGram = $this->metalCategory->calculatePricePerGram($karat);
        $metalValue = $weight * $pricePerGram;

        $laborCost = $this->labor_cost ?? $this->subcategory?->getLaborCostForMetal($this->metal_category_id) ?? 15.00;
        $totalLaborCost = $weight * $laborCost;

        $baseCost = $metalValue + $totalLaborCost;

        $profitMargin = $this->profit_margin ?? $this->subcategory?->getProfitMarginForMetal($this->metal_category_id) ?? 25.00;
        $profitAmount = $baseCost * ($profitMargin / 100);

        $finalPrice = $baseCost + $profitAmount;

        return [
            'weight' => $weight,
            'price_per_gram' => $pricePerGram,
            'metal_value' => $metalValue,
            'labor_cost_per_gram' => $laborCost,
            'total_labor_cost' => $totalLaborCost,
            'base_cost' => $baseCost,
            'profit_margin_percent' => $profitMargin,
            'profit_amount' => $profitAmount,
            'final_price' => $finalPrice,
        ];
    }

    /**
     * Get available karats for this product's metal from database
     */
    public function getAvailableKarats()
    {
        return $this->metalCategory?->getAvailableKarats() ?? [];
    }

    /**
     * Get karat display text from metal category
     */
    public function getKaratDisplayAttribute()
    {
        if (!$this->karat) {
            return 'No Karat Set';
        }

        if ($this->metalCategory) {
            return $this->metalCategory->getShortKaratDisplay($this->karat);
        }

        // Fallback
        if ($this->metalCategory?->symbol === 'XAG') {
            return $this->karat === '925' ? 'Sterling Silver (925)' : 'Silver ' . $this->karat;
        }

        return $this->karat . 'K';
    }

    /**
     * Get metal name from category
     */
    public function getMetalNameAttribute()
    {
        return $this->metalCategory?->name ?? 'Unknown Metal';
    }

    /**
     * Calculate price with optional weight
     */
    public function calculatePrice($customWeight = null)
    {
        try {
            $weight = $customWeight ?? $this->weight ?? 1.0; // Default weight if not set
            
            if (!$this->metalCategory) {
                return $this->labor_cost ?? 0;
            }

            // Get metal price per gram (convert from per ounce)
            $pricePerOunce = $this->metalCategory->current_price_usd ?? 0;
            $pricePerGram = $pricePerOunce / 31.1035; // Convert ounce to grams

            // Calculate karat multiplier
            $karatMultiplier = $this->getKaratMultiplier();

            // Calculate material cost
            $materialCost = $pricePerGram * $karatMultiplier * $weight;

            // Add labor cost
            $laborCost = $this->labor_cost ?? 0;

            // Apply profit margin if set
            $totalCost = $materialCost + $laborCost;
            if ($this->profit_margin && $this->profit_margin > 0) {
                $totalCost = $totalCost * (1 + ($this->profit_margin / 100));
            }

            return round($totalCost, 2);

        } catch (\Exception $e) {
            // Return labor cost as fallback
            return $this->labor_cost ?? 0;
        }
    }

    /**
     * Get karat multiplier for price calculation.
     */
    private function getKaratMultiplier()
    {
        if (!$this->karat) {
            return 0.75; // Default to 18K equivalent
        }

        if ($this->metalCategory && $this->metalCategory->symbol === 'XAG') {
            // Silver purity
            return match($this->karat) {
                '800' => 0.800,
                '900' => 0.900,
                '925' => 0.925,
                '950' => 0.950,
                '999' => 0.999,
                default => 0.925
            };
        }

        // Gold purity (default)
        return match($this->karat) {
            '9' => 9/24,   // 37.5%
            '10' => 10/24, // 41.7%
            '14' => 14/24, // 58.3%
            '18' => 18/24, // 75%
            '20' => 20/24, // 83.3%
            '21' => 21/24, // 87.5%
            '22' => 22/24, // 91.7%
            '24' => 24/24, // 100%
            default => 18/24 // Default to 18K
        };
    }

    /**
     * Get formatted price for display.
     */
    public function getFormattedPrice($customWeight = null)
    {
        $price = $this->calculatePrice($customWeight);
        return number_format($price, 2);
    }

    /**
     * Calculate live price (alias for calculatePrice).
     */
    public function calculateLivePrice($customWeight = null)
    {
        return $this->calculatePrice($customWeight);
    }

    /**
     * Updated validation rules - made many fields optional
     */
    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'metal_category_id' => 'required|exists:metal_categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'sku' => 'required|string|unique:products,sku' . ($id ? ',' . $id : ''),
            'weight' => 'nullable|numeric|min:0.01|max:9999.999',
            'min_weight' => 'nullable|numeric|min:0.01|max:9999.999',
            'max_weight' => 'nullable|numeric|min:0.01|max:9999.999',
            'weight_step' => 'nullable|numeric|min:0.001|max:10',
            'karat' => 'nullable|string',
            'labor_cost' => 'nullable|numeric|min:0|max:999.99',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'sort_order' => 'nullable|integer|min:0'
        ];
    }
}