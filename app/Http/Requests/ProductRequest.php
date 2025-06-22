<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MetalCategory;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->ignore($productId)
            ],
            'description' => 'nullable|string|max:2000',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            'metal_category_id' => 'required|exists:metal_categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'karat' => 'required|string',
            'weight' => 'required|numeric|min:0.001|max:9999.999',
            'min_weight' => 'required|numeric|min:0.001|max:9999.999',
            'max_weight' => 'required|numeric|min:0.001|max:9999.999',
            'weight_step' => 'required|numeric|min:0.001|max:10',
            'labor_cost' => 'nullable|numeric|min:0|max:999.99',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'image' => $this->isMethod('POST') ? 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072' : 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'gallery' => 'nullable|array|max:5',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'slug.unique' => 'A product with this slug already exists.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'sku.unique' => 'A product with this SKU already exists.',
            'sku.regex' => 'The SKU may only contain uppercase letters, numbers, and hyphens.',
            'metal_category_id.required' => 'Please select a metal category.',
            'metal_category_id.exists' => 'The selected metal category does not exist.',
            'subcategory_id.required' => 'Please select a subcategory.',
            'subcategory_id.exists' => 'The selected subcategory does not exist.',
            'karat.required' => 'Please select a karat/purity level.',
            'weight.required' => 'The base weight is required.',
            'weight.numeric' => 'The weight must be a valid number.',
            'weight.min' => 'The weight must be at least 0.001 grams.',
            'weight.max' => 'The weight may not be greater than 9999.999 grams.',
            'min_weight.required' => 'The minimum weight is required.',
            'max_weight.required' => 'The maximum weight is required.',
            'weight_step.required' => 'The weight step is required.',
            'stock_quantity.required' => 'The stock quantity is required.',
            'stock_quantity.integer' => 'The stock quantity must be an integer.',
            'stock_quantity.min' => 'The stock quantity must be at least 0.',
            'min_stock_level.required' => 'The minimum stock level is required.',
            'image.required' => 'A product image is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The image may not be greater than 3MB.',
            'gallery.array' => 'The gallery must be an array of images.',
            'gallery.max' => 'You may upload a maximum of 5 gallery images.',
            'gallery.*.image' => 'All gallery files must be images.',
            'gallery.*.mimes' => 'Gallery images must be of type: jpeg, png, jpg, gif, webp.',
            'gallery.*.max' => 'Gallery images may not be greater than 3MB each.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate weight relationships
            $minWeight = (float) $this->min_weight;
            $maxWeight = (float) $this->max_weight;
            $baseWeight = (float) $this->weight;

            if ($minWeight >= $maxWeight) {
                $validator->errors()->add('max_weight', 'The maximum weight must be greater than the minimum weight.');
            }

            if ($baseWeight < $minWeight || $baseWeight > $maxWeight) {
                $validator->errors()->add('weight', 'The base weight must be between the minimum and maximum weight.');
            }

            // Validate karat against metal category
            if ($this->filled('metal_category_id') && $this->filled('karat')) {
                $metalCategory = MetalCategory::find($this->metal_category_id);
                if ($metalCategory) {
                    $availableKarats = $metalCategory->getAvailableKarats();
                    if (!in_array($this->karat, $availableKarats)) {
                        $validator->errors()->add('karat', 'The selected karat is not available for this metal category.');
                    }
                }
            }

            // Validate subcategory is available for metal category
            if ($this->filled('metal_category_id') && $this->filled('subcategory_id')) {
                $metalCategory = MetalCategory::find($this->metal_category_id);
                if ($metalCategory) {
                    $availableSubcategories = $metalCategory->activeSubcategories()->pluck('subcategories.id')->toArray();
                    if (!in_array($this->subcategory_id, $availableSubcategories)) {
                        $validator->errors()->add('subcategory_id', 'The selected subcategory is not available for this metal category.');
                    }
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to booleans
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);

        // Generate slug if not provided
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }

        // Generate SKU if not provided and this is a store request
        if (!$this->filled('sku') && $this->isMethod('POST') && $this->filled('name')) {
            $this->merge([
                'sku' => 'PRD-' . strtoupper(\Illuminate\Support\Str::random(8))
            ]);
        }

        // Normalize numeric values
        if ($this->filled('weight')) {
            $this->merge(['weight' => round((float) $this->weight, 3)]);
        }
        if ($this->filled('min_weight')) {
            $this->merge(['min_weight' => round((float) $this->min_weight, 3)]);
        }
        if ($this->filled('max_weight')) {
            $this->merge(['max_weight' => round((float) $this->max_weight, 3)]);
        }
        if ($this->filled('weight_step')) {
            $this->merge(['weight_step' => round((float) $this->weight_step, 3)]);
        }
    }

    /**
     * Get validated data with proper type casting.
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();

        // Ensure numeric fields are properly cast
        $numericFields = ['weight', 'min_weight', 'max_weight', 'weight_step', 'labor_cost', 'profit_margin'];
        foreach ($numericFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (float) $data[$field];
            }
        }

        $integerFields = ['stock_quantity', 'min_stock_level', 'sort_order'];
        foreach ($integerFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (int) $data[$field];
            }
        }

        return $data;
    }

    /**
     * Get available karats for the selected metal category.
     */
    public function getAvailableKarats(): array
    {
        if (!$this->filled('metal_category_id')) {
            return [];
        }

        $metalCategory = MetalCategory::find($this->metal_category_id);
        return $metalCategory ? $metalCategory->getAvailableKarats() : [];
    }
}
