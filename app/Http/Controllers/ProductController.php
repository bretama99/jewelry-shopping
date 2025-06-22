<?php
// File: app/Http/Controllers/ProductController.php - COMPLETE CORRECTED VERSION
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Services\MetalPriceApiService;
use App\Services\KitcoApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $metalPriceService;
    protected $kitcoApiService;

    public function __construct(MetalPriceApiService $metalPriceService, KitcoApiService $kitcoApiService)
    {
        $this->metalPriceService = $metalPriceService;
        $this->kitcoApiService = $kitcoApiService;
    }

    /**
     * Display all products with dynamic filtering (Gold Trading Interface)
     */
    public function index(Request $request)
    {
        try {
            // Get metal categories - SIMPLIFIED
            $metalCategories = MetalCategory::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            // Get subcategories - SIMPLIFIED
            $subcategories = Subcategory::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            // Build products query - SIMPLIFIED
            $query = Product::with(['metalCategory', 'subcategory'])
                ->where('is_active', true);

            // Apply filters - SIMPLIFIED and FIXED
            if ($request->filled('metal')) {
                $metalCategory = $metalCategories->where('slug', $request->metal)->first();
                if ($metalCategory) {
                    $query->where('metal_category_id', $metalCategory->id);
                }
            }

            if ($request->filled('subcategory') && $request->subcategory !== 'all') {
                // Check if subcategory has slug field, otherwise use name
                $subcategory = $subcategories->where('slug', $request->subcategory)->first();
                if (!$subcategory) {
                    // Fallback to name if slug doesn't exist
                    $subcategory = $subcategories->where('name', 'like', '%' . $request->subcategory . '%')->first();
                }
                if ($subcategory) {
                    $query->where('subcategory_id', $subcategory->id);
                }
            }

            if ($request->filled('karat')) {
                $query->where('karat', $request->karat);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            // Apply price range filters (simplified)
            if ($request->filled('price_min')) {
                $query->whereRaw('weight * 100 >= ?', [$request->price_min]); // Simplified pricing
            }

            if ($request->filled('price_max')) {
                $query->whereRaw('weight * 100 <= ?', [$request->price_max]); // Simplified pricing
            }

            // Apply sorting - SIMPLIFIED
            $sort = $request->get('sort', 'name');
            switch ($sort) {
                case 'price':
                case 'price_low':
                    $query->orderBy('weight', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('weight', 'desc');
                    break;
                case 'featured':
                    $query->orderBy('is_featured', 'desc')
                          ->orderBy('sort_order', 'asc')
                          ->orderBy('name', 'asc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('sort_order', 'asc')
                          ->orderBy('name', 'asc');
            }

            // Get paginated results
            $products = $query->paginate(16)->withQueryString();

            // Get current metal prices - SIMPLIFIED
            $metalPrices = Cache::remember('metal_prices_display', 300, function () use ($metalCategories) {
                return $this->getCurrentMetalPrices($metalCategories);
            });

            // Get available karats - SIMPLIFIED
            $availableKarats = [];
            if ($request->filled('metal')) {
                $metalCategory = $metalCategories->where('slug', $request->metal)->first();
                if ($metalCategory) {
                    $availableKarats = $metalCategory->getAvailableKarats();
                }
            }

            // Prepare JS data - SIMPLIFIED
            $jsData = [
                'metalPricesFromDB' => $metalPrices,
                'subcategoryLaborCosts' => $this->getSubcategoryLaborCosts($subcategories),
                'subcategoryProfitMargins' => $this->getSubcategoryProfitMargins($subcategories),
                'currentMetalSlug' => $request->get('metal', $metalCategories->first()?->slug ?? 'gold')
            ];

            return view('products.index', compact(
                'products',
                'metalCategories',
                'subcategories',
                'metalPrices',
                'availableKarats',
                'jsData'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading products interface: ' . $e->getMessage());

            // Create empty paginator
            $products = new LengthAwarePaginator(
                collect([]),
                0,
                16,
                $request->get('page', 1),
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );

            return view('products.index', [
                'products' => $products,
                'metalCategories' => collect(),
                'subcategories' => collect(),
                'metalPrices' => [],
                'availableKarats' => [],
                'jsData' => [
                    'metalPricesFromDB' => [],
                    'subcategoryLaborCosts' => [],
                    'subcategoryProfitMargins' => [],
                    'currentMetalSlug' => 'gold'
                ]
            ])->with('error', 'Unable to load products. Please try again.');
        }
    }

    /**
     * Display a specific product
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load(['metalCategory', 'subcategory']);

        // Get live pricing using the database model method
        $livePricing = $product->getPriceBreakdown();

        // Get available weight options
        $weightOptions = $this->generateWeightOptions($product);

        // Get available karat options for this metal from database
        $karatOptions = [];
        if ($product->metalCategory) {
            $availableKarats = $product->metalCategory->getAvailableKarats();
            foreach ($availableKarats as $karat) {
                $karatOptions[] = [
                    'value' => $karat,
                    'label' => $product->metalCategory->getKaratDisplayText($karat),
                    'multiplier' => $product->metalCategory->purity_ratios[$karat] ?? 1.0
                ];
            }
        }

        // Get related products
        $relatedProducts = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('subcategory_id', $product->subcategory_id)
                      ->orWhere('metal_category_id', $product->metal_category_id);
            })
            ->limit(4)
            ->get();

        return view('products.show', compact(
            'product',
            'livePricing',
            'weightOptions',
            'karatOptions',
            'relatedProducts'
        ));
    }

    /**
     * Get live price calculation for product using database model
     */
    public function getLivePrice(Request $request, Product $product)
    {
        try {
            $weight = $request->get('weight', $product->weight);
            $karat = $request->get('karat', $product->karat);

            // Use the product model's pricing calculation
            $pricing = $product->getPriceBreakdown($weight);

            if (!$pricing) {
                throw new \Exception('Unable to calculate pricing');
            }

            return response()->json([
                'success' => true,
                'pricing' => $pricing,
                'formatted_price' => 'AUD$' . number_format($pricing['final_price'], 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'fallback_price' => $product->calculateLivePrice($weight ?? null)
            ]);
        }
    }

    /**
     * Get current metal prices from database/API
     */
    protected function getCurrentMetalPrices($metalCategories = null)
    {
        try {
            if (!$metalCategories) {
                $metalCategories = MetalCategory::where('is_active', true)->get();
            }

            $prices = [];
            foreach ($metalCategories as $metal) {
                // Try to get fresh prices from API if stale
                if ($metal->isPriceStale()) {
                    try {
                        $priceData = $this->kitcoApiService->getCurrentPrice($metal->symbol);
                        if ($priceData['success']) {
                            $exchangeRate = $this->metalPriceService->getExchangeRate();
                            $metal->updatePriceFromApi($priceData['price'], $exchangeRate);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to update price for ' . $metal->symbol . ': ' . $e->getMessage());
                    }
                }

                // Get all prices for different karats/purities
                $prices[$metal->slug] = $metal->getAllPrices();
            }

            return $prices;

        } catch (\Exception $e) {
            Log::error('Error fetching metal prices: ' . $e->getMessage());

            // Return fallback prices from database
            $prices = [];
            $metalCategories = $metalCategories ?? MetalCategory::where('is_active', true)->get();

            foreach ($metalCategories as $metal) {
                $prices[$metal->slug] = $metal->getAllPrices();
            }

            return $prices;
        }
    }

    /**
     * Update metal prices from API
     */
    public function updatePrices(Request $request)
    {
        try {
            $updated = [];
            $metalCategories = MetalCategory::where('is_active', true)->get();

            foreach ($metalCategories as $metal) {
                $priceData = $this->kitcoApiService->getCurrentPrice($metal->symbol);

                if ($priceData['success']) {
                    $oldPrice = $metal->current_price_usd;
                    $exchangeRate = $this->metalPriceService->getExchangeRate();
                    $metal->updatePriceFromApi($priceData['price'], $exchangeRate);

                    $updated[] = [
                        'metal' => $metal->name,
                        'symbol' => $metal->symbol,
                        'old_price' => $oldPrice,
                        'new_price' => $priceData['price'],
                        'change' => $priceData['price'] - $oldPrice,
                        'change_percent' => $oldPrice > 0 ? (($priceData['price'] - $oldPrice) / $oldPrice) * 100 : 0
                    ];
                }
            }

            // Clear the cache
            Cache::forget('metal_prices');
            Cache::forget('metal_prices_display');

            return response()->json([
                'success' => true,
                'message' => 'Metal prices updated successfully',
                'updated' => $updated,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating metal prices: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to update metal prices',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available karats for a metal category (API endpoint)
     */
    public function getAvailableKarats(Request $request, $metalSlug)
    {
        try {
            $metalCategory = MetalCategory::where('slug', $metalSlug)
                ->where('is_active', true)
                ->first();

            if (!$metalCategory) {
                return response()->json([
                    'success' => false,
                    'error' => 'Metal category not found'
                ], 404);
            }

            $karats = $metalCategory->getAvailableKarats();
            $karatsWithPrices = [];

            foreach ($karats as $karat) {
                $karatsWithPrices[] = [
                    'value' => $karat,
                    'label' => $metalCategory->getKaratDisplayText($karat),
                    'price_per_gram' => $metalCategory->calculatePricePerGram($karat)
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
     * Get subcategories for a metal category (API endpoint)
     */
    public function getSubcategoriesForMetal(Request $request, $metalSlug)
    {
        try {
            $metalCategory = MetalCategory::where('slug', $metalSlug)
                ->where('is_active', true)
                ->first();

            if (!$metalCategory) {
                return response()->json([
                    'success' => false,
                    'error' => 'Metal category not found'
                ], 404);
            }

            // Get subcategories - FIXED for missing relationship
            $subcategories = Subcategory::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories->map(function ($subcategory) use ($metalCategory) {
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug ?? Str::slug($subcategory->name),
                        'products_count' => $subcategory->products()->where('is_active', true)->count(),
                        'labor_cost' => $this->getLaborCostForMetal($subcategory, $metalCategory->id),
                        'profit_margin' => $this->getProfitMarginForMetal($subcategory, $metalCategory->id)
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get subcategories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate scrap price (API endpoint)
     */
    public function calculateScrapPrice(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'metal_slug' => 'required|string',
                'weight' => 'required|numeric|min:0.1',
                'karat' => 'required|string',
            ]);

            $metalCategory = MetalCategory::where('slug', $validatedData['metal_slug'])
                ->where('is_active', true)
                ->first();

            if (!$metalCategory) {
                return response()->json([
                    'success' => false,
                    'error' => 'Metal category not found'
                ], 404);
            }

            $weight = $validatedData['weight'];
            $karat = $validatedData['karat'];

            // Get current price for this karat
            $pricePerGram = $metalCategory->calculatePricePerGram($karat);
            $grossValue = $weight * $pricePerGram;

            // Apply scrap deductions (configurable)
            $processingFee = 0.15; // 15%
            $margins = [
                'gold' => ['9' => 0.12, '10' => 0.12, '14' => 0.10, '18' => 0.08, '21' => 0.06, '22' => 0.05],
                'silver' => ['925' => 0.10, '950' => 0.08, '999' => 0.05],
                'platinum' => ['900' => 0.10, '950' => 0.08, '999' => 0.05],
                'palladium' => ['950' => 0.08, '999' => 0.05]
            ];

            $metalSlug = $metalCategory->slug;
            $margin = $margins[$metalSlug][$karat] ?? 0.10;

            $processingDeduction = $grossValue * $processingFee;
            $marginDeduction = $grossValue * $margin;
            $totalDeductions = $processingDeduction + $marginDeduction;
            $offerValue = $grossValue - $totalDeductions;

            return response()->json([
                'success' => true,
                'data' => [
                    'weight' => $weight,
                    'karat' => $karat,
                    'price_per_gram' => $pricePerGram,
                    'gross_value' => round($grossValue, 2),
                    'processing_fee_percent' => $processingFee * 100,
                    'processing_deduction' => round($processingDeduction, 2),
                    'margin_percent' => $margin * 100,
                    'margin_deduction' => round($marginDeduction, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'offer_value' => round($offerValue, 2),
                    'offer_per_gram' => round($offerValue / $weight, 2)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to calculate scrap price',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers for checkout
     */
    public function searchCustomers(Request $request)
    {
        try {
            $query = $request->get('query', '');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query too short'
                ]);
            }

            $customers = Customer::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('passport_id_number', 'like', "%{$query}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
            })
            ->limit(10)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'full_name' => $customer->first_name . ' ' . $customer->last_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'passport_id_number' => $customer->passport_id_number,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching customers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'customer_first_name' => 'required|string|max:255',
                'customer_last_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_passport_id' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000',
                'order_type' => 'required|string|in:jewelry,scrap,bullion,mixed',
                'items' => 'required|array|min:1',
                'items.*.type' => 'required|string|in:jewelry,scrap,bullion',
                'items.*.product_id' => 'nullable|string',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.weight' => 'required|numeric|min:0.1',
                'items.*.karat' => 'required|string',
                'items.*.price_per_gram' => 'required|numeric|min:0',
                'items.*.total_price' => 'required|numeric',
            ]);

            DB::beginTransaction();

            // Find or create customer
            $customer = Customer::where('email', $validatedData['customer_email'])->first();

            if (!$customer) {
                $customer = Customer::create([
                    'first_name' => $validatedData['customer_first_name'],
                    'last_name' => $validatedData['customer_last_name'],
                    'email' => $validatedData['customer_email'],
                    'phone' => $validatedData['customer_phone'],
                    'passport_id_number' => $validatedData['customer_passport_id'],
                ]);
            }

            // Calculate totals
            $totalRevenue = 0;
            $totalExpenses = 0;

            foreach ($validatedData['items'] as $item) {
                if ($item['type'] === 'scrap' || ($item['type'] === 'bullion' && isset($item['subtype']) && $item['subtype'] === 'buy')) {
                    $totalExpenses += abs($item['total_price']);
                } else {
                    $totalRevenue += abs($item['total_price']);
                }
            }

            $netSubtotal = $totalRevenue - $totalExpenses;
            $tax = max(0, $totalRevenue * 0.10); // 10% GST on sales only
            $shipping = $totalRevenue > 500 ? 0 : ($totalRevenue > 0 ? 25 : 0);
            $finalTotal = $netSubtotal + $tax + $shipping;

            // Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'order_type' => $validatedData['order_type'],
                'status' => 'processing',
                'subtotal' => $netSubtotal,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'total_amount' => $finalTotal,
                'notes' => $validatedData['notes'],
                'metal_prices_snapshot' => $this->getCurrentMetalPrices(),
            ]);

            // Create order items
            foreach ($validatedData['items'] as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'type' => $item['type'],
                    'subtype' => $item['subtype'] ?? null,
                    'name' => $item['product_name'],
                    'weight' => $item['weight'],
                    'karat' => $item['karat'],
                    'price_per_gram' => $item['price_per_gram'],
                    'total_price' => $item['total_price'],
                    'metal_category' => $item['category_name'] ?? null,
                    'description' => $item['description'] ?? null,
                ]);

                // Update product stock if it's a jewelry item
                if ($item['type'] === 'jewelry' && !empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->decrementStock(1);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order->load('customer', 'items'),
                    'order_number' => $order->order_number
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate weight options for product customization
     */
    protected function generateWeightOptions(Product $product)
    {
        $options = [];
        $current = $product->min_weight ?? 0.5;
        $max = $product->max_weight ?? 10;
        $step = $product->weight_step ?? 0.1;

        while ($current <= $max) {
            $options[] = [
                'value' => $current,
                'label' => number_format($current, 1) . 'g'
            ];
            $current += $step;
        }

        return $options;
    }

    /**
     * Get labor costs for subcategories - FIXED
     */
    protected function getSubcategoryLaborCosts($subcategories)
    {
        $laborCosts = [];
        foreach ($subcategories as $subcategory) {
            $slug = $subcategory->slug ?? Str::slug($subcategory->name);
            $laborCosts[$slug] = $subcategory->default_labor_cost ?? 15.00;
        }
        return $laborCosts;
    }

    /**
     * Get profit margins for subcategories - FIXED
     */
    protected function getSubcategoryProfitMargins($subcategories)
    {
        $profitMargins = [];
        foreach ($subcategories as $subcategory) {
            $slug = $subcategory->slug ?? Str::slug($subcategory->name);
            $profitMargins[$slug] = 25.00; // Default 25% since your model doesn't have this field
        }
        return $profitMargins;
    }

    /**
     * Get labor cost for specific metal - HELPER METHOD
     */
    protected function getLaborCostForMetal($subcategory, $metalCategoryId)
    {
        // Since your subcategory model doesn't have the complex relationship, return default
        return $subcategory->default_labor_cost ?? 15.00;
    }

    /**
     * Get profit margin for specific metal - HELPER METHOD
     */
    protected function getProfitMarginForMetal($subcategory, $metalCategoryId)
    {
        // Since your subcategory model doesn't have this field, return default
        return 25.00;
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}