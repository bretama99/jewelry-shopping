<?php
// app/Http/Controllers/Admin/ProductController.php - Complete updated version

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use App\Services\MetalPriceApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $metalPriceService;

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->metalPriceService = $metalPriceService;
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['metalCategory', 'subcategory']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('sku', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('metal_category')) {
                $query->where('metal_category_id', $request->metal_category);
            }

            if ($request->filled('subcategory')) {
                $query->where('subcategory_id', $request->subcategory);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'featured') {
                    $query->where('is_featured', true);
                }
            }

            if ($request->filled('karat')) {
                $query->where('karat', $request->karat);
            }

            // Apply sorting
            switch ($request->get('sort', 'name')) {
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'created_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'featured':
                    $query->orderBy('is_featured', 'desc')->orderBy('name', 'asc');
                    break;
                case 'price_high':
                case 'price_low':
                    $direction = $request->get('sort') === 'price_high' ? 'desc' : 'asc';
                    $query->orderByRaw('COALESCE(weight, 0) ' . $direction);
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }

            $products = $query->paginate(20)->appends($request->query());

            // Get filter options
            $metalCategories = MetalCategory::active()->ordered()->get();
            $subcategories = Subcategory::active()->ordered()->get();

            // Calculate stats
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'featured_products' => Product::where('is_featured', true)->count(),
                'metal_categories_count' => MetalCategory::where('is_active', true)->count(),
                'subcategories_count' => Subcategory::where('is_active', true)->count(),
            ];

            return view('admin.products.index', compact('products', 'metalCategories', 'subcategories', 'stats'));

        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Error loading products: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $metalCategories = MetalCategory::active()->ordered()->get();
        $subcategories = Subcategory::active()->ordered()->get();

        return view('admin.products.create', compact('metalCategories', 'subcategories'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'metal_category_id' => 'required|exists:metal_categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku',
            'slug' => 'nullable|string|unique:products,slug',
            'karat' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0.001',
            'min_weight' => 'nullable|numeric|min:0.001',
            'max_weight' => 'nullable|numeric|min:0.001',
            'weight_step' => 'nullable|numeric|min:0.001|max:10',
            'labor_cost' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:3072',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $data = $request->all();

            // Validate karat against metal category only if karat is provided
            if ($request->karat && $request->metal_category_id) {
                $metalCategory = MetalCategory::find($request->metal_category_id);
                $availableKarats = $metalCategory->getAvailableKarats();
                if (!in_array($request->karat, $availableKarats)) {
                    return back()->withInput()
                               ->with('error', 'Invalid karat selection for ' . $metalCategory->name);
                }
            }

            // Validate weight relationships only if weights are provided
            if ($request->min_weight && $request->max_weight && $request->min_weight >= $request->max_weight) {
                return back()->withInput()
                           ->with('error', 'Maximum weight must be greater than minimum weight.');
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/products');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $filename);
                $data['image'] = $filename;
            }

            // Handle checkboxes
            $data['is_active'] = $request->has('is_active');
            $data['is_featured'] = $request->has('is_featured');

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = 'PRD-' . strtoupper(Str::random(8));
            }

            Product::create($data);

            return redirect()->route('admin.products.index')
                           ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['metalCategory', 'subcategory']);

        // Get current price calculation
        $priceBreakdown = $product->getPriceBreakdown();

        return view('admin.products.show', compact('product', 'priceBreakdown'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $metalCategories = MetalCategory::active()->ordered()->get();
        $subcategories = Subcategory::active()->ordered()->get();
        $product->load(['metalCategory', 'subcategory']);

        return view('admin.products.edit', compact('product', 'metalCategories', 'subcategories'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'metal_category_id' => 'required|exists:metal_categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'karat' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0.001',
            'min_weight' => 'nullable|numeric|min:0.001',
            'max_weight' => 'nullable|numeric|min:0.001',
            'weight_step' => 'nullable|numeric|min:0.001|max:10',
            'labor_cost' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $data = $request->all();

            // Validate karat against metal category only if karat is provided
            if ($request->karat && $request->metal_category_id) {
                $metalCategory = MetalCategory::find($request->metal_category_id);
                $availableKarats = $metalCategory->getAvailableKarats();
                if (!in_array($request->karat, $availableKarats)) {
                    return back()->withInput()
                               ->with('error', 'Invalid karat selection for ' . $metalCategory->name);
                }
            }

            // Validate weight relationships only if weights are provided
            if ($request->min_weight && $request->max_weight && $request->min_weight >= $request->max_weight) {
                return back()->withInput()
                           ->with('error', 'Maximum weight must be greater than minimum weight.');
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    $oldImagePath = public_path('images/products/' . $product->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/products');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $filename);
                $data['image'] = $filename;
            }

            // Handle checkboxes
            $data['is_active'] = $request->has('is_active');
            $data['is_featured'] = $request->has('is_featured');

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $product->update($data);

            return redirect()->route('admin.products.index')
                           ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        try {
            // Check if product has orders
            if ($product->orderItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing orders.'
                ], 400);
            }

            // Delete image if exists
            if ($product->image) {
                $imagePath = public_path('images/products/' . $product->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product pricing
     */
    public function updatePricing(Request $request, Product $product)
    {
        $request->validate([
            'labor_cost' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            $product->labor_cost = $request->labor_cost;
            $product->profit_margin = $request->profit_margin;
            $product->save();

            $newPrice = $product->calculateLivePrice();

            return response()->json([
                'success' => true,
                'message' => 'Pricing updated successfully.',
                'new_price' => $newPrice,
                'formatted_price' => 'AUD ' . number_format($newPrice, 2),
                'weight' => $product->weight
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating pricing.'
            ], 500);
        }
    }

    /**
     * Get current pricing info for product
     */
    public function getPricing(Product $product)
    {
        try {
            $priceBreakdown = $product->getPriceBreakdown();

            return response()->json([
                'success' => true,
                'pricing' => $priceBreakdown,
                'metal_name' => $product->metalCategory?->name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pricing info.'
            ], 500);
        }
    }

    /**
     * Get available karats for selected metal category (AJAX)
     */
    public function getAvailableKarats($metalCategoryId)
    {
        try {
            $metalCategory = MetalCategory::findOrFail($metalCategoryId);
            $karats = $metalCategory->getAvailableKarats();

            return response()->json([
                'success' => true,
                'karats' => $karats,
                'metal_name' => $metalCategory->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available karats'
            ], 500);
        }
    }

    /**
     * Get subcategories for selected metal category (AJAX)
     */
    public function getSubcategoriesForMetal($metalCategoryId)
    {
        try {
            $metalCategory = MetalCategory::findOrFail($metalCategoryId);
            $subcategories = $metalCategory->activeSubcategories()
                                         ->orderBy('sort_order')
                                         ->orderBy('name')
                                         ->get(['id', 'name', 'slug']);

            return response()->json([
                'success' => true,
                'subcategories' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subcategories'
            ], 500);
        }
    }

    /**
     * Calculate live price for product configuration (AJAX)
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'metal_category_id' => 'required|exists:metal_categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'karat' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0.01',
            'labor_cost' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            $metalCategory = MetalCategory::find($request->metal_category_id);
            $subcategory = Subcategory::find($request->subcategory_id);

            // Use default weight if not provided
            $weight = $request->weight ?? 1.0;

            // Validate karat only if provided
            if ($request->karat) {
                $availableKarats = $metalCategory->getAvailableKarats();
                if (!in_array($request->karat, $availableKarats)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid karat for selected metal'
                    ], 400);
                }
                $karat = $request->karat;
            } else {
                // Use a default karat for the metal type
                $availableKarats = $metalCategory->getAvailableKarats();
                $karat = $availableKarats[0] ?? '18'; // Use first available or default to 18
            }

            // Calculate price
            $pricePerGram = $metalCategory->calculatePricePerGram($karat);
            $metalValue = $weight * $pricePerGram;

            // Get labor cost (use override or default)
            $laborCost = $request->labor_cost ?? $subcategory->getLaborCostForMetal($request->metal_category_id);
            $totalLaborCost = $weight * $laborCost;

            // Get profit margin (use override or default)
            $profitMargin = $request->profit_margin ?? $subcategory->getProfitMarginForMetal($request->metal_category_id);

            $baseCost = $metalValue + $totalLaborCost;
            $profitAmount = $baseCost * ($profitMargin / 100);
            $finalPrice = $baseCost + $profitAmount;

            return response()->json([
                'success' => true,
                'calculation' => [
                    'metal_value' => round($metalValue, 2),
                    'labor_cost' => round($totalLaborCost, 2),
                    'base_cost' => round($baseCost, 2),
                    'profit_amount' => round($profitAmount, 2),
                    'final_price' => round($finalPrice, 2),
                    'price_per_gram' => round($pricePerGram, 2),
                    'weight' => $weight,
                    'karat' => $karat,
                    'metal_name' => $metalCategory->name,
                    'subcategory_name' => $subcategory->name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'product_ids' => 'required|array'
        ]);

        try {
            $products = Product::whereIn('id', $request->product_ids);

            switch ($request->action) {
                case 'activate':
                    $products->update(['is_active' => true]);
                    $message = 'Products activated successfully.';
                    break;

                case 'deactivate':
                    $products->update(['is_active' => false]);
                    $message = 'Products deactivated successfully.';
                    break;

                case 'feature':
                    $products->update(['is_featured' => true]);
                    $message = 'Products marked as featured.';
                    break;

                case 'unfeature':
                    $products->update(['is_featured' => false]);
                    $message = 'Products unmarked as featured.';
                    break;

                case 'refresh_prices':
                    // Force refresh metal prices and recalculate
                    $this->metalPriceService->forceRefreshPrices();
                    $message = 'Prices refreshed successfully.';
                    break;

                default:
                    throw new \Exception('Invalid action.');
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products
     */
    public function export()
    {
        try {
            $products = Product::with(['metalCategory', 'subcategory'])->get();

            $filename = 'products_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function() use ($products) {
                $handle = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($handle, [
                    'ID', 'Name', 'SKU', 'Metal Category', 'Subcategory', 'Karat', 'Weight (g)',
                    'Min Weight (g)', 'Max Weight (g)', 'Weight Step', 'Labor Cost', 'Profit Margin',
                    'Current Price (AUD)', 'Stock Quantity', 'Status', 'Featured', 'Created At'
                ]);

                // Add data rows
                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->id,
                        $product->name,
                        $product->sku,
                        $product->metalCategory?->name ?? 'N/A',
                        $product->subcategory?->name ?? 'N/A',
                        $product->karat ?? 'N/A',
                        $product->weight ?? 'N/A',
                        $product->min_weight ?? 'N/A',
                        $product->max_weight ?? 'N/A',
                        $product->weight_step ?? 'N/A',
                        $product->labor_cost ?? 'N/A',
                        $product->profit_margin ?? 'N/A',
                        $product->calculateLivePrice(),
                        $product->stock_quantity ?? 'Unlimited',
                        $product->is_active ? 'Active' : 'Inactive',
                        $product->is_featured ? 'Yes' : 'No',
                        $product->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            }, 200, $headers);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export products: ' . $e->getMessage());
        }
    }

    /**
     * Get current metal prices for display
     */
    public function getMetalPrices()
    {
        try {
            $metalPrices = $this->metalPriceService->getAllMetalPricesAUD();

            return response()->json([
                'success' => true,
                'prices' => $metalPrices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching metal prices'
            ], 500);
        }
    }

    /**
     * Update all metal prices from API
     */
    public function updateMetalPrices()
    {
        try {
            $updateCount = $this->metalPriceService->forceRefreshPrices();

            return response()->json([
                'success' => true,
                'message' => "Updated prices for {$updateCount} metals",
                'prices' => $this->metalPriceService->getAllMetalPricesAUD()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating metal prices: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiIndex()
{
    try {
        $products = Product::where('is_active', true)
            ->with(['metalCategory', 'subcategory'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'metal_slug' => $product->metalCategory?->slug,
                    'subcategory_slug' => $product->subcategory?->slug,
                    'subcategory_name' => $product->subcategory?->name,
                    'karat' => $product->karat,
                    'weight' => $product->weight,
                    'labor_cost' => $product->labor_cost,
                    'is_active' => $product->is_active,
                    'image_url' => $product->image_url,
                    'updated_at' => $product->updated_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);

    } catch (\Exception $e) {
        \Log::error('API: Error loading products: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to load products',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
