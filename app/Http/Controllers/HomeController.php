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

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->middleware('auth');
        $this->metalPriceService = $metalPriceService;
    }

    public function index()
    {
        // Get live metal prices (cached for 10 minutes)
        $goldPrices = Cache::remember('dashboard_gold_prices', 600, function () {
            return $this->fetchLiveGoldPrices();
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

        // Get popular categories (subcategories) - Fixed variable name
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

        // Get metal categories with their price information
        $metalCategories = MetalCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($metal) {
                // Add current prices if the method exists
                if (method_exists($metal, 'getAllPrices')) {
                    $metal->current_prices = $metal->getAllPrices();
                }
                $metal->last_update = $metal->updated_at;
                return $metal;
            });

        // Get featured products (top 8)
        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['metalCategory', 'subcategory'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(function ($product) {
                // Add live price calculation
                $product->live_price = $product->calculateLivePrice();
                
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

        // Get latest products (top 8)
        $latestProducts = Product::where('is_active', true)
            ->with(['metalCategory', 'subcategory'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(function ($product) {
                // Add live price calculation
                $product->live_price = $product->calculateLivePrice();
                
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
            'popularCategories', // Fixed variable name
            'metalCategories',
            'featuredProducts',
            'latestProducts'
        ));
    }

    private function fetchLiveGoldPrices()
    {
        try {
            // Get gold metal category
            $goldCategory = MetalCategory::where('symbol', 'XAU')->first();
            
            if ($goldCategory && method_exists($goldCategory, 'getAllPrices')) {
                return $goldCategory->getAllPrices();
            }
            
            // Fallback prices if no metal category or method exists
            return [
                '14' => 2800,
                '18' => 3200,
                '22' => 3800,
                '24' => 4200
            ];
            
        } catch (\Exception $e) {
            Log::warning('Failed to fetch live gold prices: ' . $e->getMessage());
            
            // Fallback prices
            return [
                '14' => 2800,
                '18' => 3200,
                '22' => 3800,
                '24' => 4200
            ];
        }
    }

    private function fetchLiveMetalPrices()
    {
        try {
            // Use MetalPriceAPI for all metals
            $response = Http::timeout(10)->get('https://api.metalpriceapi.com/v1/latest', [
                'api_key' => 'd68f51781cca05150ab380fbea59224c',
                'base' => 'USD',
                'currencies' => 'XAU,XAG,XPD,XPT'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] && isset($data['rates'])) {
                    $rates = $data['rates'];
                    $audRate = $this->getAudRate();

                    $allPrices = [];

                    // Process each metal
                    $metalMappings = [
                        'XAU' => 'gold',
                        'XAG' => 'silver',
                        'XPT' => 'platinum',
                        'XPD' => 'palladium'
                    ];

                    foreach ($metalMappings as $symbol => $slug) {
                        if (isset($rates['USD' . $symbol])) {
                            $metalCategory = MetalCategory::where('symbol', $symbol)->first();

                            if ($metalCategory && method_exists($metalCategory, 'updatePriceFromApi')) {
                                // Update database with new price
                                $metalCategory->updatePriceFromApi($rates['USD' . $symbol], $audRate);

                                // Get all calculated prices for this metal
                                if (method_exists($metalCategory, 'getAllPrices')) {
                                    $allPrices[$slug] = $metalCategory->getAllPrices();
                                }
                            }
                        }
                    }

                    return $allPrices;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch live metal prices from MetalPriceAPI: ' . $e->getMessage());
        }

        // Fallback to database prices
        return $this->getDatabasePrices();
    }

    private function getDatabasePrices()
    {
        $prices = [];
        $metalCategories = MetalCategory::where('is_active', true)->get();

        foreach ($metalCategories as $metal) {
            if (method_exists($metal, 'getAllPrices')) {
                $prices[$metal->slug] = $metal->getAllPrices();
            } else {
                // Fallback pricing structure
                $prices[$metal->slug] = [
                    '14' => 2800,
                    '18' => 3200,
                    '22' => 3800,
                    '24' => 4200
                ];
            }
        }

        return $prices;
    }

    private function getAudRate()
    {
        return Cache::remember('usd_aud_rate', 3600, function () {
            try {
                $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates']['AUD'] ?? 1.45;
                }
                return 1.45;
            } catch (\Exception $e) {
                Log::warning('Currency conversion failed: ' . $e->getMessage());
                return 1.45;
            }
        });
    }

    /**
     * Get metal price for specific metal and karat/purity
     *
     * @param string $metalSlug
     * @param string $karat
     * @return float
     */
    public function getMetalPrice($metalSlug = 'gold', $karat = '24')
    {
        $metalCategory = MetalCategory::where('slug', $metalSlug)->first();

        if ($metalCategory && method_exists($metalCategory, 'calculatePricePerGram')) {
            return $metalCategory->calculatePricePerGram($karat);
        }

        // Fallback to cached prices
        $metalPrices = Cache::get('dashboard_metal_prices', $this->fetchLiveMetalPrices());
        return $metalPrices[$metalSlug][$karat] ?? 0;
    }

    /**
     * Refresh metal prices manually (admin function)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshMetalPrices()
    {
        Cache::forget('dashboard_metal_prices');
        Cache::forget('dashboard_gold_prices');
        Cache::forget('usd_aud_rate');

        try {
            $metalPrices = $this->fetchLiveMetalPrices();
            Cache::put('dashboard_metal_prices', $metalPrices, 600);

            return response()->json([
                'success' => true,
                'prices' => $metalPrices,
                'message' => 'Metal prices updated successfully from MetalPriceAPI',
                'updated_at' => now()->toISOString()
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
     * Get live prices for all metals (API endpoint for frontend)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLivePrices()
    {
        try {
            $metalPrices = Cache::get('dashboard_metal_prices', $this->fetchLiveMetalPrices());

            // Add metadata
            $response = [
                'success' => true,
                'prices' => $metalPrices,
                'last_updated' => now()->toISOString(),
                'metals' => []
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
                
                // Add optional fields if they exist
                if (isset($metal->current_price_usd)) {
                    $metalData['current_price_usd'] = $metal->current_price_usd;
                }
                
                if (method_exists($metal, 'isPriceStale')) {
                    $metalData['is_stale'] = $metal->isPriceStale();
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
     * Calculate product price using database model (for AJAX calls)
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
            $karat = $request->custom_karat ?? $product->karat;

            // Use the product model's pricing calculation
            if (method_exists($product, 'getPriceBreakdown')) {
                $pricing = $product->getPriceBreakdown($weight);
            } else {
                // Fallback pricing calculation
                $pricing = [
                    'weight' => $weight,
                    'final_price' => $weight * 150 // Basic fallback calculation
                ];
            }

            if (!$pricing) {
                throw new \Exception('Unable to calculate pricing for this product');
            }

            return response()->json([
                'success' => true,
                'pricing' => $pricing,
                'formatted_price' => 'AUD$' . number_format($pricing['final_price'], 2),
                'weight' => $weight,
                'karat' => $karat,
                'metal_name' => $product->metalCategory->name ?? 'Unknown Metal',
                'metal_symbol' => $product->metalCategory->symbol ?? 'N/A'
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
     * Get available karats for a metal (API endpoint)
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

            // Check if method exists before calling
            if (method_exists($metalCategory, 'getAvailableKarats')) {
                $karats = $metalCategory->getAvailableKarats();
            } else {
                // Fallback karats based on metal symbol
                $karats = $metalCategory->symbol === 'XAG' ? ['925'] : ['14', '18', '22'];
            }
            
            $karatsWithPrices = [];

            foreach ($karats as $karat) {
                $pricePerGram = 0;
                $label = $karat;
                
                if (method_exists($metalCategory, 'calculatePricePerGram')) {
                    $pricePerGram = $metalCategory->calculatePricePerGram($karat);
                }
                
                if (method_exists($metalCategory, 'getKaratDisplayText')) {
                    $label = $metalCategory->getKaratDisplayText($karat);
                } else {
                    // Fallback label
                    $label = $metalCategory->symbol === 'XAG' 
                        ? ($karat === '925' ? 'Sterling Silver (925)' : 'Silver ' . $karat)
                        : $karat . 'K';
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
                ]
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
                        ->count()
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