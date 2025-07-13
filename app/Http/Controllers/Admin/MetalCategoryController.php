<?php
// app/Http/Controllers/Admin/MetalCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use App\Services\MetalPriceApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MetalCategoryController extends Controller
{
    protected $metalPriceService;

    // Cache key for single API call optimization
    private const CACHE_KEY = 'admin_metals_api_prices';
    private const CACHE_DURATION = 300; // 5 minutes

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->metalPriceService = $metalPriceService;
    }

    /**
     * Get all live metal prices with SINGLE API call and caching (optimized for admin)
     */
    private function getLiveMetalPrices()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                Log::info('Admin: Making single API call to metals-api.com for all metals');

                $response = Http::timeout(10)
                    ->get('https://metals-api.com/api/latest', [
                        'access_key' => '1c70eyqb8hpkqcg8bmtfwalg6u84j20qv9gq8fq7k2h8f6fi9m7p61sxkmng',
                        'base' => 'AUD',
                        'symbols' => 'XAU,XAG,XPD,XPT'
                    ]);

                if (!$response->successful()) {
                    throw new \Exception('API request failed: ' . $response->status());
                }

                $data = $response->json();
                if (!$data['success'] || !isset($data['rates'])) {
                    throw new \Exception('Invalid API response format');
                }

                // Store the rates with timestamp
                $rates = [
                    'XAU' => $data['rates']['XAU'], // Pure 24K Gold per troy ounce AUD
                    'XAG' => $data['rates']['XAG'], // Pure 999 Silver per troy ounce AUD
                    'XPD' => $data['rates']['XPD'], // Pure 999 Palladium per troy ounce AUD
                    'XPT' => $data['rates']['XPT'], // Pure 999 Platinum per troy ounce AUD
                    'timestamp' => $data['timestamp'],
                    'date' => $data['date'],
                    'fetched_at' => now()->toISOString()
                ];

                Log::info('Admin: Successfully fetched all metal prices in single API call', $rates);
                return $rates;

            } catch (\Exception $e) {
                Log::error('Admin: Failed to fetch metal prices: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Display a listing of metal categories
     */
    public function index(Request $request)
    {
        try {
            $query = MetalCategory::withCount(['products', 'subcategories']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('symbol', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Apply sorting
            switch ($request->get('sort', 'sort_order')) {
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'symbol':
                    $query->orderBy('symbol', 'asc');
                    break;
                case 'price':
                    $query->orderBy('current_price_usd', 'desc');
                    break;
                case 'products_count':
                    $query->orderByDesc('products_count');
                    break;
                default:
                    $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
            }

            $metalCategories = $query->paginate(15)->appends($request->query());

            // Calculate stats
            $stats = [
                'total_metals' => MetalCategory::count(),
                'active_metals' => MetalCategory::where('is_active', true)->count(),
                'total_products' => MetalCategory::withCount('products')->get()->sum('products_count'),
                'last_price_update' => MetalCategory::whereNotNull('current_price_usd')->max('updated_at'),
            ];

            $filters = $request->only(['search', 'status', 'sort']);

            // Get current metal prices using optimized single API call
            $currentPrices = [];
            try {
                $rates = $this->getLiveMetalPrices();
                $currentPrices = [
                    'gold' => $this->calculateAllPricesForMetal('XAU'),
                    'silver' => $this->calculateAllPricesForMetal('XAG'),
                    'platinum' => $this->calculateAllPricesForMetal('XPT'),
                    'palladium' => $this->calculateAllPricesForMetal('XPD'),
                    'api_info' => [
                        'timestamp' => $rates['timestamp'],
                        'date' => $rates['date'],
                        'fetched_at' => $rates['fetched_at']
                    ]
                ];
            } catch (\Exception $e) {
                Log::warning('Admin: Failed to get current prices: ' . $e->getMessage());
                $currentPrices = [];
            }

            return view('admin.metal-categories.index', compact('metalCategories', 'stats', 'filters', 'currentPrices'));

        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Error loading metal categories: ' . $e->getMessage());
        }
    }

    /**
     * Calculate all prices for a metal using cached data
     */
    private function calculateAllPricesForMetal($metalSymbol)
    {
        try {
            $rates = $this->getLiveMetalPrices(); // Uses cache

            if (!isset($rates[$metalSymbol])) {
                return [];
            }

            $purePricePerOz = $rates[$metalSymbol];
            $gramsPerTroyOz = 31.1035;
            $purePricePerGram = $purePricePerOz / $gramsPerTroyOz;
            $prices = [];

            // Calculate prices based on metal type
            if ($metalSymbol === 'XAU') {
                $goldKarats = [9, 10, 14, 18, 21, 22, 24];
                foreach ($goldKarats as $karat) {
                    $purityRatio = $karat / 24;
                    $prices[(string)$karat] = round($purePricePerGram * $purityRatio, 2);
                }
            } elseif ($metalSymbol === 'XAG') {
                $silverPurities = ['925' => 0.925, '950' => 0.950, '999' => 0.999];
                foreach ($silverPurities as $purity => $ratio) {
                    $prices[$purity] = round($purePricePerGram * $ratio, 2);
                }
            } elseif ($metalSymbol === 'XPT') {
                $platinumPurities = ['900' => 0.900, '950' => 0.950, '999' => 0.999];
                foreach ($platinumPurities as $purity => $ratio) {
                    $prices[$purity] = round($purePricePerGram * $ratio, 2);
                }
            } elseif ($metalSymbol === 'XPD') {
                $palladiumPurities = ['500' => 0.500, '950' => 0.950, '999' => 0.999];
                foreach ($palladiumPurities as $purity => $ratio) {
                    $prices[$purity] = round($purePricePerGram * $ratio, 2);
                }
            }

            return $prices;
        } catch (\Exception $e) {
            Log::error("Admin: Failed to calculate prices for {$metalSymbol}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Show the form for creating a new metal category
     */
    public function create()
    {
        // Get all active subcategories for the jewelry types selection
        $subcategories = Subcategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.metal-categories.create', compact('subcategories'));
    }

    /**
     * Store a newly created metal category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:metal_categories,name',
            'symbol' => 'nullable|string|size:3|unique:metal_categories,symbol',
            'slug' => 'nullable|string|unique:metal_categories,slug',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'is_precious' => 'nullable|boolean',
            'auto_update_prices' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'exists:subcategories,id'
        ]);

        try {
            $data = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $this->handleImageUpload($request->file('image'), $data['name']);
            }

            // Handle checkboxes
            $data['is_active'] = $request->has('is_active');
            $data['is_precious'] = $request->has('is_precious');
            $data['auto_update_prices'] = $request->has('auto_update_prices');

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Create default purity ratios based on symbol with correct karats
            $data['purity_ratios'] = $this->getDefaultPurityRatios($data['symbol']);

            $metalCategory = MetalCategory::create($data);

            // Attach subcategories if selected
            if ($request->has('subcategories') && is_array($request->subcategories)) {
                $subcategoryData = [];
                foreach ($request->subcategories as $subcategoryId) {
                    $subcategoryData[$subcategoryId] = [
                        'is_available' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $metalCategory->subcategories()->sync($subcategoryData);
            }

            // Try to fetch initial price from metals-api.com using cached data
            try {
                $this->updateMetalPriceFromCache($metalCategory);
            } catch (\Exception $e) {
                Log::warning("Admin: Failed to fetch initial price for {$metalCategory->symbol}: " . $e->getMessage());
            }

            return redirect()->route('admin.metal-categories.index')
                           ->with('success', 'Metal category created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error creating metal category: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified metal category
     */
    public function edit(MetalCategory $metalCategory)
    {
        // Get all active subcategories for the jewelry types selection
        $subcategories = Subcategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.metal-categories.edit', compact('metalCategory', 'subcategories'));
    }

    /**
     * Display the specified metal category
     */
    public function show(MetalCategory $metalCategory)
    {
        $metalCategory->load(['subcategories', 'products' => function ($query) {
            $query->where('is_active', true)->orderBy('name')->take(10);
        }]);

        // Get current prices for all karats/purities using cached data
        $currentPrices = $this->calculateAllPricesForMetal($metalCategory->symbol);

        return view('admin.metal-categories.show', compact('metalCategory', 'currentPrices'));
    }

    /**
     * Update the specified metal category
     */
    public function update(Request $request, MetalCategory $metalCategory)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('metal_categories')->ignore($metalCategory->id)],
            'symbol' => ['nullable', 'string', 'size:3', Rule::unique('metal_categories')->ignore($metalCategory->id)],
            'slug' => ['nullable', 'string', Rule::unique('metal_categories')->ignore($metalCategory->id)],
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'is_precious' => 'nullable|boolean',
            'auto_update_prices' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'exists:subcategories,id'
        ]);

        try {
            $data = $request->all();

            // Handle image removal
            if ($request->has('remove_image') && $request->remove_image) {
                if ($metalCategory->image) {
                    $this->deleteImage($metalCategory->image);
                    $data['image'] = null;
                }
            }

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($metalCategory->image) {
                    $this->deleteImage($metalCategory->image);
                }
                $data['image'] = $this->handleImageUpload($request->file('image'), $data['name']);
            }

            // Handle checkboxes
            $data['is_active'] = $request->has('is_active');
            $data['is_precious'] = $request->has('is_precious');
            $data['auto_update_prices'] = $request->has('auto_update_prices');

            $metalCategory->update($data);

            // Update subcategories if provided
            if ($request->has('subcategories')) {
                $subcategoryData = [];
                foreach ($request->subcategories as $subcategoryId) {
                    $subcategoryData[$subcategoryId] = [
                        'is_available' => true,
                        'updated_at' => now(),
                    ];
                }
                $metalCategory->subcategories()->sync($subcategoryData);
            }

            return redirect()->route('admin.metal-categories.index')
                           ->with('success', 'Metal category updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error updating metal category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified metal category
     */
    public function destroy(MetalCategory $metalCategory)
    {
        try {
            // Check if metal category can be deleted
            if ($metalCategory->products()->count() > 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete metal category with existing products.'
                    ], 400);
                }

                return back()->with('error', 'Cannot delete metal category with existing products.');
            }

            // Delete associated image before deleting category
            if ($metalCategory->image) {
                $this->deleteImage($metalCategory->image);
            }

            // Detach all subcategories
            $metalCategory->subcategories()->detach();

            $metalCategory->delete();

            if (request()->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.metal-categories.index')
                           ->with('success', 'Metal category deleted successfully.');

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get live price for specific metal symbol using cached data (AJAX endpoint)
     */
    public function getLivePrice($symbol)
    {
        try {
            // Validate symbol
            if (!in_array($symbol, ['XAU', 'XAG', 'XPT', 'XPD'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid metal symbol'
                ], 400);
            }

            // Get price from cached data - no direct API call
            $rates = $this->getLiveMetalPrices();

            if (!isset($rates[$symbol])) {
                throw new \Exception('Metal price not available in cache');
            }

            $gramsPerTroyOz = 31.1035;
            $audPricePerOz = $rates[$symbol];
            $audPricePerGram = $audPricePerOz / $gramsPerTroyOz;

            return response()->json([
                'success' => true,
                'price_usd' => 'AUD$' . number_format($audPricePerOz, 2),
                'price_per_gram_aud' => 'AUD$' . number_format($audPricePerGram, 2),
                'exchange_rate' => '1.00 AUD (Direct)',
                'last_updated' => $rates['fetched_at'],
                'api_source' => 'metals-api.com (cached)',
                'api_timestamp' => $rates['timestamp']
            ]);

        } catch (\Exception $e) {
            Log::error("Admin: Error fetching live price for {$symbol}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching live price from cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle metal category status
     */
    public function toggleStatus(MetalCategory $metalCategory)
    {
        try {
            $metalCategory->update(['is_active' => !$metalCategory->is_active]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_active' => $metalCategory->is_active,
                    'message' => 'Status updated successfully.'
                ]);
            }

            return back()->with('success', 'Metal category status updated successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', 'Error updating status.');
        }
    }

    /**
     * Update price for specific metal category using cached data
     */
    public function updatePrice(Request $request, MetalCategory $metalCategory)
    {
        $request->validate([
            'price_aud' => 'required|numeric|min:0',
        ]);

        try {
            $metalCategory->update([
                'current_price_usd' => $request->price_aud, // Store AUD price (keeping field name for compatibility)
                'aud_rate' => 1.0, // Always 1.0 since we work in AUD
                'price_updated_at' => now()
            ]);

            // Get new calculated prices
            $newPrices = $this->calculateAllPricesForMetal($metalCategory->symbol);

            return response()->json([
                'success' => true,
                'message' => 'Price updated successfully.',
                'new_prices' => $newPrices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update all metal prices using cached data
     */
    public function updateAllPrices()
    {
        try {
            // Clear cache to force fresh API call
            Cache::forget(self::CACHE_KEY);

            $rates = $this->getLiveMetalPrices(); // This will make a fresh API call
            $updateCount = 0;

            $metalCategories = MetalCategory::where('auto_update_prices', true)->get();

            foreach ($metalCategories as $metalCategory) {
                try {
                    $this->updateMetalPriceFromCache($metalCategory);
                    $updateCount++;
                } catch (\Exception $e) {
                    Log::error("Admin: Failed to update price for {$metalCategory->symbol}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Updated prices for {$updateCount} metal categories using single API call.",
                'api_timestamp' => $rates['timestamp'],
                'fetched_at' => $rates['fetched_at']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating prices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update metal price from cached data (optimized)
     */
    private function updateMetalPriceFromCache(MetalCategory $metalCategory)
    {
        try {
            $rates = $this->getLiveMetalPrices();

            if (isset($rates[$metalCategory->symbol])) {
                $audPricePerOz = $rates[$metalCategory->symbol];

                $metalCategory->update([
                    'current_price_usd' => $audPricePerOz, // Store as AUD (keeping field name for compatibility)
                    'aud_rate' => 1.0, // Always 1.0 since we get AUD directly
                    'price_updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Admin: Failed to update price for {$metalCategory->symbol} from cache: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Duplicate metal category
     */
    public function duplicate(MetalCategory $metalCategory)
    {
        try {
            // Handle image duplication
            $duplicatedImage = null;
            if ($metalCategory->image) {
                $duplicatedImage = $this->duplicateImage($metalCategory->image);
            }

            $newMetalCategory = $metalCategory->replicate();
            $newMetalCategory->name = $metalCategory->name . ' (Copy)';
            $newMetalCategory->symbol = $metalCategory->symbol . '2'; // Modify symbol to ensure uniqueness
            $newMetalCategory->slug = null; // Will be auto-generated
            $newMetalCategory->is_active = false;
            $newMetalCategory->image = $duplicatedImage;
            $newMetalCategory->current_price_usd = null; // Reset price
            $newMetalCategory->save();

            // Copy subcategory relationships
            $subcategoryData = [];
            foreach ($metalCategory->subcategories as $subcategory) {
                $subcategoryData[$subcategory->id] = [
                    'labor_cost_override' => $subcategory->pivot->labor_cost_override ?? null,
                    'profit_margin_override' => $subcategory->pivot->profit_margin_override ?? null,
                    'is_available' => $subcategory->pivot->is_available ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $newMetalCategory->subcategories()->sync($subcategoryData);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Metal category duplicated successfully.',
                    'redirect' => route('admin.metal-categories.edit', $newMetalCategory)
                ]);
            }

            return redirect()->route('admin.metal-categories.edit', $newMetalCategory)
                           ->with('success', 'Metal category duplicated successfully. Please review and update as needed.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', 'Error duplicating metal category.');
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,update_prices',
            'metal_category_ids' => 'required|array|min:1',
            'metal_category_ids.*' => 'exists:metal_categories,id'
        ]);

        try {
            $metalCategories = MetalCategory::whereIn('id', $request->metal_category_ids);

            switch ($request->action) {
                case 'activate':
                    $metalCategories->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $metalCategories->update(['is_active' => false]);
                    break;
                case 'update_prices':
                    foreach ($metalCategories->get() as $metalCategory) {
                        $this->updateMetalPriceFromCache($metalCategory);
                    }
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid bulk action: {$request->action}");
            }

            $actionName = str_replace('_', ' ', $request->action);
            return response()->json([
                'success' => true,
                'message' => "Successfully {$actionName}d " . count($request->metal_category_ids) . " metal category(s)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export metal categories
     */
    public function export(Request $request)
    {
        try {
            $metalCategories = MetalCategory::with('subcategories')
                ->when($request->status, function($query, $status) {
                    $query->where('is_active', $status === 'active');
                })
                ->get();

            $filename = 'metal_categories_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function() use ($metalCategories) {
                $handle = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($handle, [
                    'ID', 'Name', 'Symbol', 'Slug', 'Description', 'Status',
                    'Current Price AUD', 'Products Count', 'Subcategories Count',
                    'Available Karats/Purities', 'Sort Order', 'Created At', 'Updated At'
                ]);

                // Add data rows
                foreach ($metalCategories as $metalCategory) {
                    // Get available karats/purities
                    $availableKarats = '';
                    if ($metalCategory->symbol === 'XAU') {
                        $availableKarats = '9K, 10K, 14K, 18K, 21K, 22K, 24K';
                    } elseif ($metalCategory->symbol === 'XAG') {
                        $availableKarats = '925, 950, 999';
                    } elseif ($metalCategory->symbol === 'XPT') {
                        $availableKarats = '900, 950, 999';
                    } elseif ($metalCategory->symbol === 'XPD') {
                        $availableKarats = '500, 950, 999';
                    }

                    fputcsv($handle, [
                        $metalCategory->id,
                        $metalCategory->name,
                        $metalCategory->symbol,
                        $metalCategory->slug,
                        $metalCategory->description,
                        $metalCategory->is_active ? 'Active' : 'Inactive',
                        $metalCategory->current_price_usd, // Actually AUD
                        $metalCategory->products->count(),
                        $metalCategory->subcategories->count(),
                        $availableKarats,
                        $metalCategory->sort_order,
                        $metalCategory->created_at->format('Y-m-d H:i:s'),
                        $metalCategory->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            }, 200, $headers);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export metal categories: ' . $e->getMessage());
        }
    }

    /**
     * Get default purity ratios based on metal symbol with correct karats
     */
    private function getDefaultPurityRatios($symbol)
    {
        $defaults = [
            'XAU' => [ // Gold with correct karats: 9K, 10K, 14K, 18K, 21K, 22K, 24K
                '9' => 0.375,   // 9K = 37.5%
                '10' => 0.417,  // 10K = 41.7%
                '14' => 0.583,  // 14K = 58.3%
                '18' => 0.75,   // 18K = 75%
                '21' => 0.875,  // 21K = 87.5%
                '22' => 0.917,  // 22K = 91.7%
                '24' => 1.0     // 24K = 100%
            ],
            'XAG' => [ // Silver
                '925' => 0.925, // Sterling silver
                '950' => 0.950, // Higher grade silver
                '999' => 0.999  // Fine silver
            ],
            'XPT' => [ // Platinum
                '900' => 0.900,
                '950' => 0.950,
                '999' => 0.999
            ],
            'XPD' => [ // Palladium
                '500' => 0.500,
                '950' => 0.950,
                '999' => 0.999
            ]
        ];

        return json_encode($defaults[$symbol] ?? []);
    }

    /**
     * Handle image upload to metals folder
     */
    private function handleImageUpload($file, $metalName)
    {
        // Create metals directory if it doesn't exist
        $metalsPath = public_path('images/metals');
        if (!File::exists($metalsPath)) {
            File::makeDirectory($metalsPath, 0755, true);
        }

        // Generate unique filename
        $filename = time() . '_' . Str::slug($metalName) . '.' . $file->getClientOriginalExtension();

        // Move file to metals directory
        $file->move($metalsPath, $filename);

        return $filename;
    }

    /**
     * Delete image from metals folder
     */
    private function deleteImage($filename)
    {
        if ($filename) {
            $imagePath = public_path('images/metals/' . $filename);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }
    }

    /**
     * Duplicate image file for metal category duplication
     */
    private function duplicateImage($originalFilename)
    {
        $originalPath = public_path('images/metals/' . $originalFilename);

        if (!File::exists($originalPath)) {
            return null;
        }

        // Generate new filename
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $newFilename = time() . '_copy_' . Str::random(10) . '.' . $extension;
        $newPath = public_path('images/metals/' . $newFilename);

        // Copy the file
        if (File::copy($originalPath, $newPath)) {
            return $newFilename;
        }

        return null;
    }

    public function apiIndex()
{
    try {
        $metalCategories = MetalCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'symbol' => $category->symbol,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'sort_order' => $category->sort_order,
                    'available_karats' => $this->getAvailableKaratsForMetal($category->symbol),
                    'available_purities' => $this->getAvailableKaratsForMetal($category->symbol),
                    'purity_ratios' => $this->getPurityRatiosForMetal($category->symbol),
                    'updated_at' => $category->updated_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $metalCategories
        ]);

    } catch (\Exception $e) {
        \Log::error('API: Error loading metal categories: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to load metal categories',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Get available karats for metal - ADD THIS METHOD
 */
private function getAvailableKaratsForMetal($symbol)
{
    switch ($symbol) {
        case 'XAU': return ['9', '10', '14', '18', '21', '22', '24'];
        case 'XAG': return ['925', '950', '999'];
        case 'XPT': return ['900', '950', '999'];
        case 'XPD': return ['500', '950', '999'];
        default: return [];
    }
}

/**
 * Get purity ratios for metal - ADD THIS METHOD
 */
private function getPurityRatiosForMetal($symbol)
{
    switch ($symbol) {
        case 'XAU': return ['9' => 0.375, '10' => 0.417, '14' => 0.583, '18' => 0.75, '21' => 0.875, '22' => 0.917, '24' => 1.0];
        case 'XAG': return ['925' => 0.925, '950' => 0.950, '999' => 0.999];
        case 'XPT': return ['900' => 0.900, '950' => 0.950, '999' => 0.999];
        case 'XPD': return ['500' => 0.500, '950' => 0.950, '999' => 0.999];
        default: return [];
    }
}


public function getScrapMargins($metalSlug)
{
    try {
        // Default scrap margins by metal and purity
        $defaultMargins = [
            'gold' => [
                '9' => 0.20,   // 20% margin for 9K gold
                '10' => 0.18,  // 18% margin for 10K gold
                '14' => 0.15,  // 15% margin for 14K gold
                '18' => 0.12,  // 12% margin for 18K gold
                '21' => 0.10,  // 10% margin for 21K gold
                '22' => 0.08,  // 8% margin for 22K gold
                '24' => 0.05   // 5% margin for 24K gold
            ],
            'silver' => [
                '925' => 0.25, // 25% margin for sterling silver
                '950' => 0.20, // 20% margin for 950 silver
                '999' => 0.15  // 15% margin for fine silver
            ],
            'platinum' => [
                '900' => 0.20, // 20% margin for 900 platinum
                '950' => 0.15, // 15% margin for 950 platinum
                '999' => 0.10  // 10% margin for fine platinum
            ],
            'palladium' => [
                '500' => 0.30, // 30% margin for 500 palladium
                '950' => 0.20, // 20% margin for 950 palladium
                '999' => 0.15  // 15% margin for fine palladium
            ]
        ];

        return response()->json([
            'success' => true,
            'margins' => $defaultMargins[$metalSlug] ?? []
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching scrap margins',
            'margins' => []
        ], 500);
    }
}

public function getBullionPremium($metalSlug)
{
    try {
        // Default bullion sell premiums by metal
        $defaultPremiums = [
            'gold' => 0.08,      // 8% premium for gold bullion
            'silver' => 0.12,    // 12% premium for silver bullion
            'platinum' => 0.10,  // 10% premium for platinum bullion
            'palladium' => 0.15  // 15% premium for palladium bullion
        ];

        return response()->json([
            'success' => true,
            'sell_premium' => $defaultPremiums[$metalSlug] ?? 0.08
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching bullion premium',
            'sell_premium' => 0.08
        ], 500);
    }
}

public function getBullionMargin($metalSlug)
{
    try {
        // Default bullion buy margins by metal
        $defaultMargins = [
            'gold' => 0.05,      // 5% margin for gold bullion
            'silver' => 0.08,    // 8% margin for silver bullion
            'platinum' => 0.06,  // 6% margin for platinum bullion
            'palladium' => 0.10  // 10% margin for palladium bullion
        ];

        return response()->json([
            'success' => true,
            'buy_margin' => $defaultMargins[$metalSlug] ?? 0.05
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching bullion margin',
            'buy_margin' => 0.05
        ], 500);
    }
}

}
