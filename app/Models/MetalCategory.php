<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MetalCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'slug',
        'description',
        'image',
        'current_price_usd',
        'aud_rate',
        'purity_ratios',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'current_price_usd' => 'decimal:2',
        'aud_rate' => 'decimal:4',
        'purity_ratios' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    // Boot method for auto-slug generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($metalCategory) {
            if (empty($metalCategory->slug)) {
                $metalCategory->slug = $metalCategory->generateUniqueSlug($metalCategory->name);
            }
        });

        static::updating(function ($metalCategory) {
            if ($metalCategory->isDirty('name') && empty($metalCategory->slug)) {
                $metalCategory->slug = $metalCategory->generateUniqueSlug($metalCategory->name);
            }
        });
    }

    // Relationships
    public function subcategories()
    {
        return $this->belongsToMany(Subcategory::class, 'metal_category_subcategory')
                    ->withPivot(['labor_cost_override', 'profit_margin_override', 'is_available'])
                    ->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function activeSubcategories()
    {
        return $this->subcategories()->where('subcategories.is_active', true)
                    ->wherePivot('is_available', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path('images/metals/' . $this->image))) {
            return asset('images/metals/' . $this->image);
        }
        return asset('images/default-metal.jpg');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    /**
     * Get all available karats/purities for this metal
     */
public function getAvailableKarats()
{
    $purityRatios = $this->purity_ratios;

    // Handle case where purity_ratios might be null, empty, or not properly cast
    if (!is_array($purityRatios)) {
        return [];
    }

    return array_keys($purityRatios);
}

    /**
     * Get prices for all karats/purities
     */
/**
 * Get prices for all karats/purities
 */
public function getAllPrices()
{
    $karats = $this->getAvailableKarats();

    if (empty($karats)) {
        return [];
    }

    $prices = [];
    foreach ($karats as $karat) {
        $prices[$karat] = $this->calculatePricePerGram($karat);
    }
    return $prices;
}

    /**
     * Update price from API
     */
    public function updatePriceFromApi($usdPrice, $audRate = null)
    {
        $this->current_price_usd = $usdPrice;
        if ($audRate) {
            $this->aud_rate = $audRate;
        }
        $this->save();
    }

    /**
     * Get icon color class for UI display
     */
    public function getIconColorClass()
    {
        switch ($this->symbol) {
            case 'XAU': // Gold
                return 'text-warning';
            case 'XAG': // Silver
                return 'text-secondary';
            case 'XPT': // Platinum
                return 'text-dark';
            case 'XPD': // Palladium
                return 'text-info';
            default:
                return 'text-muted';
        }
    }

    /**
     * Get karat display text with full description
     */
    public function getKaratDisplayText($karat)
    {
        switch ($this->symbol) {
            case 'XAU': // Gold
                return $karat . 'K Gold (' . (($karat / 24) * 100) . '% Pure)';
            case 'XAG': // Silver
                $purity = $this->purity_ratios[$karat] ?? 0;
                switch ($karat) {
                    case '925':
                        return '925 Sterling Silver (92.5% Pure)';
                    case '950':
                        return '950 Britannia Silver (95% Pure)';
                    case '999':
                        return '999 Fine Silver (99.9% Pure)';
                    default:
                        return $karat . ' Silver (' . ($purity * 100) . '% Pure)';
                }
            case 'XPT': // Platinum
                $purity = $this->purity_ratios[$karat] ?? 0;
                return $karat . ' Platinum (' . ($purity * 100) . '% Pure)';
            case 'XPD': // Palladium
                $purity = $this->purity_ratios[$karat] ?? 0;
                return $karat . ' Palladium (' . ($purity * 100) . '% Pure)';
            default:
                return $karat;
        }
    }

    /**
     * Get short karat display for dropdowns
     */
    public function getShortKaratDisplay($karat)
    {
        switch ($this->symbol) {
            case 'XAU': // Gold
                return $karat . 'K';
            case 'XAG': // Silver
            case 'XPT': // Platinum
            case 'XPD': // Palladium
                return $karat;
            default:
                return $karat;
        }
    }

    /**
     * Get default karats/purities for seeding
     */
    public static function getDefaultPurityRatios($symbol)
    {
        switch ($symbol) {
            case 'XAU': // Gold
                return [
                    '9' => 9/24,   // 9K = 37.5%
                    '10' => 10/24, // 10K = 41.7%
                    '14' => 14/24, // 14K = 58.3%
                    '18' => 18/24, // 18K = 75%
                    '19' => 19/24, // 19K = 79.2%
                    '20' => 20/24, // 20K = 83.3%
                    '21' => 21/24, // 21K = 87.5%
                    '22' => 22/24, // 22K = 91.7%
                    '23' => 23/24, // 23K = 95.8%
                    '24' => 24/24  // 24K = 100%
                ];
            case 'XAG': // Silver
                return [
                    '800' => 0.800, // 80% silver
                    '900' => 0.900, // 90% coin silver
                    '925' => 0.925, // Sterling silver
                    '950' => 0.950, // Britannia silver
                    '999' => 0.999  // Fine silver
                ];
            case 'XPT': // Platinum
                return [
                    '850' => 0.850, // 85% platinum
                    '900' => 0.900, // 90% platinum
                    '950' => 0.950, // 95% platinum
                    '999' => 0.999  // Fine platinum
                ];
            case 'XPD': // Palladium
                return [
                    '500' => 0.500, // 50% palladium
                    '950' => 0.950, // 95% palladium
                    '999' => 0.999  // Fine palladium
                ];
            default:
                return [];
        }
    }

    /**
     * Check if this metal supports a specific karat/purity
     */
    public function supportsKarat($karat)
    {
        return array_key_exists($karat, $this->purity_ratios ?? []);
    }

    /**
     * Get formatted price display
     */
    public function getFormattedPriceAttribute()
    {
        return 'USD$' . number_format($this->current_price_usd, 2);
    }

    /**
     * Get last price update time
     */
    public function getLastPriceUpdateAttribute()
    {
        return $this->updated_at;
    }

    /**
     * Check if price data is stale (older than 30 minutes)
     */
    public function isPriceStale()
    {
        return $this->updated_at < now()->subMinutes(30);
    }

    /**
     * Get metal category by symbol
     */
    public static function findBySymbol($symbol)
    {
        return static::where('symbol', $symbol)->where('is_active', true)->first();
    }

    /**
     * Seed default metal categories
     */
    public static function seedDefaults()
    {
        $metals = [
            [
                'name' => 'Gold',
                'symbol' => 'XAU',
                'slug' => 'gold',
                'description' => 'Precious yellow metal, symbol of wealth and luxury',
                'current_price_usd' => 2000.00,
                'aud_rate' => 1.45,
                'purity_ratios' => self::getDefaultPurityRatios('XAU'),
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Silver',
                'symbol' => 'XAG',
                'slug' => 'silver',
                'description' => 'Precious white metal, affordable luxury option',
                'current_price_usd' => 25.00,
                'aud_rate' => 1.45,
                'purity_ratios' => self::getDefaultPurityRatios('XAG'),
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Platinum',
                'symbol' => 'XPT',
                'slug' => 'platinum',
                'description' => 'Rare precious metal, extremely durable',
                'current_price_usd' => 1000.00,
                'aud_rate' => 1.45,
                'purity_ratios' => self::getDefaultPurityRatios('XPT'),
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Palladium',
                'symbol' => 'XPD',
                'slug' => 'palladium',
                'description' => 'Modern precious metal, popular in contemporary jewelry',
                'current_price_usd' => 1500.00,
                'aud_rate' => 1.45,
                'purity_ratios' => self::getDefaultPurityRatios('XPD'),
                'is_active' => true,
                'sort_order' => 4
            ]
        ];

        foreach ($metals as $metalData) {
            static::updateOrCreate(
                ['symbol' => $metalData['symbol']],
                $metalData
            );
        }
    }

    public function calculatePricePerGram($purity)
{
    try {
        $metalPriceService = app(\App\Services\MetalPriceApiService::class);
        return $metalPriceService->getMetalPrice($this->symbol, $purity);
    } catch (\Exception $e) {
        \Log::error("Error calculating price for {$this->symbol} {$purity}: " . $e->getMessage());

        // Fallback to database calculation if service fails
        if (!$this->current_price_usd || !$this->aud_rate) {
            return 0;
        }

        $purityRatios = $this->purity_ratios ?? [];
        $purityRatio = $purityRatios[$purity] ?? 1;

        // Convert troy ounce to grams (1 troy ounce = 31.1035 grams)
        $gramsPerTroyOz = 31.1035;

        // Calculate: (USD price per troy ounce * AUD rate * purity ratio) / grams per troy ounce
        $pricePerGram = ($this->current_price_usd * $this->aud_rate * $purityRatio) / $gramsPerTroyOz;

        return round($pricePerGram, 2);
    }
}

/**
 * ADD this method to MetalCategory model for API compatibility
 */
public function getAvailableKaratsAttribute()
{
    return $this->getAvailableKarats();
}

/**
 * ADD this method to MetalCategory model for API compatibility
 */
public function getPurityRatiosAttribute($value)
{
    if (is_string($value)) {
        return json_decode($value, true);
    }
    return $value;
}

}
