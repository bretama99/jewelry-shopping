<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\MetalPriceApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $metalPriceService;

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->metalPriceService = $metalPriceService;
    }
 public function reports(){
    return view('admin.reports');
 }
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Basic statistics
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_metal_categories' => MetalCategory::count(),
            'active_metal_categories' => MetalCategory::where('is_active', true)->count(),
            'total_subcategories' => Subcategory::count(),
            'active_subcategories' => Subcategory::where('is_active', true)->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_customers' => User::customers()->count(), // Using User model with customers scope
            'featured_products' => Product::where('is_featured', true)->count(),
            'low_stock_products' => Product::where('is_active', true)
                ->whereColumn('stock_quantity', '<=', 'min_stock_level')
                ->count(),
        ];

        // Recent orders - Updated to use User relationship
        $recentOrders = Order::with(['user']) // Changed from 'customer' to 'user'
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                $order->total_items = $order->items->count();
                $order->customer_name = $order->user ?
                    $order->user->first_name . ' ' . $order->user->last_name :
                    'Unknown Customer';
                return $order;
            });

        // Recent products
        $recentProducts = Product::with(['metalCategory', 'subcategory'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($product) {
                $product->live_price = $product->calculateLivePrice();
                return $product;
            });

        // Low stock alerts
        $lowStockProducts = Product::with(['metalCategory', 'subcategory'])
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        // Metal category distribution
        $metalCategoryStats = MetalCategory::withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->where('is_active', true)
            ->orderBy('products_count', 'desc')
            ->get()
            ->map(function ($metal) {
                $metal->total_value = $metal->products()
                    ->where('is_active', true)
                    ->sum(DB::raw('stock_quantity * weight * labor_cost'));
                return $metal;
            });

        // Subcategory distribution
        $subcategoryStats = Subcategory::withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->where('is_active', true)
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        // Monthly sales data
        $monthlySales = $this->getMonthlySalesData();

        // Product by karat distribution
        $karatDistribution = Product::where('products.is_active', true)
            ->join('metal_categories', 'products.metal_category_id', '=', 'metal_categories.id')
            ->select('metal_categories.name as metal_name', 'products.karat', DB::raw('COUNT(*) as count'))
            ->groupBy('metal_categories.name', 'products.karat')
            ->orderBy('metal_categories.name')
            ->orderBy('products.karat')
            ->get();

        // Current metal prices with status
        $metalPrices = MetalCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($metal) {
                $metal->current_prices = $metal->getAllPrices();
                $metal->is_stale = $metal->isPriceStale();
                $metal->price_change = $this->calculatePriceChange($metal);
                return $metal;
            });

        // Order statistics by type
        $orderTypeStats = Order::select('tracking_number', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('tracking_number')
            ->get();

        // Revenue trends (last 30 days)
        $revenueTrends = Order::where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // System info
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_size' => $this->getDatabaseSize(),
            'cache_status' => $this->getCacheStatus(),
            'last_price_update' => MetalCategory::where('is_active', true)->max('updated_at'),
            'stale_price_count' => MetalCategory::where('is_active', true)
                ->where('updated_at', '<', Carbon::now()->subMinutes(30))
                ->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'recentProducts',
            'lowStockProducts',
            'metalCategoryStats',
            'subcategoryStats',
            'monthlySales',
            'karatDistribution',
            'metalPrices',
            'orderTypeStats',
            'revenueTrends',
            'systemInfo'
        ));
    }

    /**
     * Get monthly sales data.
     */
    protected function getMonthlySalesData()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $monthData = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('status', '!=', 'cancelled')
                ->selectRaw('
                    COUNT(*) as orders,
                    SUM(total_amount) as sales,
                    SUM(tax_amount) as tax,
                    AVG(total_amount) as avg_order_value
                ')
                ->first();

            $months[] = [
                'month' => $date->format('M'),
                'year' => $date->format('Y'),
                'sales' => $monthData->sales ?? 0,
                'orders' => $monthData->orders ?? 0,
                'tax' => $monthData->tax ?? 0,
                'avg_order_value' => $monthData->avg_order_value ?? 0,
            ];
        }
        return $months;
    }

    /**
     * Calculate price change for a metal category
     */
    protected function calculatePriceChange(MetalCategory $metal)
    {
        try {
            // Get cached previous price (you might want to store this in a separate table)
            $cacheKey = "previous_price_{$metal->symbol}";
            $previousPrice = Cache::get($cacheKey, $metal->current_price_usd);

            if ($previousPrice > 0) {
                $change = (($metal->current_price_usd - $previousPrice) / $previousPrice) * 100;
                return round($change, 2);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get dashboard analytics data via AJAX.
     */
    public function getAnalytics(Request $request)
    {
        $period = $request->get('period', '7d');

        switch ($period) {
            case '24h':
                $startDate = Carbon::now()->subDay();
                break;
            case '7d':
                $startDate = Carbon::now()->subWeek();
                break;
            case '30d':
                $startDate = Carbon::now()->subMonth();
                break;
            case '90d':
                $startDate = Carbon::now()->subDays(90);
                break;
            default:
                $startDate = Carbon::now()->subWeek();
        }

        // Product creation trends
        $productTrends = Product::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Order trends
        $orderTrends = Order::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Metal category performance
        $metalPerformance = MetalCategory::withCount(['products' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->where('is_active', true)
            ->orderBy('products_count', 'desc')
            ->get();

        // Top selling products (by order items)
        $topProducts = Product::select('products.*', DB::raw('SUM(order_items.weight * order_items.price_per_gram) as total_value'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startDate)
            ->where('orders.status', '!=', 'cancelled')
            ->with(['metalCategory', 'subcategory'])
            ->groupBy('products.id')
            ->orderBy('total_value', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'product_trends' => $productTrends,
            'order_trends' => $orderTrends,
            'metal_performance' => $metalPerformance,
            'top_products' => $topProducts,
            'period' => $period
        ]);
    }

    /**
     * Refresh metal prices manually.
     */
    public function refreshPrices()
    {
        try {
            $updated = [];
            $metalCategories = MetalCategory::where('is_active', true)->get();

            foreach ($metalCategories as $metal) {
                // Store previous price for change calculation
                $previousPrice = $metal->current_price_usd;
                Cache::put("previous_price_{$metal->symbol}", $previousPrice, 86400); // 24 hours

                // Try to get new price from API
                $priceData = $this->metalPriceService->getCurrentPrice($metal->symbol);

                if ($priceData['success']) {
                    $exchangeRate = $this->metalPriceService->getExchangeRate();
                    $metal->updatePriceFromApi($priceData['price'], $exchangeRate);

                    $updated[] = [
                        'metal' => $metal->name,
                        'symbol' => $metal->symbol,
                        'old_price' => $previousPrice,
                        'new_price' => $priceData['price'],
                        'change' => $priceData['price'] - $previousPrice,
                        'change_percent' => $previousPrice > 0 ? (($priceData['price'] - $previousPrice) / $previousPrice) * 100 : 0
                    ];
                }
            }

            // Clear related caches
            Cache::forget('dashboard_metal_prices');

            return response()->json([
                'success' => true,
                'message' => 'Metal prices updated successfully',
                'updated' => $updated,
                'updated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update metal prices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system status.
     */
    public function getSystemStatus()
    {
        $status = [
            'database' => $this->checkDatabaseConnection(),
            'cache' => $this->checkCacheStatus(),
            'storage' => $this->checkStorageStatus(),
            'api' => $this->checkApiStatus(),
            'metal_prices' => $this->checkMetalPriceStatus(),
        ];

        $overall = collect($status)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'overall_status' => $overall ? 'healthy' : 'issues',
            'checks' => $status,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get database size information
     */
    protected function getDatabaseSize()
    {
        try {
            $tables = [
                'products' => Product::count(),
                'metal_categories' => MetalCategory::count(),
                'subcategories' => Subcategory::count(),
                'orders' => Order::count(),
                'order_items' => OrderItem::count(),
                'users' => User::count(), // Changed from 'customers' to 'users'
            ];

            return $tables;
        } catch (\Exception $e) {
            return ['error' => 'Unable to calculate database size'];
        }
    }

    /**
     * Get cache status information
     */
    protected function getCacheStatus()
    {
        try {
            $cacheKeys = [
                'dashboard_metal_prices' => Cache::has('dashboard_metal_prices'),
                'usd_aud_rate' => Cache::has('usd_aud_rate'),
                'metal_prices' => Cache::has('metal_prices'),
            ];

            return [
                'active_keys' => array_filter($cacheKeys),
                'total_keys' => count($cacheKeys),
                'working' => true
            ];
        } catch (\Exception $e) {
            return ['working' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check database connection.
     */
    protected function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            $productCount = Product::count();
            return [
                'status' => 'ok',
                'message' => "Database connected successfully ({$productCount} products)"
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check cache status.
     */
    protected function checkCacheStatus()
    {
        try {
            $testKey = 'system_check_' . time();
            Cache::put($testKey, 'test', 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === 'test') {
                return ['status' => 'ok', 'message' => 'Cache is working properly'];
            } else {
                return ['status' => 'warning', 'message' => 'Cache test failed'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache system error'];
        }
    }

    /**
     * Check storage status.
     */
    protected function checkStorageStatus()
    {
        try {
            $storagePath = storage_path('app');
            $publicPath = public_path('images');

            $checks = [
                'storage_writable' => is_writable($storagePath),
                'public_writable' => is_writable($publicPath),
                'storage_space' => disk_free_space($storagePath)
            ];

            if ($checks['storage_writable'] && $checks['public_writable']) {
                $freeSpaceGB = round($checks['storage_space'] / (1024 * 1024 * 1024), 2);
                return [
                    'status' => 'ok',
                    'message' => "Storage is writable ({$freeSpaceGB}GB free)"
                ];
            } else {
                return ['status' => 'error', 'message' => 'Storage directories not writable'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Storage check failed'];
        }
    }

    /**
     * Check API status.
     */
    protected function checkApiStatus()
    {
        try {
            // Test the metal price API
            $testResponse = $this->metalPriceService->getCurrentPrice('XAU');

            if ($testResponse['success']) {
                return ['status' => 'ok', 'message' => 'Metal Price API is responding'];
            } else {
                return ['status' => 'warning', 'message' => 'Metal Price API returned error'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'API check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check metal price status.
     */
    protected function checkMetalPriceStatus()
    {
        try {
            $staleCount = MetalCategory::where('is_active', true)
                ->where('updated_at', '<', Carbon::now()->subMinutes(30))
                ->count();

            $totalCount = MetalCategory::where('is_active', true)->count();

            if ($staleCount === 0) {
                return [
                    'status' => 'ok',
                    'message' => "All {$totalCount} metal prices are current"
                ];
            } elseif ($staleCount < $totalCount) {
                return [
                    'status' => 'warning',
                    'message' => "{$staleCount} of {$totalCount} metal prices are stale"
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => "All {$totalCount} metal prices are stale"
                ];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Metal price check failed'];
        }
    }

    /**
     * Get inventory report
     */
    public function getInventoryReport()
    {
        try {
            $inventory = [
                'overview' => [
                    'total_products' => Product::where('is_active', true)->count(),
                    'low_stock_count' => Product::where('is_active', true)
                        ->whereColumn('stock_quantity', '<=', 'min_stock_level')
                        ->count(),
                    'out_of_stock' => Product::where('is_active', true)
                        ->where('stock_quantity', 0)
                        ->count(),
                    'total_value' => Product::where('is_active', true)
                        ->sum(DB::raw('stock_quantity * weight * labor_cost'))
                ],
                'by_metal' => MetalCategory::where('is_active', true)
                    ->withCount(['products' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->with(['products' => function ($query) {
                        $query->where('is_active', true)
                              ->select('metal_category_id', DB::raw('SUM(stock_quantity) as total_stock'))
                              ->groupBy('metal_category_id');
                    }])
                    ->get(),
                'low_stock_items' => Product::with(['metalCategory', 'subcategory'])
                    ->where('is_active', true)
                    ->whereColumn('stock_quantity', '<=', 'min_stock_level')
                    ->orderBy('stock_quantity', 'asc')
                    ->take(20)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'inventory' => $inventory,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate inventory report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales report
     */
    public function getSalesReport(Request $request)
    {
        try {
            $period = $request->get('period', '30d');

            switch ($period) {
                case '7d':
                    $startDate = Carbon::now()->subDays(7);
                    break;
                case '30d':
                    $startDate = Carbon::now()->subDays(30);
                    break;
                case '90d':
                    $startDate = Carbon::now()->subDays(90);
                    break;
                case '1y':
                    $startDate = Carbon::now()->subYear();
                    break;
                default:
                    $startDate = Carbon::now()->subDays(30);
            }

            $report = [
                'overview' => Order::where('created_at', '>=', $startDate)
                    ->where('status', '!=', 'cancelled')
                    ->selectRaw('
                        COUNT(*) as total_orders,
                        SUM(total_amount) as total_revenue,
                        AVG(total_amount) as avg_order_value,
                        SUM(tax_amount) as total_tax
                    ')
                    ->first(),
                'by_type' => Order::where('created_at', '>=', $startDate)
                    ->where('status', '!=', 'cancelled')
                    ->groupBy('order_type')
                    ->selectRaw('order_type, COUNT(*) as count, SUM(total_amount) as revenue')
                    ->get(),
                'by_metal' => OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('metal_categories', 'products.metal_category_id', '=', 'metal_categories.id')
                    ->where('orders.created_at', '>=', $startDate)
                    ->where('orders.status', '!=', 'cancelled')
                    ->groupBy('metal_categories.name')
                    ->selectRaw('
                        metal_categories.name,
                        COUNT(*) as items_sold,
                        SUM(order_items.total_price) as revenue,
                        SUM(order_items.weight) as total_weight
                    ')
                    ->get(),
                'daily_sales' => Order::where('created_at', '>=', $startDate)
                    ->where('status', '!=', 'cancelled')
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
                    ->orderBy('date')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'report' => $report,
                'period' => $period,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate sales report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export dashboard data
     */
    public function exportData(Request $request)
    {
        try {
            $type = $request->get('type', 'overview');

            switch ($type) {
                case 'products':
                    $data = Product::with(['metalCategory', 'subcategory'])
                        ->where('is_active', true)
                        ->get()
                        ->map(function ($product) {
                            return [
                                'ID' => $product->id,
                                'Name' => $product->name,
                                'SKU' => $product->sku,
                                'Metal' => $product->metalCategory->name ?? 'Unknown',
                                'Subcategory' => $product->subcategory->name ?? 'Unknown',
                                'Karat' => $product->karat,
                                'Weight' => $product->weight,
                                'Stock' => $product->stock_quantity,
                                'Min Stock' => $product->min_stock_level,
                                'Labor Cost' => $product->labor_cost,
                                'Live Price' => $product->calculateLivePrice(),
                                'Created' => $product->created_at->format('Y-m-d H:i:s')
                            ];
                        });
                    break;

                case 'orders':
                    $data = Order::with(['user', 'items']) // Changed from 'customer' to 'user'
                        ->latest()
                        ->take(1000)
                        ->get()
                        ->map(function ($order) {
                            return [
                                'Order Number' => $order->order_number,
                                'Customer' => $order->user ? // Changed from 'customer' to 'user'
                                    $order->user->first_name . ' ' . $order->user->last_name :
                                    'Unknown',
                                'Type' => $order->order_type,
                                'Status' => $order->status,
                                'Items Count' => $order->items->count(),
                                'Subtotal' => $order->subtotal,
                                'Tax' => $order->tax_amount,
                                'Total' => $order->total_amount,
                                'Created' => $order->created_at->format('Y-m-d H:i:s')
                            ];
                        });
                    break;

                default:
                    $data = [
                        'Export Time' => now()->format('Y-m-d H:i:s'),
                        'Total Products' => Product::count(),
                        'Active Products' => Product::where('is_active', true)->count(),
                        'Total Orders' => Order::count(),
                        'Total Users' => User::count(), // Changed from 'Total Customers' to 'Total Users'
                        'Metal Categories' => MetalCategory::where('is_active', true)->count(),
                        'Subcategories' => Subcategory::where('is_active', true)->count()
                    ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'type' => $type,
                'exported_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to export data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}