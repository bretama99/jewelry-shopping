<?php
// app/Http/Controllers/Admin/MetalCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetalCategory;
use App\Models\Subcategory; // ADD THIS IMPORT
use App\Services\MetalPriceApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MetalCategoryController extends Controller
{
    protected $metalPriceService;

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->metalPriceService = $metalPriceService;
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

            // Get current metal prices from API
            $currentPrices = $this->metalPriceService->getAllMetalPricesAUD();

            return view('admin.metal-categories.index', compact('metalCategories', 'stats', 'filters', 'currentPrices'));

        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Error loading metal categories: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new metal category
     * FIXED: Added subcategories to the view
     */
    public function create()
    {
        // Get all active subcategories for the jewelry types selection
        $subcategories = Subcategory::active()->ordered()->get();
        
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
            'color' => 'nullable|string|max:7', // Add color validation
            'is_active' => 'nullable|boolean',
            'is_precious' => 'nullable|boolean', 
            'auto_update_prices' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
            'subcategories' => 'nullable|array', // Add subcategories validation
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

            // Create default purity ratios based on symbol
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

            // Try to fetch initial price
            try {
                $this->updateMetalPrice($metalCategory);
            } catch (\Exception $e) {
                // Price fetch failed, but category was created successfully
                \Log::warning("Failed to fetch initial price for {$metalCategory->symbol}: " . $e->getMessage());
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
        $subcategories = Subcategory::active()->ordered()->get();
        
        return view('admin.metal-categories.edit', compact('metalCategory', 'subcategories'));
    }

    /**
     * Get live price for specific metal symbol (AJAX endpoint)
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

            // Fetch USD price first
            $usdResponse = file_get_contents("https://api.metalpriceapi.com/v1/latest?api_key=d68f51781cca05150ab380fbea59224c&base=USD&currencies={$symbol}");
            $usdData = json_decode($usdResponse, true);
            
            if (!$usdData['success']) {
                throw new \Exception('USD API call failed');
            }

            // Get AUD exchange rate
            $audResponse = file_get_contents('https://api.exchangerate-api.com/v4/latest/USD');
            $audData = json_decode($audResponse, true);
            $audRate = $audData['rates']['AUD'] ?? 1.45;
            
            $gramsPerTroyOz = 31.1035;
            
            // CORRECT CALCULATION: 1/fraction = USD per ounce
            $usdFraction = $usdData['rates'][$symbol];
            $usdPricePerOz = 1 / $usdFraction;
            $audPricePerOz = $usdPricePerOz * $audRate;
            $audPricePerGram = $audPricePerOz / $gramsPerTroyOz;
            
            return response()->json([
                'success' => true,
                'price_usd' => 'AUD $' . number_format($audPricePerOz, 2),
                'price_per_gram_aud' => 'AUD $' . number_format($audPricePerGram, 2),
                'exchange_rate' => number_format($audRate, 4) . ' AUD',
                'last_updated' => now()->format('Y-m-d H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error fetching live price for {$symbol}: " . $e->getMessage());
            
            // Return fallback prices
            $fallbackPrices = [
                'XAU' => ['ounce' => 3825.00, 'gram' => 154.50],
                'XAG' => ['ounce' => 45.20, 'gram' => 1.45],
                'XPT' => ['ounce' => 1520.00, 'gram' => 48.80],
                'XPD' => ['ounce' => 1735.00, 'gram' => 55.77]
            ];
            
            $fallback = $fallbackPrices[$symbol] ?? ['ounce' => 0, 'gram' => 0];
            
            return response()->json([
                'success' => true,
                'price_usd' => 'AUD $' . number_format($fallback['ounce'], 2),
                'price_per_gram_aud' => 'AUD $' . number_format($fallback['gram'], 2),
                'exchange_rate' => '1.45 AUD (Fallback)',
                'last_updated' => now()->format('Y-m-d H:i:s') . ' (Fallback)',
            ]);
        }
    }

    /**
     * Get default purity ratios based on metal symbol
     */
    private function getDefaultPurityRatios($symbol)
    {
        $defaults = [
            'XAU' => [ // Gold
                '24' => 1.0,    // 24K = 100%
                '22' => 0.917,  // 22K = 91.7%
                '21' => 0.875,  // 21K = 87.5%
                '18' => 0.75,   // 18K = 75%
                '14' => 0.583,  // 14K = 58.3%
                '10' => 0.417   // 10K = 41.7%
            ],
            'XAG' => [ // Silver
                '999' => 0.999, // Fine silver
                '925' => 0.925, // Sterling silver
                '900' => 0.900, // Coin silver
                '800' => 0.800  // European silver
            ],
            'XPT' => [ // Platinum
                '999' => 0.999,
                '950' => 0.950,
                '900' => 0.900,
                '850' => 0.850
            ],
            'XPD' => [ // Palladium
                '999' => 0.999,
                '950' => 0.950,
                '500' => 0.500
            ]
        ];

        return json_encode($defaults[$symbol] ?? []);
    }

    /**
     * Update metal price from API
     */
    private function updateMetalPrice(MetalCategory $metalCategory)
    {
        try {
            $response = file_get_contents("https://api.metalpriceapi.com/v1/latest?api_key=d68f51781cca05150ab380fbea59224c&base=USD&currencies={$metalCategory->symbol}");
            $data = json_decode($response, true);
            
            if ($data['success'] && isset($data['rates'][$metalCategory->symbol])) {
                // Get AUD rate
                $audResponse = file_get_contents('https://api.exchangerate-api.com/v4/latest/USD');
                $audData = json_decode($audResponse, true);
                $audRate = $audData['rates']['AUD'] ?? 1.45;
                
                // Calculate USD price per ounce (correct calculation)
                $usdFraction = $data['rates'][$metalCategory->symbol];
                $usdPricePerOz = 1 / $usdFraction;
                
                // Update the metal category
                $metalCategory->update([
                    'current_price_usd' => $usdPricePerOz,
                    'aud_rate' => $audRate,
                    'price_updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to update price for {$metalCategory->symbol}: " . $e->getMessage());
            throw $e;
        }
    }

    // ... (rest of your existing methods remain the same)

    /**
     * Display the specified metal category
     */
    public function show(MetalCategory $metalCategory)
    {
        $metalCategory->load(['subcategories', 'products' => function ($query) {
            $query->active()->orderBy('name')->take(10);
        }]);

        // Get current prices for all karats/purities
        $currentPrices = $metalCategory->getAllPrices();

        // Get price history (if you have a price history table)
        $priceHistory = []; // Implement price history if needed

        return view('admin.metal-categories.show', compact('metalCategory', 'currentPrices', 'priceHistory'));
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
                    'labor_cost_override' => $subcategory->pivot->labor_cost_override,
                    'profit_margin_override' => $subcategory->pivot->profit_margin_override,
                    'is_available' => $subcategory->pivot->is_available,
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
     * Update price for specific metal category
     */
    public function updatePrice(Request $request, MetalCategory $metalCategory)
    {
        $request->validate([
            'price_usd' => 'required|numeric|min:0',
            'aud_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            $metalCategory->update([
                'current_price_usd' => $request->price_usd,
                'aud_rate' => $request->aud_rate ?? 1.45,
                'price_updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Price updated successfully.',
                'new_prices' => $metalCategory->getAllPrices()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update all metal prices from API
     */
    public function updateAllPrices()
    {
        try {
            $updateCount = 0;
            $metalCategories = MetalCategory::where('auto_update_prices', true)->get();
            
            foreach ($metalCategories as $metalCategory) {
                try {
                    $this->updateMetalPrice($metalCategory);
                    $updateCount++;
                } catch (\Exception $e) {
                    \Log::error("Failed to update price for {$metalCategory->symbol}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Updated prices for {$updateCount} metal categories."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating prices: ' . $e->getMessage()
            ], 500);
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
                        $this->updateMetalPrice($metalCategory);
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
                    'Current Price USD', 'AUD Rate', 'Products Count', 'Subcategories Count',
                    'Sort Order', 'Created At', 'Updated At'
                ]);

                // Add data rows
                foreach ($metalCategories as $metalCategory) {
                    fputcsv($handle, [
                        $metalCategory->id,
                        $metalCategory->name,
                        $metalCategory->symbol,
                        $metalCategory->slug,
                        $metalCategory->description,
                        $metalCategory->is_active ? 'Active' : 'Inactive',
                        $metalCategory->current_price_usd,
                        $metalCategory->aud_rate,
                        $metalCategory->products->count(),
                        $metalCategory->subcategories->count(),
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
}