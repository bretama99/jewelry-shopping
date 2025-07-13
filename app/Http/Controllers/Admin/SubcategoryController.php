<?php
// app/Http/Controllers/Admin/SubcategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\MetalCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubcategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of subcategories
     */
    public function index(Request $request)
    {
        try {
            $query = Subcategory::withCount(['products', 'activeProducts']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('slug', 'like', '%' . $search . '%');
                });
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

            // Apply sorting
            switch ($request->get('sort', 'sort_order')) {
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'created_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'products_count':
                    $query->orderByDesc('products_count');
                    break;
                case 'featured':
                    $query->orderBy('is_featured', 'desc')->orderBy('name', 'asc');
                    break;
                default:
                    $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
            }

            $subcategories = $query->paginate(15)->appends($request->query());

            // Calculate stats
            $stats = [
                'total_subcategories' => Subcategory::count(),
                'active_subcategories' => Subcategory::where('is_active', true)->count(),
                'featured_subcategories' => Subcategory::where('is_featured', true)->count(),
                'total_products' => Subcategory::withCount('products')->get()->sum('products_count'),
            ];

            $filters = $request->only(['search', 'status', 'sort']);

            return view('admin.subcategories.index', compact('subcategories', 'stats', 'filters'));

        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Error loading subcategories: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new subcategory
     */
    public function create()
    {
        $metalCategories = MetalCategory::active()->ordered()->get();
        return view('admin.subcategories.create', compact('metalCategories'));
    }

    /**
     * Store a newly created subcategory
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subcategories,name',
            'slug' => 'nullable|string|unique:subcategories,slug',
            'description' => 'nullable|string|max:1000',
            'default_labor_cost' => 'required|numeric|min:0|max:999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
            'metal_categories' => 'nullable|array',
            'metal_categories.*' => 'exists:metal_categories,id',
        ]);

        try {
            $data = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $this->handleImageUpload($request->file('image'), $data['name']);
            }

            // Handle checkboxes
            $data['is_active'] = $request->has('is_active');
            $data['is_featured'] = $request->has('is_featured');

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $subcategory = Subcategory::create($data);

            // Attach metal categories with default settings
            if ($request->has('metal_categories')) {
                foreach ($request->metal_categories as $metalCategoryId) {
                    $subcategory->metalCategories()->attach($metalCategoryId, [
                        'is_available' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return redirect()->route('admin.subcategories.index')
                           ->with('success', 'Subcategory created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error creating subcategory: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified subcategory
     */
    public function show(Subcategory $subcategory)
    {
        $subcategory->load(['metalCategories', 'products' => function ($query) {
            $query->active()->orderBy('name')->take(10);
        }]);

        return view('admin.subcategories.show', compact('subcategory'));
    }

    /**
     * Show the form for editing the specified subcategory
     */
    public function edit(Subcategory $subcategory)
    {
        $metalCategories = MetalCategory::active()->ordered()->get();
        $subcategory->load('metalCategories');

        return view('admin.subcategories.edit', compact('subcategory', 'metalCategories'));
    }

    /**
     * Update the specified subcategory
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subcategories')->ignore($subcategory->id)],
            'slug' => ['nullable', 'string', Rule::unique('subcategories')->ignore($subcategory->id)],
            'description' => 'nullable|string|max:1000',
            'default_labor_cost' => 'required|numeric|min:0|max:999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
            'metal_categories' => 'nullable|array',
            'metal_categories.*' => 'exists:metal_categories,id',
        ]);

        try {
            $data = $request->all();

            // Handle image removal
            if ($request->has('remove_image') && $request->remove_image) {
                if ($subcategory->image) {
                    $this->deleteImage($subcategory->image);
                    $data['image'] = null;
                }
            }

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($subcategory->image) {
                    $this->deleteImage($subcategory->image);
                }

                $data['image'] = $this->handleImageUpload($request->file('image'), $data['name']);
            }

            // Handle checkboxes
            $data['is_active'] = $request->has('is_active');
            $data['is_featured'] = $request->has('is_featured');

            $subcategory->update($data);

            // Sync metal categories
            if ($request->has('metal_categories')) {
                $syncData = [];
                foreach ($request->metal_categories as $metalCategoryId) {
                    $syncData[$metalCategoryId] = [
                        'is_available' => true,
                        'updated_at' => now(),
                    ];
                }
                $subcategory->metalCategories()->sync($syncData);
            } else {
                $subcategory->metalCategories()->detach();
            }

            return redirect()->route('admin.subcategories.index')
                           ->with('success', 'Subcategory updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error updating subcategory: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subcategory
     */
    public function destroy(Subcategory $subcategory)
    {
        try {
            // Check if subcategory can be deleted
            if (!$subcategory->canBeDeleted()) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete subcategory with existing products.'
                    ], 400);
                }

                return back()->with('error', 'Cannot delete subcategory with existing products.');
            }

            // Delete associated image before deleting subcategory
            if ($subcategory->image) {
                $this->deleteImage($subcategory->image);
            }

            // Detach all metal categories
            $subcategory->metalCategories()->detach();

            $subcategory->delete();

            if (request()->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.subcategories.index')
                           ->with('success', 'Subcategory deleted successfully.');

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle subcategory status
     */
    public function toggleStatus(Subcategory $subcategory)
    {
        try {
            $subcategory->update(['is_active' => !$subcategory->is_active]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_active' => $subcategory->is_active,
                    'message' => 'Status updated successfully.'
                ]);
            }

            return back()->with('success', 'Subcategory status updated successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', 'Error updating status.');
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeature(Subcategory $subcategory)
    {
        try {
            $subcategory->update(['is_featured' => !$subcategory->is_featured]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_featured' => $subcategory->is_featured,
                    'message' => 'Featured status updated successfully.'
                ]);
            }

            return back()->with('success', 'Featured status updated successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', 'Error updating featured status.');
        }
    }

    /**
     * Duplicate subcategory
     */
    public function duplicate(Subcategory $subcategory)
    {
        try {
            // Handle image duplication
            $duplicatedImage = null;
            if ($subcategory->image) {
                $duplicatedImage = $this->duplicateImage($subcategory->image);
            }

            $newSubcategory = $subcategory->replicate();
            $newSubcategory->name = $subcategory->name . ' (Copy)';
            $newSubcategory->slug = null; // Will be auto-generated
            $newSubcategory->is_active = false;
            $newSubcategory->image = $duplicatedImage;
            $newSubcategory->save();

            // Copy metal category relationships
            $metalCategoryData = [];
            foreach ($subcategory->metalCategories as $metalCategory) {
                $metalCategoryData[$metalCategory->id] = [
                    'labor_cost_override' => $metalCategory->pivot->labor_cost_override,
                    'profit_margin_override' => $metalCategory->pivot->profit_margin_override,
                    'is_available' => $metalCategory->pivot->is_available,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $newSubcategory->metalCategories()->sync($metalCategoryData);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subcategory duplicated successfully.',
                    'redirect' => route('admin.subcategories.edit', $newSubcategory)
                ]);
            }

            return redirect()->route('admin.subcategories.edit', $newSubcategory)
                           ->with('success', 'Subcategory duplicated successfully. Please review and update as needed.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return back()->with('error', 'Error duplicating subcategory.');
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'subcategory_ids' => 'required|array|min:1',
            'subcategory_ids.*' => 'exists:subcategories,id'
        ]);

        try {
            $subcategories = Subcategory::whereIn('id', $request->subcategory_ids);

            switch ($request->action) {
                case 'activate':
                    $subcategories->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $subcategories->update(['is_active' => false]);
                    break;
                case 'feature':
                    $subcategories->update(['is_featured' => true]);
                    break;
                case 'unfeature':
                    $subcategories->update(['is_featured' => false]);
                    break;
                case 'delete':
                    $subcategoriesToDelete = $subcategories->get();
                    foreach ($subcategoriesToDelete as $subcategory) {
                        if ($subcategory->canBeDeleted()) {
                            if ($subcategory->image) {
                                $this->deleteImage($subcategory->image);
                            }
                            $subcategory->metalCategories()->detach();
                            $subcategory->delete();
                        }
                    }
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid bulk action: {$request->action}");
            }

            $actionName = str_replace('_', ' ', $request->action);
            return response()->json([
                'success' => true,
                'message' => "Successfully {$actionName}d " . count($request->subcategory_ids) . " subcategory(s)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get subcategories for specific metal category (AJAX)
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
                'data' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subcategories'
            ], 500);
        }
    }

    /**
     * Update metal category relationship settings
     */
    public function updateMetalCategorySettings(Request $request, Subcategory $subcategory, MetalCategory $metalCategory)
    {
        $request->validate([
            'labor_cost_override' => 'nullable|numeric|min:0|max:999.99',
            'profit_margin_override' => 'nullable|numeric|min:0|max:100',
            'is_available' => 'boolean',
        ]);

        try {
            $subcategory->metalCategories()->updateExistingPivot($metalCategory->id, [
                'labor_cost_override' => $request->labor_cost_override,
                'profit_margin_override' => $request->profit_margin_override,
                'is_available' => $request->boolean('is_available', true),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle image upload to subcategories folder
     */
    private function handleImageUpload($file, $subcategoryName)
    {
        // Create subcategories directory if it doesn't exist
        $subcategoriesPath = public_path('images/subcategories');
        if (!File::exists($subcategoriesPath)) {
            File::makeDirectory($subcategoriesPath, 0755, true);
        }

        // Generate unique filename
        $filename = time() . '_' . Str::slug($subcategoryName) . '.' . $file->getClientOriginalExtension();

        // Move file to subcategories directory
        $file->move($subcategoriesPath, $filename);

        return $filename;
    }

    /**
     * Delete image from subcategories folder
     */
    private function deleteImage($filename)
    {
        if ($filename) {
            $imagePath = public_path('images/subcategories/' . $filename);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }
    }

    /**
     * Duplicate image file for subcategory duplication
     */
    private function duplicateImage($originalFilename)
    {
        $originalPath = public_path('images/subcategories/' . $originalFilename);

        if (!File::exists($originalPath)) {
            return null;
        }

        // Generate new filename
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $newFilename = time() . '_copy_' . Str::random(10) . '.' . $extension;
        $newPath = public_path('images/subcategories/' . $newFilename);

        // Copy the file
        if (File::copy($originalPath, $newPath)) {
            return $newFilename;
        }

        return null;
    }
}
