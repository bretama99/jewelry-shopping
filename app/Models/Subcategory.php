<?php
// File: app/Models/OrderItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'default_labor_cost',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'default_labor_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    // Boot method for auto-slug generation and cleanup
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subcategory) {
            if (empty($subcategory->slug)) {
                $subcategory->slug = $subcategory->generateUniqueSlug($subcategory->name);
            }
        });

        static::updating(function ($subcategory) {
            if ($subcategory->isDirty('name') && empty($subcategory->slug)) {
                $subcategory->slug = $subcategory->generateUniqueSlug($subcategory->name);
            }
        });

        static::deleting(function ($subcategory) {
            if ($subcategory->image) {
                $imagePath = public_path('images/subcategories/' . $subcategory->image);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }
        });
    }

    // Relationships
    public function metalCategories()
    {
        return $this->belongsToMany(MetalCategory::class, 'metal_category_subcategory')
                    ->withPivot(['labor_cost_override', 'profit_margin_override', 'is_available'])
                    ->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    public function activeMetalCategories()
    {
        return $this->metalCategories()->where('metal_categories.is_active', true)
                    ->wherePivot('is_available', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeWithProductCount($query)
    {
        return $query->withCount(['products', 'activeProducts']);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path('images/subcategories/' . $this->image))) {
            return asset('images/subcategories/' . $this->image);
        }
        return asset('images/default-subcategory.jpg');
    }

    public function getProductsCountAttribute()
    {
        if (!$this->relationLoaded('products')) {
            return $this->products()->count();
        }
        return $this->products->count();
    }

    public function getActiveProductsCountAttribute()
    {
        if (!$this->relationLoaded('activeProducts')) {
            return $this->activeProducts()->count();
        }
        return $this->activeProducts->count();
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->name;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: Str::limit($this->description, 160);
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

    public function canBeDeleted()
    {
        return $this->products()->count() === 0;
    }

    public function hasImage()
    {
        return !empty($this->image) && file_exists(public_path('images/subcategories/' . $this->image));
    }

    /**
     * Get labor cost for specific metal category (with override)
     */
    public function getLaborCostForMetal($metalCategoryId)
    {
        $pivot = $this->metalCategories()->where('metal_category_id', $metalCategoryId)->first()?->pivot;
        return $pivot?->labor_cost_override ?? $this->default_labor_cost;
    }

    /**
     * Get profit margin for specific metal category (with override)
     */

    // URL generation
    public function getUrlAttribute()
    {
        return route('subcategories.show', $this->slug);
    }

    public function getAdminUrlAttribute()
    {
        return route('admin.subcategories.show', $this);
    }

    public function getEditUrlAttribute()
    {
        return route('admin.subcategories.edit', $this);
    }

    // JSON serialization for API
    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => Str::limit($this->description, 100),
            'products_count' => $this->products_count,
            'image_url' => $this->image_url,
            'url' => $this->url,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active
        ];
    }
}
