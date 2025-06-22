{{-- File: resources/views/admin/products/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New Product</h1>
            <p class="mb-0 text-muted">Add a new jewelry product to your store</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Products
        </a>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}"
          enctype="multipart/form-data" id="productForm">
        @csrf

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
                                   id="name" name="name" value="{{ old('name') }}"
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
                                                {{ old('metal_category_id') == $metalCategory->id ? 'selected' : '' }}>
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
                                                {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
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
                            <div class="form-text" id="karatInfo">Select metal category first to see available karats</div>
                            @error('karat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> -->

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Product Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4"
                                      placeholder="Detailed description of the product...">{{ old('description') }}</textarea>
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
                                       id="weight" name="weight" value="{{ old('weight') }}"
                                       step="0.001" min="0.001" max="9999.999"
                                       placeholder="e.g., 5.250" onchange="calculateSamplePrice()">
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="min_weight" class="form-label">Minimum Weight</label>
                                <input type="number" class="form-control @error('min_weight') is-invalid @enderror"
                                       id="min_weight" name="min_weight" value="{{ old('min_weight') }}"
                                       step="0.001" min="0.001" max="9999.999"
                                       placeholder="e.g., 2.000">
                                @error('min_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="max_weight" class="form-label">Maximum Weight</label>
                                <input type="number" class="form-control @error('max_weight') is-invalid @enderror"
                                       id="max_weight" name="max_weight" value="{{ old('max_weight') }}"
                                       step="0.001" min="0.001" max="9999.999"
                                       placeholder="e.g., 15.000">
                                @error('max_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="weight_step" class="form-label">Weight Step</label>
                                <input type="number" class="form-control @error('weight_step') is-invalid @enderror"
                                       id="weight_step" name="weight_step" value="{{ old('weight_step', '0.100') }}"
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
                                       id="labor_cost" name="labor_cost" value="{{ old('labor_cost') }}"
                                       step="0.01" min="0" max="99999.99"
                                       onchange="calculateSamplePrice()">
                                <div class="form-text">Leave empty to use subcategory default (<span id="defaultLaborCost">--</span> AUD/g)</div>
                                @error('labor_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="profit_margin" class="form-label">Profit Margin Override (%)</label>
                                <input type="number" class="form-control @error('profit_margin') is-invalid @enderror"
                                       id="profit_margin" name="profit_margin" value="{{ old('profit_margin') }}"
                                       step="0.01" min="0" max="100"
                                       onchange="calculateSamplePrice()">
                                <div class="form-text">Leave empty to use subcategory default (<span id="defaultProfitMargin">--</span>%)</div>
                                @error('profit_margin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                        </div>

                        <!-- Live Price Calculation -->
                        <!-- <div class="mt-4">
                            <h6>Price Calculation Preview</h6>
                            <div id="priceCalculation" class="bg-light p-3 rounded">
                                <small class="text-muted">Fill in the form to see live pricing calculation</small>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Product Image -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Product Image</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*" required>
                            <div class="form-text">Recommended: Square format (1:1 ratio), min 400x400px, max 3MB</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="text-center" style="display: none;">
                            <img id="previewImg" src="" alt="Preview"
                                 class="img-fluid rounded border" style="max-height: 300px;">
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
                                   id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity') }}"
                                   min="0" max="99999" placeholder="Leave blank to disable">
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                            <input type="number" class="form-control @error('min_stock_level') is-invalid @enderror"
                                   id="min_stock_level" name="min_stock_level" value="{{ old('min_stock_level') }}"
                                   min="0" max="99999" placeholder="Low stock alert threshold">
                            @error('min_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div> -->

                <!-- Product Status -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (Visible to customers)
                            </label>
                        </div>

                        <!-- <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Featured Product
                            </label>
                        </div> -->

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                   min="0" max="999">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="previewProduct()">
                            <i class="fas fa-eye me-2"></i>Preview
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">
                    <i class="fas fa-save me-2"></i>Create Product
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let availableKarats = {};
let metalCategories = @json($metalCategories);
let subcategories = @json($subcategories);

document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    // Image preview
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 3 * 1024 * 1024) { // 3MB limit
                alert('Image size must be less than 3MB');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Auto-fill weight ranges based on base weight (only if weight fields exist)
    const weightInput = document.getElementById('weight');
    if (weightInput) {
        weightInput.addEventListener('change', function() {
            const baseWeight = parseFloat(this.value);
            const minWeightInput = document.getElementById('min_weight');
            const maxWeightInput = document.getElementById('max_weight');
            
            if (baseWeight && minWeightInput && !minWeightInput.value) {
                minWeightInput.value = (baseWeight * 0.5).toFixed(3);
            }
            if (baseWeight && maxWeightInput && !maxWeightInput.value) {
                maxWeightInput.value = (baseWeight * 3).toFixed(3);
            }
        });
    }

    // Simplified form validation (removed weight requirements)
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const minWeightInput = document.getElementById('min_weight');
        const maxWeightInput = document.getElementById('max_weight');
        
        if (minWeightInput && maxWeightInput && minWeightInput.value && maxWeightInput.value) {
            const minWeight = parseFloat(minWeightInput.value);
            const maxWeight = parseFloat(maxWeightInput.value);
            
            if (minWeight >= maxWeight) {
                e.preventDefault();
                alert('Maximum weight must be greater than minimum weight.');
                return;
            }
        }
    });
});

// Update metal category and load available karats
function updateMetalCategory() {
    const metalCategoryId = document.getElementById('metal_category_id').value;
    const karatSelect = document.getElementById('karat');
    const subcategorySelect = document.getElementById('subcategory_id');

    // Clear karat options
    karatSelect.innerHTML = '<option value="">Loading...</option>';

    if (metalCategoryId) {
        // Fetch available karats for this metal
        fetch(`/admin/products/metal/${metalCategoryId}/karats`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availableKarats = data.karats;
                    karatSelect.innerHTML = '<option value="">No karat specified</option>';

                    data.karats.forEach(karat => {
                        const option = document.createElement('option');
                        option.value = karat;
                        option.textContent = formatKaratDisplay(karat, data.metal_name);
                        karatSelect.appendChild(option);
                    });

                    updateKaratInfo();
                }
            })
            .catch(error => {
                console.error('Error fetching karats:', error);
                karatSelect.innerHTML = '<option value="">Error loading karats</option>';
            });
    } else {
        karatSelect.innerHTML = '<option value="">Select metal category first</option>';
    }

    calculateSamplePrice();
}

// Update subcategory and load default pricing
function updateSubcategory() {
    const subcategoryId = document.getElementById('subcategory_id').value;

    if (subcategoryId) {
        const subcategory = subcategories.find(s => s.id == subcategoryId);
        if (subcategory) {
            document.getElementById('defaultLaborCost').textContent = subcategory.default_labor_cost || '--';
            document.getElementById('defaultProfitMargin').textContent = subcategory.default_profit_margin || '--';
        }
    } else {
        document.getElementById('defaultLaborCost').textContent = '--';
        document.getElementById('defaultProfitMargin').textContent = '--';
    }

    calculateSamplePrice();
}

// Format karat display based on metal type
function formatKaratDisplay(karat, metalName) {
    if (metalName === 'Silver') {
        return karat === '925' ? 'Sterling Silver (925)' : `Silver ${karat}`;
    } else if (metalName === 'Gold') {
        return `${karat}K Gold`;
    } else {
        return `${karat} ${metalName}`;
    }
}

// Update karat information
function updateKaratInfo() {
    const karat = document.getElementById('karat').value;
    const metalCategoryId = document.getElementById('metal_category_id').value;
    const karatInfo = document.getElementById('karatInfo');

    if (karat && metalCategoryId) {
        const metalCategory = metalCategories.find(m => m.id == metalCategoryId);
        if (metalCategory) {
            karatInfo.textContent = `Selected: ${formatKaratDisplay(karat, metalCategory.name)}`;
            karatInfo.className = 'form-text text-success';
        }
        calculateSamplePrice();
    } else if (metalCategoryId) {
        karatInfo.textContent = 'Karat not specified (will use defaults for pricing)';
        karatInfo.className = 'form-text text-info';
    } else {
        karatInfo.textContent = 'Select metal category first to see available karats';
        karatInfo.className = 'form-text';
    }
}

// Calculate sample price (simplified for optional fields)
function calculateSamplePrice() {
    const metalCategoryId = document.getElementById('metal_category_id').value;
    const subcategoryId = document.getElementById('subcategory_id').value;
    const karat = document.getElementById('karat').value;
    const weightInput = document.getElementById('weight');
    const weight = weightInput ? weightInput.value || '1.0' : '1.0';
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
                    <div class="col-6 text-end">${calc.metal_value}</div>

                    <div class="col-6">Labor Cost:</div>
                    <div class="col-6 text-end">${calc.labor_cost}</div>

                    <div class="col-6">Base Cost:</div>
                    <div class="col-6 text-end">${calc.base_cost}</div>

                    <div class="col-6">Profit (${calc.profit_margin}%):</div>
                    <div class="col-6 text-end">${calc.profit_amount}</div>

                    <div class="col-6"><strong>Final Price:</strong></div>
                    <div class="col-6 text-end"><strong>${calc.final_price} AUD</strong></div>
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

// Preview product (simplified)
function previewProduct() {
    const formData = new FormData(document.getElementById('productForm'));
    const name = formData.get('name') || 'Product Name';
    const metalCategoryId = formData.get('metal_category_id');
    const subcategoryId = formData.get('subcategory_id');
    const karat = formData.get('karat') || 'Not specified';
    const weight = formData.get('weight') || 'Not specified';
    const description = formData.get('description') || 'No description provided';

    const metalCategory = metalCategories.find(m => m.id == metalCategoryId);
    const subcategory = subcategories.find(s => s.id == subcategoryId);

    const previewHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="border rounded p-3 text-center">
                    ${document.getElementById('previewImg').src ?
                        `<img src="${document.getElementById('previewImg').src}" class="img-fluid rounded" style="max-height: 200px;">` :
                        '<div class="bg-light p-5 rounded"><i class="fas fa-image fa-3x text-muted"></i><br><small class="text-muted">No image selected</small></div>'
                    }
                </div>
            </div>
            <div class="col-md-6">
                <h5>${name}</h5>
                <p class="text-muted">${metalCategory ? metalCategory.name : 'No metal'} • ${subcategory ? subcategory.name : 'No subcategory'}</p>
                <p class="text-muted">Karat: ${karat} • Weight: ${weight}</p>
                <p>${description}</p>
                <div class="bg-light p-3 rounded">
                    <h6>Configuration:</h6>
                    <div class="small">
                        <div>Metal: ${metalCategory ? metalCategory.name : 'Not selected'}</div>
                        <div>Type: ${subcategory ? subcategory.name : 'Not selected'}</div>
                        <div>Purity: ${karat}</div>
                        <div>Weight: ${weight}</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('previewContent').innerHTML = previewHTML;
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Submit form from modal
function submitForm() {
    document.getElementById('productForm').submit();
}
</script>
@endpush