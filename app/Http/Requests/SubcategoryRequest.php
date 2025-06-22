<?php
// app/Http/Requests/SubcategoryRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubcategoryRequest extends FormRequest
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
        $subcategoryId = $this->route('subcategory')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories', 'name')->ignore($subcategoryId)
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('subcategories', 'slug')->ignore($subcategoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'default_labor_cost' => 'required|numeric|min:0|max:999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
            'metal_categories' => 'nullable|array',
            'metal_categories.*' => 'exists:metal_categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'remove_image' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The subcategory name is required.',
            'name.unique' => 'A subcategory with this name already exists.',
            'slug.unique' => 'A subcategory with this slug already exists.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'default_labor_cost.required' => 'The default labor cost is required.',
            'default_labor_cost.numeric' => 'The labor cost must be a valid number.',
            'default_labor_cost.min' => 'The labor cost must be at least 0.',
            'default_labor_cost.max' => 'The labor cost may not be greater than 999.99.',
           
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The image may not be greater than 2MB.',
            'metal_categories.array' => 'The metal categories must be an array.',
            'metal_categories.*.exists' => 'One or more selected metal categories do not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation logic can be added here
            if ($this->filled('metal_categories') && count($this->metal_categories) === 0) {
                $validator->errors()->add('metal_categories', 'Please select at least one metal category.');
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
            'remove_image' => $this->boolean('remove_image'),
        ]);

        // Generate slug if not provided
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }
    }

    /**
     * Get validated data with type casting.
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();

        // Ensure numeric fields are properly cast
        if (isset($data['default_labor_cost'])) {
            $data['default_labor_cost'] = (float) $data['default_labor_cost'];
        }

       

        if (isset($data['sort_order'])) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        return $data;
    }
}
