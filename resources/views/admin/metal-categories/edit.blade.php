@extends('layouts.admin')

@section('title', 'Edit Metal Category')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Metal Category</h1>
            <p class="mb-0 text-muted">Update metal: <strong>{{ $metalCategory->name }} ({{ $metalCategory->symbol }})</strong></p>
        </div>
        <div class="d-flex gap-2">
           
            <a href="{{ route('admin.metal-categories.show', $metalCategory) }}" class="btn btn-outline-info">
                <i class="fas fa-eye me-2"></i>View Details
            </a>
            <a href="{{ route('admin.metal-categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Metal Categories
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.metal-categories.update', $metalCategory) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <!-- Metal Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Metal Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $metalCategory->name) }}"
                                   placeholder="e.g., Gold, Silver, Platinum, Palladium" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Metal Symbol -->
                        <div class="mb-3">
                            <label for="symbol" class="form-label">Metal Symbol <span class="text-danger">*</span></label>
                            <select class="form-select @error('symbol') is-invalid @enderror"
                                    id="symbol" name="symbol" required onchange="updateKaratInfo(); updatePricePreview();">
                                <option value="">Select Metal Symbol</option>
                                <option value="XAU" {{ old('symbol', $metalCategory->symbol) == 'XAU' ? 'selected' : '' }}>XAU (Gold)</option>
                                <option value="XAG" {{ old('symbol', $metalCategory->symbol) == 'XAG' ? 'selected' : '' }}>XAG (Silver)</option>
                                <option value="XPT" {{ old('symbol', $metalCategory->symbol) == 'XPT' ? 'selected' : '' }}>XPT (Platinum)</option>
                                <option value="XPD" {{ old('symbol', $metalCategory->symbol) == 'XPD' ? 'selected' : '' }}>XPD (Palladium)</option>
                            </select>
                       
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Metal Image</h6>
                    </div>
                    <div class="card-body">
                        <!-- Current Image -->
                        @if($metalCategory->image_url)
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $metalCategory->image_url }}" alt="{{ $metalCategory->name }}"
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
                            <label for="image" class="form-label">{{ $metalCategory->image_url ? 'Replace Image' : 'Upload Image' }}</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 2MB. Recommended: 400x300px</div>
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
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status & Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Status & Settings</h6>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select @error('is_active') is-invalid @enderror"
                                    id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $metalCategory->is_active) == 1 ? 'selected' : '' }}>
                                    Active (Available for products)
                                </option>
                                <option value="0" {{ old('is_active', $metalCategory->is_active) == 0 ? 'selected' : '' }}>
                                    Inactive (Hidden from products)
                                </option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order"
                                   value="{{ old('sort_order', $metalCategory->sort_order) }}"
                                   min="0" max="9999">
                            <div class="form-text">Lower numbers appear first in listings</div>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Metal Category
                            </button>
                           
                            <a href="{{ route('admin.metal-categories.show', $metalCategory) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                         
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.generated === 'true') {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
            slugInput.dataset.generated = 'true';
        }
    });

    slugInput.addEventListener('input', function() {
        this.dataset.generated = 'false';
    });

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

    // Initialize with current values
    updateKaratInfo();
    updatePricePreview();
});

function updateKaratInfo() {
    const symbol = document.getElementById('symbol').value;
    const karatInfo = document.getElementById('karatInfo');

    let karatText = '';

    switch(symbol) {
        case 'XAU':
            karatText = `
                <h6 class="text-warning mb-2">Gold Karat Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">24K (99.9% Pure)</div>
                    <div class="col-6">22K (91.7% Gold)</div>
                    <div class="col-6">21K (87.5% Gold)</div>
                    <div class="col-6">18K (75% Gold)</div>
                    <div class="col-6">14K (58.3% Gold)</div>
                    <div class="col-6">10K (41.7% Gold)</div>
                </div>
            `;
            break;
        case 'XAG':
            karatText = `
                <h6 class="text-secondary mb-2">Silver Purity Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">999 (99.9% Pure)</div>
                    <div class="col-6">925 (Sterling Silver)</div>
                    <div class="col-6">900 (Coin Silver)</div>
                    <div class="col-6">800 (80% Silver)</div>
                </div>
            `;
            break;
        case 'XPT':
            karatText = `
                <h6 class="text-info mb-2">Platinum Purity Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">999 (99.9% Pure)</div>
                    <div class="col-6">950 (95% Platinum)</div>
                    <div class="col-6">900 (90% Platinum)</div>
                    <div class="col-6">850 (85% Platinum)</div>
                </div>
            `;
            break;
        case 'XPD':
            karatText = `
                <h6 class="text-dark mb-2">Palladium Purity Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">999 (99.9% Pure)</div>
                    <div class="col-6">950 (95% Palladium)</div>
                    <div class="col-6">500 (50% Palladium)</div>
                </div>
            `;
            break;
        default:
            karatText = '<small class="text-muted">Select a metal symbol to see available purity levels</small>';
    }

    karatInfo.innerHTML = karatText;
}

function updatePricePreview() {
    const symbol = document.getElementById('symbol').value;
    const pricePreview = document.getElementById('pricePreview');

    if (!symbol) {
        pricePreview.innerHTML = `
            <i class="fas fa-coins fa-3x text-muted mb-3"></i>
            <p class="text-muted">Select a metal symbol to see current market price</p>
        `;
        return;
    }

    pricePreview.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2">Loading current market price...</p>
    `;

    fetch(`/admin/metal-categories/live-price/${symbol}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAllPriceDisplays(data);
                pricePreview.innerHTML = `
                    <div class="text-center">
                        <h4 class="text-success mb-1">${data.price_usd}</h4>
                        <small class="text-muted">USD per ounce</small>
                        <hr>
                        <div class="small">
                            <div>AUD Exchange Rate: ${data.exchange_rate}</div>
                            <div>Price per gram (AUD): ${data.price_per_gram_aud}</div>
                            <div class="text-muted mt-2">Last updated: ${data.last_updated}</div>
                        </div>
                    </div>
                `;
            } else {
                pricePreview.innerHTML = `
                    <div class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Unable to fetch current price</p>
                        <small>${data.message}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading price preview:', error);
            pricePreview.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Error loading price</p>
                </div>
            `;
        });
}

function updateAllPriceDisplays(data) {
    // Update current price
    document.getElementById('currentPrice').innerHTML = `
        <h5 class="text-success mb-0">${data.price_usd}</h5>
        <small class="text-muted">USD per ounce</small>
    `;

    // Update exchange rate
    document.getElementById('exchangeRate').innerHTML = `
        <h6 class="text-info mb-0">${data.exchange_rate}</h6>
        <small class="text-muted">1 USD = ${data.exchange_rate} AUD</small>
    `;

    // Update price per gram
    document.getElementById('pricePerGram').innerHTML = `
        <h6 class="text-warning mb-0">${data.price_per_gram_aud}</h6>
        <small class="text-muted">AUD per gram</small>
    `;

    // Update last updated
    document.getElementById('lastUpdated').innerHTML = `
        <span class="text-success">${data.last_updated}</span>
    `;
}


function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                     type === 'warning' ? 'alert-warning' : 'alert-danger';

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alert);

    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection>
                            