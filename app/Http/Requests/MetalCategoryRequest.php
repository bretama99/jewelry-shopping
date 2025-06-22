<?php


// app/Http/Requests/

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MetalCategoryRequest extends FormRequest
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
        $metalCategoryId = $this->route('metalCategory')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('metal_categories', 'name')->ignore($metalCategoryId)
            ],
            'symbol' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('metal_categories', 'symbol')->ignore($metalCategoryId)
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('metal_categories', 'slug')->ignore($metalCategoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'purity_ratios' => 'required|json',
            'current_price_usd' => 'nullable|numeric|min:0',
            'aud_rate' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'boolean',
            'remove_image' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The metal category name is required.',
            'name.unique' => 'A metal category with this name already exists.',
            'symbol.required' => 'The metal symbol is required.',
            'symbol.size' => 'The metal symbol must be exactly 3 characters.',
            'symbol.regex' => 'The metal symbol must contain only uppercase letters.',
            'symbol.unique' => 'A metal category with this symbol already exists.',
            'slug.unique' => 'A metal category with this slug already exists.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'purity_ratios.required' => 'Purity ratios are required.',
            'purity_ratios.json' => 'Purity ratios must be valid JSON.',
            'current_price_usd.numeric' => 'The price must be a valid number.',
            'current_price_usd.min' => 'The price must be at least 0.',
            'aud_rate.numeric' => 'The AUD rate must be a valid number.',
            'aud_rate.min' => 'The AUD rate must be at least 0.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The image may not be greater than 2MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate purity ratios JSON structure
            if ($this->filled('purity_ratios')) {
                try {
                    $purityRatios = json_decode($this->purity_ratios, true);

                    if (!is_array($purityRatios) || empty($purityRatios)) {
                        $validator->errors()->add('purity_ratios', 'Purity ratios must contain at least one karat/purity level.');
                        return;
                    }

                    foreach ($purityRatios as $karat => $ratio) {
                        if (!is_numeric($ratio) || $ratio <= 0 || $ratio > 1) {
                            $validator->errors()->add('purity_ratios', "Invalid purity ratio for {$karat}. Must be between 0 and 1.");
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add('purity_ratios', 'Invalid JSON format for purity ratios.');
                }
            }

            // Validate symbol format for known metals
            if ($this->filled('symbol')) {
                $validSymbols = ['XAU', 'XAG', 'XPD', 'XPT']; // Add more as needed
                $symbol = strtoupper($this->symbol);

                if (!in_array($symbol, $validSymbols) && $this->isMethod('POST')) {
                    $validator->errors()->add('symbol', 'Invalid metal symbol. Valid symbols are: ' . implode(', ', $validSymbols));
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
            'remove_image' => $this->boolean('remove_image'),
        ]);

        // Normalize symbol to uppercase
        if ($this->filled('symbol')) {
            $this->merge([
                'symbol' => strtoupper($this->symbol)
            ]);
        }

        // Generate slug if not provided
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }
    }

    /**
     * Get validated data with type casting and JSON parsing.
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();

        // Parse purity ratios JSON
        if (isset($data['purity_ratios'])) {
            $data['purity_ratios'] = json_decode($data['purity_ratios'], true);
        }

        // Ensure numeric fields are properly cast
        if (isset($data['current_price_usd'])) {
            $data['current_price_usd'] = (float) $data['current_price_usd'];
        }

        if (isset($data['aud_rate'])) {
            $data['aud_rate'] = (float) $data['aud_rate'];
        }

        if (isset($data['sort_order'])) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        return $data;
    }
}

// app/Http/Requests/ProductRequest.php
