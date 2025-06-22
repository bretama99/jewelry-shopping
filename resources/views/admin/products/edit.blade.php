{{-- File: resources/views/admin/products/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
            <p class="mb-0 text-muted">Update product: <strong>{{ $product->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info">
                <i class="fas fa-eye me-2"></i>View Product
            </a>
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>View on Website
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}"
          enctype="multipart/form-data" id="productForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Product Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $product->name) }}"
                                   placeholder="e.g., Classic Gold Wedding Ring" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Metal Category and Subcategory -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="metal_category_id" class="form-label">Metal Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('metal_category_id') is-invalid @enderror"
                                        id="metal_category_id" name="metal_category_id" required onchange="updateMetalCategory()">
                                    <option value="">Select Metal Category</option>
                                    @foreach($metalCategories as $metalCategory)
                                        <option value="{{ $metalCategory->id }}"
                                                data-symbol="{{ $metalCategory->symbol }}"
                                                {{ old('metal_category_id', $product->metal_category_id) == $metalCategory->id ? 'selected' : '' }}>
                                            {{ $metalCategory->name }} ({{ $metalCategory->symbol }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('metal_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subcategory_id" class="form-label">Subcategory <span class="text-danger">*</span></label>
                                <select class="form-select @error('subcategory_id') is-invalid @enderror"
                                        id="subcategory_id" name="subcategory_id" required onchange="updateSubcategory()">
                                    <option value="">Select Subcategory</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}"
                                                {{ old('subcategory_id', $product->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Optional Karat Selection -->
                        <!-- <div class="mb-3">
                            <label for="karat" class="form-label">Karat/Purity (Optional)</label>
                            <select class="form-select @error('karat') is-invalid @enderror"
                                    id="karat" name="karat" onchange="updateKaratInfo()">
                                <option value="">No karat specified</option>
                            </select>
                            <div class="form-text" id="karatInfo">
                                @if($product->karat)
                                    Current: {{ $product->karat_display }}
                                @else
                                    No karat currently set
                                @endif
                            </div>
                            @error('karat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> -->

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Product Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4"
                                      placeholder="Detailed description of the product...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Optional Weight Configuration -->
                <!-- <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Weight Configuration (Optional)</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Weight settings are optional. Leave blank if not applicable for this product type.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="weight" class="form-label">Base Weight (grams)</label>
                                <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                       id="weight" name="weight" value="{{ old('weight', $product->weight) }}"
                                       step="0.001" min="0.001" max="9999.999"
                                       placeholder="e.g., 5.250">
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="min_weight" class="form-label">Minimum Weight</label>
                                <input type="number" class="form-control @error('min_weight') is-invalid @enderror"
                                       id="min_weight" name="min_weight" value="{{ old('min_weight', $product->min_weight) }}"
                                       step="0.001" min="0.001" max="9999.999"
                                       placeholder="e.g., 2.000">
                                @error('min_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="max_weight" class="form-label">Maximum Weight</label>
                                <input type="number" class="form-control @error('max_weight') is-invalid @enderror"
                                       id="max_weight" name="max_weight" value="{{ old('max_weight', $product->max_weight) }}"
                                       step="0.001" min="0.001" max="9999.999"
                                       placeholder="e.g., 15.000">
                                @error('max_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="weight_step" class="form-label">Weight Step</label>
                                <input type="number" class="form-control @error('weight_step') is-invalid @enderror"
                                       id="weight_step" name="weight_step" value="{{ old('weight_step', $product->weight_step) }}"
                                       step="0.001" min="0.001" max="10"
                                       placeholder="0.100">
                                @error('weight_step')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Pricing Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pricing Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="labor_cost" class="form-label">Labor Cost Override (AUD per gram)</label>
                                <input type="number" class="form-control @error('labor_cost') is-invalid @enderror"
                                       id="labor_cost" name="labor_cost" value="{{ old('labor_cost', $product->labor_cost) }}"
                                       step="0.01" min="0" max="99999.99"
                                       onchange="calculateSamplePrice()">
                                <div class="form-text">Leave empty to use subcategory default (<span id="defaultLaborCost">{{ $product->subcategory?->default_labor_cost ?? '--' }}</span> AUD/g)</div>
                                @error('labor_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="profit_margin" class="form-label">Profit Margin Override (%)</label>
                                <input type="number" class="form-control @error('profit_margin') is-invalid @enderror"
                                       id="profit_margin" name="profit_margin" value="{{ old('profit_margin', $product->profit_margin) }}"
                                       step="0.01" min="0" max="100"
                                       onchange="calculateSamplePrice()">
                                <div class="form-text">Leave empty to use subcategory default (<span id="defaultProfitMargin">{{ $product->subcategory?->default_profit_margin ?? '--' }}</span>%)</div>
                                @error('profit_margin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                        </div>

                        <!-- Live Price Calculation -->
                        <!-- <div class="mt-4">
                            <h6>Current Price Calculation</h6>
                            <div id="priceCalculation" class="bg-light p-3 rounded">
                                <small class="text-muted">Loading current price calculation...</small>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Publication Status -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Publication Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select @error('is_active') is-invalid @enderror"
                                    id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $product->is_active) == 1 ? 'selected' : '' }}>
                                    Active (Visible)
                                </option>
                                <option value="0" {{ old('is_active', $product->is_active) == 0 ? 'selected' : '' }}>
                                    Inactive (Hidden)
                                </option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Featured Product
                            </label>
                        </div> -->

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}"
                                   min="0" max="9999">
                            <div class="form-text">Lower numbers appear first</div>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Media -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Product Images</h6>
                    </div>
                    <div class="card-body">
                        <!-- Current Images -->
                        @if($product->image_url)
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                             class="img-thumbnail" style="max-width: 200px;">
                                        <div class="form-check position-absolute top-0 end-0 m-1">
                                            <input type="checkbox" class="form-check-input"
                                                   name="remove_image" id="remove_image" value="1">
                                            <label class="form-check-label text-danger" for="remove_image">
                                                Remove
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Upload New Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label">{{ $product->image_url ? 'Replace Image' : 'Upload Image' }}</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 2MB</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <label class="form-label">New Image Preview</label>
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                </div>

                <!-- Optional Stock Configuration -->
                <!-- <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Stock Management (Optional)</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Stock tracking is optional. Leave blank to disable stock management for this product.</p>
                        
                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                   id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}"
                                   min="0" max="99999" placeholder="Leave blank to disable">
                            <div class="form-text">
                                @if($product->stock_quantity !== null)
                                    Current: {{ $product->stock_quantity }} 
                                    @if($product->isLowStock())
                                        <span class="text-warning">(Low Stock)</span>
                                    @endif
                                @else
                                    Stock tracking disabled
                                @endif
                            </div>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                            <input type="number" class="form-control @error('min_stock_level') is-invalid @enderror"
                                   id="min_stock_level" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level) }}"
                                   min="0" max="99999" placeholder="Low stock alert threshold">
                            @error('min_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div> -->

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Product
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                <i class="fas fa-file-alt me-2"></i>Save as Draft
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Created:</strong> {{ $product->created_at->format('M d, Y') }}<br>
                            <strong>Updated:</strong> {{ $product->updated_at->format('M d, Y') }}<br>
                            <strong>ID:</strong> {{ $product->id }}<br>
                            <strong>SKU:</strong> {{ $product->sku }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with current values
    if (document.getElementById('metal_category_id').value) {
        updateMetalCategory();
    }

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slugInput = document.getElementById('slug');
        if (slugInput && !slugInput.value) {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        }
    });

    // Load current price calculation
    calculateSamplePrice();
});

// Update metal category and available karats
function updateMetalCategory() {
    const metalSelect = document.getElementById('metal_category_id');
    const karatSelect = document.getElementById('karat');
    const selectedOption = metalSelect.options[metalSelect.selectedIndex];

    if (!selectedOption.value) {
        karatSelect.innerHTML = '<option value="">Select metal category first</option>';
        return;
    }

    const symbol = selectedOption.dataset.symbol;
    const currentKarat = '{{ old("karat", $product->karat) }}';

    // Update karat options based on metal
    let karatOptions = [];
    if (symbol === 'XAU') { // Gold
        karatOptions = [
            {value: '24', text: '24K (Pure Gold - 99.9%)'},
            {value: '22', text: '22K (91.7% Gold)'},
            {value: '21', text: '21K (87.5% Gold)'},
            {value: '18', text: '18K (75% Gold)'},
            {value: '14', text: '14K (58.3% Gold)'},
            {value: '10', text: '10K (41.7% Gold)'}
        ];
    } else if (symbol === 'XAG') { // Silver
        karatOptions = [
            {value: '999', text: '999 Silver (99.9% Pure)'},
            {value: '925', text: '925 Sterling Silver (92.5%)'},
            {value: '900', text: '900 Coin Silver (90%)'},
            {value: '800', text: '800 Silver (80%)'}
        ];
    } else if (symbol === 'XPT') { // Platinum
        karatOptions = [
            {value: '999', text: '999 Platinum (99.9% Pure)'},
            {value: '950', text: '950 Platinum (95%)'},
            {value: '900', text: '900 Platinum (90%)'},
            {value: '850', text: '850 Platinum (85%)'}
        ];
    } else if (symbol === 'XPD') { // Palladium
        karatOptions = [
            {value: '999', text: '999 Palladium (99.9% Pure)'},
            {value: '950', text: '950 Palladium (95%)'},
            {value: '500', text: '500 Palladium (50%)'}
        ];
    }

    karatSelect.innerHTML = '<option value="">No karat specified</option>';
    karatOptions.forEach(option => {
        const optionElement = new Option(option.text, option.value);
        if (option.value === currentKarat) {
            optionElement.selected = true;
        }
        karatSelect.add(optionElement);
    });

    updateKaratInfo();
    calculateSamplePrice();
}

// Update subcategory defaults
function updateSubcategory() {
    const subcategoryId = document.getElementById('subcategory_id').value;

    if (subcategoryId) {
        // Fetch subcategory details if needed
        // For now, we'll use the values already shown in the form
        calculateSamplePrice();
    }
}

// Update karat information
function updateKaratInfo() {
    const karatSelect = document.getElementById('karat');
    const karatInfo = document.getElementById('karatInfo');

    if (karatSelect.value) {
        karatInfo.textContent = `Selected: ${karatSelect.options[karatSelect.selectedIndex].text}`;
        karatInfo.className = 'form-text text-success';
        calculateSamplePrice();
    } else {
        karatInfo.textContent = 'No karat specified (will use defaults for pricing)';
        karatInfo.className = 'form-text text-info';
    }
}

// Calculate sample price
function calculateSamplePrice() {
    const metalCategoryId = document.getElementById('metal_category_id').value;
    const subcategoryId = document.getElementById('subcategory_id').value;
    const karat = document.getElementById('karat').value;
    const weightInput = document.getElementById('weight');
    const weight = weightInput ? (weightInput.value || '1.0') : '1.0';
    const laborCost = document.getElementById('labor_cost').value;
    const profitMargin = document.getElementById('profit_margin').value;

    if (!metalCategoryId || !subcategoryId) {
        document.getElementById('priceCalculation').innerHTML =
            '<small class="text-muted">Please select metal category and subcategory to see price calculation</small>';
        return;
    }

    // Show loading
    document.getElementById('priceCalculation').innerHTML =
        '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Calculating price...';

    // Fetch live price calculation
    fetch('/admin/products/calculate-price', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            metal_category_id: metalCategoryId,
            subcategory_id: subcategoryId,
            karat: karat || null,
            weight: parseFloat(weight),
            labor_cost: laborCost || null,
            profit_margin: profitMargin || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const calc = data.calculation;
            document.getElementById('priceCalculation').innerHTML = `
                <div class="row g-2 small">
                    <div class="col-6"><strong>Weight:</strong></div>
                    <div class="col-6 text-end">${calc.weight}g</div>

                    <div class="col-6">Karat:</div>
                    <div class="col-6 text-end">${calc.karat || 'Default'}</div>

                    <div class="col-6">Metal Value:</div>
                    <div class="col-6 text-end">$${calc.metal_value}</div>

                    <div class="col-6">Labor Cost:</div>
                    <div class="col-6 text-end">$${calc.labor_cost}</div>

                    <div class="col-6">Base Cost:</div>
                    <div class="col-6 text-end">$${calc.base_cost}</div>

                    <div class="col-6">Profit (${calc.profit_margin}%):</div>
                    <div class="col-6 text-end">$${calc.profit_amount}</div>

                    <div class="col-6"><strong>Final Price:</strong></div>
                    <div class="col-6 text-end"><strong>$${calc.final_price} AUD</strong></div>
                </div>
            `;
        } else {
            document.getElementById('priceCalculation').innerHTML =
                `<small class="text-danger">Error calculating price: ${data.message}</small>`;
        }
    })
    .catch(error => {
        console.error('Error calculating price:', error);
        document.getElementById('priceCalculation').innerHTML =
            '<small class="text-danger">Error calculating price. Please try again.</small>';
    });
}

// Save as draft
function saveDraft() {
    const form = document.getElementById('productForm');
    const statusSelect = document.getElementById('is_active');
    statusSelect.value = '0'; // Set to inactive
    form.submit();
}
</script>
@endsection