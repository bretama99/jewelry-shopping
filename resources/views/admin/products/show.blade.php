{{-- File: resources/views/admin/products/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'View Product')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $product->name }}</h1>
            <p class="mb-0 text-muted">Product Details & Information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
               class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>View on Website
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Product
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Product Information -->
        <div class="col-lg-8">
            <!-- Product Overview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Product Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="product-image text-center">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                     class="img-fluid rounded border" style="max-height: 400px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb-3">{{ $product->name }}</h4>

                            <div class="row g-3">
                                <div class="col-6">
                                    <strong>SKU:</strong>
                                    <span class="text-muted">{{ $product->sku ?? 'N/A' }}</span>
                                </div>
                                <div class="col-6">
                                    <strong>Category:</strong>
                                    @if($product->category)
                                        <span class="badge badge-info">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">Uncategorized</span>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <strong>Gold Karat:</strong>
                                    <span class="badge badge-dark">{{ $product->karat }}K</span>
                                </div>
                                <div class="col-6">
                                    <strong>Purity:</strong>
                                    <span class="text-success">{{ number_format($product->karat_purity * 100, 1) }}%</span>
                                </div>
                                <div class="col-6">
                                    <strong>Base Weight:</strong>
                                    <span class="text-primary">{{ number_format($product->base_weight, 3) }}g</span>
                                </div>
                                <div class="col-6">
                                    <strong>Weight Range:</strong>
                                    <span class="text-muted">{{ $product->formatted_weight_range }}</span>
                                </div>
                                <div class="col-6">
                                    <strong>Status:</strong>
                                    <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($product->is_featured)
                                        <span class="badge badge-warning ml-1">Featured</span>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <strong>Sort Order:</strong>
                                    <span class="text-muted">{{ $product->sort_order }}</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <strong>Current Price:</strong>
                                <h5 class="text-success mb-0">${{ number_format($product->calculatePrice($product->base_weight), 2) }}</h5>
                                <small class="text-muted">For {{ $product->base_weight }}g base weight</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Description -->
            @if($product->description)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Description</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $product->description }}</p>
                </div>
            </div>
            @endif

            <!-- Product Features -->
            @if($product->features_list && count($product->features_list) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Product Features</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($product->features_list as $feature)
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Pricing Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pricing Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary mb-3">Labor Cost</h6>
                                <h4 class="mb-1">${{ number_format($product->labor_cost, 2) }}</h4>
                                <small class="text-muted">Fixed craftsmanship cost</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-3">Profit Margin</h6>
                                <h4 class="mb-1">{{ number_format($product->profit_margin, 1) }}%</h4>
                                <small class="text-muted">Markup percentage</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-3">Price Calculator</h6>
                        <div id="priceBreakdown">
                            <div class="text-muted">Loading price calculation...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Product
                        </a>
                        <button class="btn btn-outline-secondary" onclick="duplicateProduct({{ $product->id }})">
                            <i class="fas fa-copy me-2"></i>Duplicate Product
                        </button>
                        <button class="btn btn-outline-info" onclick="toggleFeature({{ $product->id }})">
                            <i class="fas fa-star me-2"></i>{{ $product->is_featured ? 'Unfeature' : 'Feature' }} Product
                        </button>
                        <button class="btn btn-outline-warning" onclick="updatePrice({{ $product->id }})">
                            <i class="fas fa-dollar-sign me-2"></i>Update Pricing
                        </button>
                        @if($product->orderItems()->count() == 0)
                        <button class="btn btn-outline-danger" onclick="deleteProduct({{ $product->id }})">
                            <i class="fas fa-trash me-2"></i>Delete Product
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Product Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-12">
                            <div class="stat-item border rounded p-3">
                                <h5 class="text-primary mb-1">{{ $product->created_at->format('M d, Y') }}</h5>
                                <small class="text-muted">Created Date</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-item border rounded p-3">
                                <h5 class="text-success mb-1">{{ $product->updated_at->format('M d, Y') }}</h5>
                                <small class="text-muted">Last Updated</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-item border rounded p-3">
                                <h5 class="text-warning mb-1">{{ $product->orderItems()->count() }}</h5>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weight Specifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Weight Specifications</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Base Weight:</span>
                                <strong>{{ number_format($product->base_weight, 3) }}g</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Minimum Weight:</span>
                                <strong>{{ number_format($product->min_weight, 3) }}g</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Maximum Weight:</span>
                                <strong>{{ number_format($product->max_weight, 3) }}g</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span>Weight Step:</span>
                                <strong>{{ number_format($product->weight_step, 3) }}g</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Gold Prices -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-chart-line me-2"></i>Current Gold Prices
                    </h6>
                </div>
                <div class="card-body">
                    <div id="currentGoldPrices">
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2 small text-muted">Loading current prices...</div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-sync-alt me-1"></i>Prices update every 5 minutes
                        </small>
                    </div>
                </div>
            </div>

            <!-- SEO Information -->
            @if($product->meta_title || $product->meta_description)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">SEO Information</h6>
                </div>
                <div class="card-body">
                    @if($product->meta_title)
                    <div class="mb-3">
                        <strong>Meta Title:</strong>
                        <p class="text-muted mb-0">{{ $product->meta_title }}</p>
                    </div>
                    @endif
                    @if($product->meta_description)
                    <div class="mb-3">
                        <strong>Meta Description:</strong>
                        <p class="text-muted mb-0">{{ $product->meta_description }}</p>
                    </div>
                    @endif
                    <div>
                        <strong>URL Slug:</strong>
                        <code>{{ $product->slug }}</code>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone. All associated data will be permanently removed.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> Delete Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Price Update Modal -->
<div class="modal fade" id="priceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Product Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="priceForm">
                    <input type="hidden" id="productId" name="product_id">
                    <div class="mb-3">
                        <label for="laborCost" class="form-label">Labor Cost (per item)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="laborCost" name="labor_cost"
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="profitMargin" class="form-label">Profit Margin (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="profitMargin" name="profit_margin"
                                   step="0.1" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <small><strong>Note:</strong> Final price = (Gold Cost + Labor Cost) + Profit Margin</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmPriceUpdate()">
                    <i class="fas fa-save"></i> Update Price
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadGoldPrices();
    calculatePriceBreakdown();
});

// Load current gold prices
function loadGoldPrices() {
    fetch('/api/gold-prices')
        .then(response => response.json())
        .then(data => {
            let html = '<div class="row g-1">';
            for (const [karat, price] of Object.entries(data.prices)) {
                const isCurrentKarat = karat == '{{ $product->karat }}';
                html += `
                    <div class="col-6 d-flex justify-content-between ${isCurrentKarat ? 'bg-warning bg-opacity-25 rounded' : ''}">
                        <span class="fw-bold ${isCurrentKarat ? 'text-warning' : ''}">${karat}K:</span>
                        <span class="text-primary">${parseFloat(price).toFixed(2)}</span>
                    </div>
                `;
            }
            html += '</div>';
            document.getElementById('currentGoldPrices').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('currentGoldPrices').innerHTML =
                '<div class="text-danger text-center">Unable to load current prices</div>';
        });
}

// Calculate price breakdown
function calculatePriceBreakdown() {
    const karat = {{ $product->karat }};
    const baseWeight = {{ $product->base_weight }};
    const laborCost = {{ $product->labor_cost }};
    const profitMargin = {{ $product->profit_margin }};

    fetch('/api/gold-prices')
        .then(response => response.json())
        .then(goldPrices => {
            const goldPricePerGram = goldPrices.prices[karat] || 85.50;
            const purities = {
                10: 0.417, 14: 0.583, 18: 0.75,
                22: 0.917, 24: 1.0
            };

            const goldCost = baseWeight * goldPricePerGram * purities[karat];
            const totalCost = goldCost + laborCost;
            const profit = totalCost * (profitMargin / 100);
            const finalPrice = totalCost + profit;

            document.getElementById('priceBreakdown').innerHTML = `
                <div class="row g-2 small">
                    <div class="col-6">Gold Cost (${baseWeight}g × ${goldPricePerGram.toFixed(2)} × ${(purities[karat] * 100).toFixed(1)}%):</div>
                    <div class="col-6 text-end fw-bold">${goldCost.toFixed(2)}</div>
                    <div class="col-6">Labor Cost:</div>
                    <div class="col-6 text-end fw-bold">${laborCost.toFixed(2)}</div>
                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end fw-bold">${totalCost.toFixed(2)}</div>
                    <div class="col-6">Profit Margin (${profitMargin}%):</div>
                    <div class="col-6 text-end fw-bold">${profit.toFixed(2)}</div>
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-6 fw-bold text-primary">Final Price:</div>
                    <div class="col-6 text-end fw-bold text-primary h6">${finalPrice.toFixed(2)}</div>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('priceBreakdown').innerHTML =
                '<div class="text-danger">Error calculating price breakdown.</div>';
        });
}

// Toggle featured status
function toggleFeature(productId) {
    fetch(`/admin/products/${productId}/toggle-feature`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating featured status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating featured status');
    });
}

// Duplicate product
function duplicateProduct(productId) {
    if (confirm('Create a duplicate of this product?')) {
        fetch(`/admin/products/${productId}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    location.reload();
                }
            } else {
                alert('Error duplicating product: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error duplicating product');
        });
    }
}

// Update product price
function updatePrice(productId) {
    fetch(`/admin/products/${productId}/pricing`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('productId').value = productId;
            document.getElementById('laborCost').value = data.labor_cost || 10;
            document.getElementById('profitMargin').value = data.profit_margin || 20;

            const modal = new bootstrap.Modal(document.getElementById('priceModal'));
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error fetching pricing data:', error);
        alert('Error loading pricing data');
    });
}

function confirmPriceUpdate() {
    const productId = document.getElementById('productId').value;
    const laborCost = document.getElementById('laborCost').value;
    const profitMargin = document.getElementById('profitMargin').value;

    const updateBtn = event.target;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    updateBtn.disabled = true;

    fetch(`/admin/products/${productId}/update-pricing`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            labor_cost: laborCost,
            profit_margin: profitMargin
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('priceModal')).hide();
            alert('Price updated successfully!');
            location.reload();
        } else {
            alert('Error updating price: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating price');
    })
    .finally(() => {
        updateBtn.innerHTML = '<i class="fas fa-save"></i> Update Price';
        updateBtn.disabled = false;
    });
}

// Delete product
function deleteProduct(productId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();

    document.getElementById('confirmDelete').onclick = function() {
        const deleteBtn = this;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        deleteBtn.disabled = true;

        fetch(`/admin/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("admin.products.index") }}';
            } else {
                alert('Error deleting product: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting product');
        })
        .finally(() => {
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete Product';
            deleteBtn.disabled = false;
        });
    };
}
</script>
@endpush
