<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use App\Models\Order;
use App\Services\MetalPriceApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected $metalPriceService;

    // Cache key for metal prices
    private const CACHE_KEY = 'metals_api_prices';
    private const CACHE_DURATION = 300; // 5 minutes

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->middleware('auth');
        $this->metalPriceService = $metalPriceService;
    }

    /**
     * Get all live metal prices with SINGLE API call and caching
     */
    private function getLiveMetalPrices()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                Log::info('Making single API call to metals-api.com for all metals');

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

                Log::info('Successfully fetched all metal prices in single API call', $rates);
                return $rates;

            } catch (\Exception $e) {
                Log::error('Failed to fetch metal prices: ' . $e->getMessage());
                throw $e; // Don't use fallback - force proper error handling
            }
        });
    }

    /**
     * Calculate price for specific metal and karat using cached data
     */
    private function calculateMetalPrice($metalSymbol, $karat)
    {
        $rates = $this->getLiveMetalPrices(); // Uses cache, no new API call

        if (!isset($rates[$metalSymbol])) {
            throw new \Exception("Metal symbol not found: {$metalSymbol}");
        }

        $purePricePerOz = $rates[$metalSymbol];
        $gramsPerTroyOz = 31.1035;
        $purePricePerGram = $purePricePerOz / $gramsPerTroyOz;

        // Calculate price for specific karat/purity
        if ($metalSymbol === 'XAU') {
            // Gold: karat/24
            $purityRatio = (int)$karat / 24;
        } elseif ($metalSymbol === 'XAG') {
            // Silver: purity ratios
            $purityRatio = $karat === '925' ? 0.925 : ($karat === '950' ? 0.950 : 0.999);
        } elseif ($metalSymbol === 'XPT') {
            // Platinum
            $purityRatio = $karat === '900' ? 0.900 : ($karat === '950' ? 0.950 : 0.999);
        } elseif ($metalSymbol === 'XPD') {
            // Palladium
            $purityRatio = $karat === '500' ? 0.500 : ($karat === '950' ? 0.950 : 0.999);
        } else {
            throw new \Exception("Unknown metal symbol: {$metalSymbol}");
        }

        return round($purePricePerGram * $purityRatio, 2);
    }

    /**
     * Calculate all prices for a metal category using cached data
     */
    private function calculateAllPricesForMetal($metalSymbol)
    {
        $rates = $this->getLiveMetalPrices(); // Uses cache, no new API call

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
    }

    public function index()
    {
        // Pre-fetch all metal prices with SINGLE API call
        $this->getLiveMetalPrices(); // This caches the data for all subsequent calls

        // Get live gold prices (uses cached data)
        $goldPrices = Cache::remember('dashboard_gold_prices', 600, function () {
            return $this->calculateAllPricesForMetal('XAU');
        });

        // Get statistics
        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
            'total_categories' => Subcategory::where('is_active', true)->count(),
            'total_metal_categories' => MetalCategory::where('is_active', true)->count(),
            'karat_varieties' => Product::distinct()->pluck('karat')->count(),
            'recent_orders' => Order::where('created_at', '>=', now()->subDays(7))->count(),
            'total_orders' => Order::count(),
            'price_range' => [
                'min' => Product::where('is_active', true)->min('labor_cost') ?? 100,
                'max' => Product::where('is_active', true)->max('labor_cost') ?? 5000,
            ]
        ];

        // Get popular categories (subcategories)
        $popularCategories = Subcategory::where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take(6)
            ->get()
            ->map(function ($subcategory) {
                $subcategory->active_products_count = $subcategory->products_count;
                return $subcategory;
            });

        // Get metal categories with their price information (uses cached data)
        $metalCategories = MetalCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($metal) {
                try {
                    $metal->current_prices = $this->calculateAllPricesForMetal($metal->symbol);
                } catch (\Exception $e) {
                    Log::warning("Failed to calculate prices for {$metal->symbol}: " . $e->getMessage());
                    $metal->current_prices = [];
                }
                $metal->last_update = $metal->updated_at;
                return $metal;
            });

        // Get featured products (uses cached data)
        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['metalCategory', 'subcategory'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(function ($product) {
                try {
                    if ($product->metalCategory && $product->karat) {
                        $product->live_price = $this->calculateMetalPrice($product->metalCategory->symbol, $product->karat);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to calculate live price for product {$product->id}: " . $e->getMessage());
                    $product->live_price = 0;
                }

                // Add category relationship for backwards compatibility
                if (!$product->category && $product->subcategory) {
                    $product->category = $product->subcategory;
                }

                // Add base_weight for backwards compatibility if weight exists
                if (!isset($product->base_weight) && isset($product->weight)) {
                    $product->base_weight = $product->weight;
                }

                return $product;
            });

        // Get latest products (uses cached data)
        $latestProducts = Product::where('is_active', true)
            ->with(['metalCategory', 'subcategory'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(function ($product) {
                try {
                    if ($product->metalCategory && $product->karat) {
                        $product->live_price = $this->calculateMetalPrice($product->metalCategory->symbol, $product->karat);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to calculate live price for product {$product->id}: " . $e->getMessage());
                    $product->live_price = 0;
                }

                // Add category relationship for backwards compatibility
                if (!$product->category && $product->subcategory) {
                    $product->category = $product->subcategory;
                }

                // Add base_weight for backwards compatibility if weight exists
                if (!isset($product->base_weight) && isset($product->weight)) {
                    $product->base_weight = $product->weight;
                }

                return $product;
            });

        return view('home.index', compact(
            'goldPrices',
            'stats',
            'popularCategories',
            'metalCategories',
            'featuredProducts',
            'latestProducts'
        ));
    }

    /**
     * Get metal price for specific metal and karat/purity - USES CACHED DATA
     *
     * @param string $metalSlug
     * @param string $karat
     * @return float
     */
    public function getMetalPrice($metalSlug = 'gold', $karat = '24')
    {
        try {
            $metalCategory = MetalCategory::where('slug', $metalSlug)->first();

            if (!$metalCategory) {
                throw new \Exception("Metal category not found: {$metalSlug}");
            }

            // Use cached data - no new API call
            return $this->calculateMetalPrice($metalCategory->symbol, $karat);

        } catch (\Exception $e) {
            Log::error("Failed to get price for {$metalSlug} {$karat}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refresh metal prices manually (admin function) - SINGLE API CALL
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshMetalPrices()
    {
        try {
            // Clear cache first
            Cache::forget(self::CACHE_KEY);
            Cache::forget('dashboard_metal_prices');
            Cache::forget('dashboard_gold_prices');

            // Fetch fresh data with single API call
            $rates = $this->getLiveMetalPrices();

            // Calculate all metal prices
            $allPrices = [
                'gold' => $this->calculateAllPricesForMetal('XAU'),
                'silver' => $this->calculateAllPricesForMetal('XAG'),
                'platinum' => $this->calculateAllPricesForMetal('XPT'),
                'palladium' => $this->calculateAllPricesForMetal('XPD'),
            ];

            // Cache the calculated prices
            Cache::put('dashboard_metal_prices', $allPrices, 600);

            return response()->json([
                'success' => true,
                'prices' => $allPrices,
                'message' => 'Metal prices updated successfully from metals-api.com (single API call)',
                'updated_at' => now()->toISOString(),
                'api_timestamp' => $rates['timestamp'],
                'fetched_at' => $rates['fetched_at']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update metal prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live prices for all metals (API endpoint for frontend) - USES CACHED DATA
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLivePrices()
    {
        try {
            $rates = $this->getLiveMetalPrices(); // Uses cache

            $allPrices = [
                'gold' => $this->calculateAllPricesForMetal('XAU'),
                'silver' => $this->calculateAllPricesForMetal('XAG'),
                'platinum' => $this->calculateAllPricesForMetal('XPT'),
                'palladium' => $this->calculateAllPricesForMetal('XPD'),
            ];

            // Add metadata
            $response = [
                'success' => true,
                'prices' => $allPrices,
                'last_updated' => $rates['fetched_at'],
                'api_timestamp' => $rates['timestamp'],
                'metals' => [],
                'api_source' => 'metals-api.com',
                'cache_status' => 'Using single API call with caching'
            ];

            // Add metal category information
            $metalCategories = MetalCategory::where('is_active', true)->get();
            foreach ($metalCategories as $metal) {
                $metalData = [
                    'name' => $metal->name,
                    'symbol' => $metal->symbol,
                    'slug' => $metal->slug,
                    'last_updated' => $metal->updated_at->toISOString(),
                ];

                // Add current price if available
                if (isset($metal->current_price_usd)) {
                    $metalData['current_price_aud'] = $metal->current_price_usd;
                }

                // Add available karats/purities
                if ($metal->symbol === 'XAU') {
                    $metalData['available_karats'] = ['9', '10', '14', '18', '21', '22', '24'];
                } elseif ($metal->symbol === 'XAG') {
                    $metalData['available_purities'] = ['925', '950', '999'];
                } elseif ($metal->symbol === 'XPT') {
                    $metalData['available_purities'] = ['900', '950', '999'];
                } elseif ($metal->symbol === 'XPD') {
                    $metalData['available_purities'] = ['500', '950', '999'];
                }

                $response['metals'][] = $metalData;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch live prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate product price using CACHED DATA (for AJAX calls)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateProductPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'custom_weight' => 'nullable|numeric|min:0.01',
            'custom_karat' => 'nullable|string'
        ]);

        try {
            $product = Product::with(['metalCategory', 'subcategory'])->findOrFail($request->product_id);
            $weight = $request->custom_weight ?? $product->weight ?? 1.0;
            $karat = $request->custom_karat ?? $product->karat ?? '18';

            if (!$product->metalCategory) {
                throw new \Exception('Product has no metal category assigned');
            }

            // Get price from cached data - no new API call
            $pricePerGram = $this->calculateMetalPrice($product->metalCategory->symbol, $karat);

            // Calculate pricing breakdown
            $metalValue = $weight * $pricePerGram;
            $laborCost = $weight * 15; // Default labor cost per gram
            $baseCost = $metalValue + $laborCost;
            $profitMargin = $baseCost * 0.25; // 25% profit margin
            $finalPrice = $baseCost + $profitMargin;

            $pricing = [
                'weight' => $weight,
                'metal_value' => round($metalValue, 2),
                'labor_cost' => round($laborCost, 2),
                'profit_margin' => round($profitMargin, 2),
                'final_price' => round($finalPrice, 2),
                'price_per_gram' => round($pricePerGram, 2)
            ];

            return response()->json([
                'success' => true,
                'pricing' => $pricing,
                'formatted_price' => 'AUD$' . number_format($pricing['final_price'], 2),
                'weight' => $weight,
                'karat' => $karat,
                'metal_name' => $product->metalCategory->name,
                'metal_symbol' => $product->metalCategory->symbol,
                'api_source' => 'metals-api.com (cached)',
                'cache_status' => 'Using cached data from single API call'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Price calculation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available karats for a metal using CACHED DATA (API endpoint)
     *
     * @param string $metalSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableKarats($metalSlug)
    {
        try {
            $metalCategory = MetalCategory::where('slug', $metalSlug)->where('is_active', true)->first();

            if (!$metalCategory) {
                return response()->json([
                    'success' => false,
                    'error' => 'Metal category not found'
                ], 404);
            }

            // Get correct karats based on metal symbol
            $karats = [];

            if ($metalCategory->symbol === 'XAU') {
                $karats = ['9', '10', '14', '18', '21', '22', '24'];
            } elseif ($metalCategory->symbol === 'XAG') {
                $karats = ['925', '950', '999'];
            } elseif ($metalCategory->symbol === 'XPT') {
                $karats = ['900', '950', '999'];
            } elseif ($metalCategory->symbol === 'XPD') {
                $karats = ['500', '950', '999'];
            }

            $karatsWithPrices = [];

            foreach ($karats as $karat) {
                try {
                    // Get price from cached data - no new API call
                    $pricePerGram = $this->calculateMetalPrice($metalCategory->symbol, $karat);
                } catch (\Exception $e) {
                    $pricePerGram = 0;
                    Log::warning("Failed to get price for {$metalSlug} {$karat}: " . $e->getMessage());
                }

                // Create display label
                if ($metalCategory->symbol === 'XAU') {
                    $purityPercent = ((int)$karat / 24 * 100);
                    $label = $karat . 'K Gold (' . number_format($purityPercent, 1) . '% Pure)';
                } elseif ($metalCategory->symbol === 'XAG') {
                    $purityPercent = $karat === '925' ? '92.5' : ($karat === '950' ? '95.0' : '99.9');
                    $label = $karat . ' Silver (' . $purityPercent . '% Pure)';
                } else {
                    $purityPercent = $karat === '999' ? '99.9' : ($karat === '950' ? '95.0' : ($karat === '900' ? '90.0' : '50.0'));
                    $label = $karat . ' ' . $metalCategory->name . ' (' . $purityPercent . '% Pure)';
                }

                $karatsWithPrices[] = [
                    'value' => $karat,
                    'label' => $label,
                    'price_per_gram' => $pricePerGram
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $karatsWithPrices,
                'metal' => [
                    'name' => $metalCategory->name,
                    'symbol' => $metalCategory->symbol,
                    'slug' => $metalCategory->slug
                ],
                'api_source' => 'metals-api.com (cached)',
                'cache_status' => 'Using cached data from single API call'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get karats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics (API endpoint)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats()
    {
        try {
            $stats = [
                'products' => [
                    'total' => Product::count(),
                    'active' => Product::where('is_active', true)->count(),
                    'featured' => Product::where('is_featured', true)->count(),
                    'by_metal' => Product::join('metal_categories', 'products.metal_category_id', '=', 'metal_categories.id')
                        ->where('products.is_active', true)
                        ->where('metal_categories.is_active', true)
                        ->groupBy('metal_categories.name')
                        ->selectRaw('metal_categories.name, COUNT(*) as count')
                        ->get()
                ],
                'categories' => [
                    'total_metals' => MetalCategory::where('is_active', true)->count(),
                    'total_subcategories' => Subcategory::where('is_active', true)->count(),
                    'by_subcategory' => Subcategory::withCount(['products' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->where('is_active', true)
                    ->orderBy('products_count', 'desc')
                    ->take(5)
                    ->get()
                ],
                'orders' => [
                    'total' => Order::count(),
                    'today' => Order::whereDate('created_at', today())->count(),
                    'this_week' => Order::where('created_at', '>=', now()->startOfWeek())->count(),
                    'this_month' => Order::where('created_at', '>=', now()->startOfMonth())->count(),
                ],
                'prices' => [
                    'last_updated' => MetalCategory::where('is_active', true)->max('updated_at'),
                    'stale_count' => MetalCategory::where('is_active', true)
                        ->where('updated_at', '<', now()->subMinutes(30))
                        ->count(),
                    'api_source' => 'metals-api.com (single call + cache)',
                    'cache_status' => Cache::has(self::CACHE_KEY) ? 'Active' : 'Expired'
                ]
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get dashboard statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * About page
     */
    public function about()
    {
        $metalCategories = MetalCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('home.about', compact('metalCategories'));
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return view('home.contact');
    }

    /**
     * Privacy policy page
     */
    public function privacy()
    {
        return view('home.privacy');
    }

    /**
     * Terms of service page
     */
    public function terms()
    {
        return view('home.terms');
    }
}
