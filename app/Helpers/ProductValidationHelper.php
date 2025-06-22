<?php
// File: app/Helpers/ProductValidationHelper.php

namespace App\Helpers;

use App\Models\MetalCategory;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductValidationHelper
{
    /**
     * Get validation rules for product creation/update
     *
     * @param int|null $productId For update validation (exclude current product from unique checks)
     * @return array
     */
    public static function getValidationRules($productId = null): array
    {
        return [
            // Required fields
            'name' => 'required|string|max:255',
            'metal_category_id' => 'required|exists:metal_categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'image' => $productId ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072' : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            
            // Optional but validated fields
            'sku' => 'nullable|string|unique:products,sku' . ($productId ? ',' . $productId : ''),
            'slug' => 'nullable|string|unique:products,slug' . ($productId ? ',' . $productId : ''),
            'description' => 'nullable|string|max:2000',
            
            // Optional metal/weight specifications
            'karat' => 'nullable|string|max:10',
            'weight' => 'nullable|numeric|min:0.001|max:9999.999',
            'min_weight' => 'nullable|numeric|min:0.001|max:9999.999',
            'max_weight' => 'nullable|numeric|min:0.001|max:9999.999',
            'weight_step' => 'nullable|numeric|min:0.001|max:10',
            
            // Optional pricing overrides
            'labor_cost' => 'nullable|numeric|min:0|max:999.99',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
            
            // Optional stock management
            'stock_quantity' => 'nullable|integer|min:0|max:999999',
            'min_stock_level' => 'nullable|integer|min:0|max:999999',
            
            // Optional metadata
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            
            // Boolean flags
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ];
    }

    /**
     * Perform custom validation logic for related fields
     *
     * @param Request $request
     * @throws ValidationException
     */
    public static function validateRelatedFields(Request $request): void
    {
        $errors = [];

        // Validate karat against metal category
        if ($request->karat && $request->metal_category_id) {
            $metalCategory = MetalCategory::find($request->metal_category_id);
            if ($metalCategory) {
                $availableKarats = $metalCategory->getAvailableKarats();
                if (!in_array($request->karat, $availableKarats)) {
                    $errors['karat'] = "Selected karat is not valid for {$metalCategory->name}.";
                }
            }
        }

        // Validate weight relationships
        if ($request->min_weight && $request->max_weight) {
            if ($request->min_weight >= $request->max_weight) {
                $errors['max_weight'] = 'Maximum weight must be greater than minimum weight.';
            }
        }

        // Validate base weight is within min/max range if all are provided
        if ($request->weight && $request->min_weight && $request->max_weight) {
            if ($request->weight < $request->min_weight || $request->weight > $request->max_weight) {
                $errors['weight'] = 'Base weight must be between minimum and maximum weight.';
            }
        }

        // Validate weight step makes sense
        if ($request->weight_step && $request->min_weight && $request->max_weight) {
            $range = $request->max_weight - $request->min_weight;
            if ($request->weight_step > $range) {
                $errors['weight_step'] = 'Weight step cannot be larger than the weight range.';
            }
        }

        // Validate stock levels relationship
        if ($request->stock_quantity !== null && $request->min_stock_level !== null) {
            if ($request->min_stock_level > $request->stock_quantity) {
                $errors['min_stock_level'] = 'Minimum stock level cannot be higher than current stock quantity.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate subcategory compatibility with metal category
     *
     * @param int $metalCategoryId
     * @param int $subcategoryId
     * @return bool
     */
    public static function validateSubcategoryCompatibility($metalCategoryId, $subcategoryId): bool
    {
        $metalCategory = MetalCategory::find($metalCategoryId);
        $subcategory = Subcategory::find($subcategoryId);

        if (!$metalCategory || !$subcategory) {
            return false;
        }

        // Check if subcategory is compatible with this metal category
        return $metalCategory->activeSubcategories()->where('subcategories.id', $subcategoryId)->exists();
    }

    /**
     * Get default values for optional fields based on metal category and subcategory
     *
     * @param int|null $metalCategoryId
     * @param int|null $subcategoryId
     * @return array
     */
    public static function getDefaultValues($metalCategoryId = null, $subcategoryId = null): array
    {
        $defaults = [
            'weight' => null,
            'min_weight' => null,
            'max_weight' => null,
            'weight_step' => 0.100,
            'karat' => null,
            'labor_cost' => null,
            'profit_margin' => null,
            'stock_quantity' => null,
            'min_stock_level' => null,
            'sort_order' => 0,
        ];

        // Set metal-specific defaults
        if ($metalCategoryId) {
            $metalCategory = MetalCategory::find($metalCategoryId);
            if ($metalCategory) {
                $availableKarats = $metalCategory->getAvailableKarats();
                // Set default karat to first available (most common)
                $defaults['karat'] = $availableKarats[0] ?? null;
            }
        }

        // Set subcategory-specific defaults
        if ($subcategoryId) {
            $subcategory = Subcategory::find($subcategoryId);
            if ($subcategory) {
                $defaults['labor_cost'] = $subcategory->default_labor_cost;
                $defaults['profit_margin'] = $subcategory->default_profit_margin;
                
                // Set weight defaults based on subcategory type
                switch (strtolower($subcategory->slug)) {
                    case 'rings':
                    case 'wedding-rings':
                        $defaults['weight'] = 5.000;
                        $defaults['min_weight'] = 3.000;
                        $defaults['max_weight'] = 12.000;
                        break;
                    case 'necklaces':
                    case 'chains':
                        $defaults['weight'] = 15.000;
                        $defaults['min_weight'] = 8.000;
                        $defaults['max_weight'] = 50.000;
                        break;
                    case 'earrings':
                        $defaults['weight'] = 3.000;
                        $defaults['min_weight'] = 1.500;
                        $defaults['max_weight'] = 8.000;
                        break;
                    case 'bracelets':
                        $defaults['weight'] = 12.000;
                        $defaults['min_weight'] = 6.000;
                        $defaults['max_weight'] = 30.000;
                        break;
                    case 'bullion':
                    case 'bars':
                        $defaults['weight'] = 31.103; // 1 oz
                        $defaults['min_weight'] = 31.103;
                        $defaults['max_weight'] = 31.103;
                        $defaults['stock_quantity'] = 0;
                        $defaults['min_stock_level'] = 1;
                        break;
                    case 'services':
                        // Services typically don't have weight or stock
                        $defaults['weight'] = null;
                        $defaults['min_weight'] = null;
                        $defaults['max_weight'] = null;
                        $defaults['stock_quantity'] = null;
                        $defaults['min_stock_level'] = null;
                        break;
                }
            }
        }

        return $defaults;
    }

    /**
     * Prepare data for storage (handle null values and generate missing fields)
     *
     * @param array $data
     * @return array
     */
    public static function prepareDataForStorage(array $data): array
    {
        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = 'PRD-' . strtoupper(\Str::random(8));
        }

        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        // Handle boolean values
        $data['is_active'] = isset($data['is_active']) && $data['is_active'];
        $data['is_featured'] = isset($data['is_featured']) && $data['is_featured'];

        // Convert empty strings to null for nullable numeric fields
        $nullableFields = [
            'weight', 'min_weight', 'max_weight', 'weight_step',
            'labor_cost', 'profit_margin', 'stock_quantity', 'min_stock_level'
        ];

        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Convert empty string to null for karat
        if (isset($data['karat']) && $data['karat'] === '') {
            $data['karat'] = null;
        }

        return $data;
    }

    /**
     * Get validation messages for better user experience
     *
     * @return array
     */
    public static function getValidationMessages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'metal_category_id.required' => 'Please select a metal category.',
            'subcategory_id.required' => 'Please select a subcategory.',
            'image.required' => 'Product image is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'Image size cannot exceed 3MB.',
            'sku.unique' => 'This SKU is already in use.',
            'slug.unique' => 'This URL slug is already in use.',
            'weight.min' => 'Weight must be at least 0.001 grams.',
            'weight.max' => 'Weight cannot exceed 9999.999 grams.',
            'min_weight.min' => 'Minimum weight must be at least 0.001 grams.',
            'max_weight.min' => 'Maximum weight must be at least 0.001 grams.',
            'weight_step.min' => 'Weight step must be at least 0.001 grams.',
            'weight_step.max' => 'Weight step cannot exceed 10 grams.',
            'labor_cost.min' => 'Labor cost cannot be negative.',
            'profit_margin.min' => 'Profit margin cannot be negative.',
            'profit_margin.max' => 'Profit margin cannot exceed 100%.',
            'stock_quantity.min' => 'Stock quantity cannot be negative.',
            'min_stock_level.min' => 'Minimum stock level cannot be negative.',
            'meta_title.max' => 'Meta title cannot exceed 60 characters.',
            'meta_description.max' => 'Meta description cannot exceed 160 characters.',
        ];
    }

    /**
     * Check if a product configuration is valid for pricing
     *
     * @param array $data Product data
     * @return array [isValid, missingFields, warnings]
     */
    public static function validatePricingConfiguration(array $data): array
    {
        $missingFields = [];
        $warnings = [];
        
        // Check required fields for pricing
        if (empty($data['metal_category_id'])) {
            $missingFields[] = 'metal_category_id';
        }
        
        if (empty($data['subcategory_id'])) {
            $missingFields[] = 'subcategory_id';
        }
        
        // Check optional but recommended fields
        if (empty($data['karat'])) {
            $warnings[] = 'No karat specified - will use default for metal type';
        }
        
        if (empty($data['weight'])) {
            $warnings[] = 'No base weight specified - will use 1.0g for calculations';
        }
        
        if (empty($data['labor_cost'])) {
            $warnings[] = 'No labor cost override - will use subcategory default';
        }
        
        if (empty($data['profit_margin'])) {
            $warnings[] = 'No profit margin override - will use subcategory default';
        }
        
        return [
            'isValid' => empty($missingFields),
            'missingFields' => $missingFields,
            'warnings' => $warnings
        ];
    }

    /**
     * Get product type recommendations based on form data
     *
     * @param array $data
     * @return array
     */
    public static function getProductTypeRecommendations(array $data): array
    {
        $recommendations = [];
        
        // Detect if this looks like a service
        if (isset($data['subcategory_id'])) {
            $subcategory = Subcategory::find($data['subcategory_id']);
            if ($subcategory && str_contains(strtolower($subcategory->name), 'service')) {
                $recommendations[] = [
                    'type' => 'service',
                    'message' => 'This appears to be a service item. Consider leaving weight and stock fields empty.',
                    'suggestions' => [
                        'Set labor cost as fixed service fee',
                        'Leave weight fields empty',
                        'Disable stock tracking'
                    ]
                ];
            }
        }
        
        // Detect if this looks like made-to-order
        if (empty($data['weight']) && empty($data['stock_quantity'])) {
            $recommendations[] = [
                'type' => 'custom',
                'message' => 'This configuration suggests a custom/made-to-order item.',
                'suggestions' => [
                    'Consider setting weight ranges if customization is limited',
                    'Leave stock tracking disabled for unlimited availability',
                    'Set clear description about customization options'
                ]
            ];
        }
        
        // Detect if this looks like bullion/bars
        if (isset($data['weight']) && $data['weight'] > 30 && $data['weight'] < 35) {
            $recommendations[] = [
                'type' => 'bullion',
                'message' => 'This weight suggests a 1oz bullion item.',
                'suggestions' => [
                    'Set fixed weight (no range) for standard bullion',
                    'Enable stock tracking for inventory management',
                    'Consider setting minimum stock level for reordering'
                ]
            ];
        }
        
        return $recommendations;
    }

    /**
     * Validate and suggest optimal field combinations
     *
     * @param array $data
     * @return array
     */
    public static function suggestOptimalConfiguration(array $data): array
    {
        $suggestions = [];
        
        // Weight configuration suggestions
        if (!empty($data['weight'])) {
            if (empty($data['min_weight']) || empty($data['max_weight'])) {
                $baseWeight = (float)$data['weight'];
                $suggestions['weight_range'] = [
                    'message' => 'Consider setting weight customization range',
                    'suggested_min' => round($baseWeight * 0.7, 3),
                    'suggested_max' => round($baseWeight * 1.5, 3),
                    'suggested_step' => round($baseWeight * 0.1, 3)
                ];
            }
        }
        
        // Stock management suggestions
        if (isset($data['stock_quantity']) && $data['stock_quantity'] > 0) {
            if (empty($data['min_stock_level'])) {
                $suggestions['stock_level'] = [
                    'message' => 'Consider setting minimum stock level for alerts',
                    'suggested_min' => max(1, floor($data['stock_quantity'] * 0.2))
                ];
            }
        }
        
        // Karat suggestions based on metal
        if (!empty($data['metal_category_id']) && empty($data['karat'])) {
            $metalCategory = MetalCategory::find($data['metal_category_id']);
            if ($metalCategory) {
                $availableKarats = $metalCategory->getAvailableKarats();
                $suggestions['karat'] = [
                    'message' => 'Consider specifying karat for accurate pricing',
                    'most_common' => $availableKarats[0] ?? null,
                    'available_options' => $availableKarats
                ];
            }
        }
        
        return $suggestions;
    }

    /**
     * Generate validation summary for admin interface
     *
     * @param array $data
     * @return array
     */
    public static function generateValidationSummary(array $data): array
    {
        $summary = [
            'configuration_type' => 'standard',
            'completeness_score' => 0,
            'missing_recommended' => [],
            'warnings' => [],
            'suggestions' => []
        ];
        
        $totalFields = 8; // Total recommended fields
        $filledFields = 0;
        
        // Required fields
        $requiredFields = ['name', 'metal_category_id', 'subcategory_id'];
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $filledFields++;
            }
        }
        
        // Optional but recommended fields
        $recommendedFields = ['karat', 'weight', 'labor_cost', 'profit_margin', 'stock_quantity'];
        foreach ($recommendedFields as $field) {
            if (!empty($data[$field])) {
                $filledFields++;
            } else {
                $summary['missing_recommended'][] = $field;
            }
        }
        
        $summary['completeness_score'] = round(($filledFields / $totalFields) * 100);
        
        // Determine configuration type
        if (empty($data['weight']) && empty($data['stock_quantity'])) {
            $summary['configuration_type'] = 'service_or_custom';
        } elseif (!empty($data['weight']) && !empty($data['min_weight']) && !empty($data['max_weight'])) {
            $summary['configuration_type'] = 'customizable';
        } elseif (!empty($data['stock_quantity'])) {
            $summary['configuration_type'] = 'inventory_tracked';
        }
        
        // Add contextual warnings
        if ($summary['completeness_score'] < 50) {
            $summary['warnings'][] = 'Low configuration completeness may affect pricing accuracy';
        }
        
        if (empty($data['karat'])) {
            $summary['warnings'][] = 'No karat specified - pricing will use metal defaults';
        }
        
        return $summary;
    }
}