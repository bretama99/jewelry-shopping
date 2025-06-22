{{-- File: resources/views/admin/products/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Products Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Products Management</h1>
            <p class="mb-0 text-muted">Manage your jewelry catalog with dynamic pricing</p>
        </div>
        <div class="d-flex gap-2">
            <!-- <button type="button" class="btn btn-outline-success" onclick="refreshPrices()" id="refreshPricesBtn">
                <i class="fas fa-sync-alt me-2"></i>Refresh Metal Prices
            </button> -->
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Product
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Product name, SKU...">
                    </div>

                    <!-- Metal Category Filter -->
                    <div class="col-md-2">
                        <label for="metal_category" class="form-label">Metal Category</label>
                        <select class="form-select" id="metal_category" name="metal_category">
                            <option value="">All Metals</option>
                            @foreach($metalCategories as $metalCategory)
                                <option value="{{ $metalCategory->id }}"
                                        {{ request('metal_category') == $metalCategory->id ? 'selected' : '' }}>
                                    {{ $metalCategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subcategory Filter -->
                    <div class="col-md-2">
                        <label for="subcategory" class="form-label">Subcategory</label>
                        <select class="form-select" id="subcategory" name="subcategory">
                            <option value="">All Types</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}"
                                        {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                            <option value="updated_at" {{ request('sort') == 'updated_at' ? 'selected' : '' }}>Date Updated</option>
                            <option value="sort_order" {{ request('sort') == 'sort_order' ? 'selected' : '' }}>Sort Order</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($products->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    Bulk Actions
                    <span class="badge bg-secondary ms-2" id="selectedCount">0 selected</span>
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('activate')" disabled id="bulkActivateBtn">
                        <i class="fas fa-check me-1"></i>Activate Selected
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')" disabled id="bulkDeactivateBtn">
                        <i class="fas fa-pause me-1"></i>Deactivate Selected
                    </button>
                </div>
              
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')" disabled id="bulkDeleteBtn">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    Products ({{ $products->total() }} total)
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Show per page:</small>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                </th>
                                <th style="width: 110px;">Image</th>
                                <th>Product Details</th>
                                <th>Metal</th>
                                <!-- <th>Live Price</th> -->
                                <th>Status</th>
                                <th style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-checkbox"
                                               value="{{ $product->id }}" onchange="updateBulkActions()">
                                    </td>
                                    <td>
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                 class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route('admin.products.show', $product) }}"
                                                   class="text-decoration-none">{{ $product->name }}</a>
                                            </h6>
                                            <small class="text-muted">
                                                SKU: {{ $product->sku ?: 'N/A' }} |
                                                {{ $product->subcategory?->name }}
                                            </small>
                                           
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $product->metalCategory?->name }}</strong>
                                            
                                        </div>
                                    </td>
                                   
                                    <!-- <td>
                                        <div class="price-display" data-product-id="{{ $product->id }}">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </td> -->
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($product->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                            <small class="text-muted">
                                                Updated: {{ $product->updated_at->format('M d, Y') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- <a href="{{ route('admin.products.show', $product) }}"
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a> -->
                                            <a href="{{ route('admin.products.edit', $product) }}"
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- <a href="{{ route('products.show', $product) }}"
                                               class="btn btn-sm btn-outline-secondary" title="View on Website" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a> -->
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Delete" onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-gem fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Products Found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'metal_category', 'subcategory', 'status']))
                            No products match your current filters.
                            <a href="{{ route('admin.products.index') }}" class="text-decoration-none">Clear filters</a>
                        @else
                            Start building your jewelry catalog by adding your first product.
                        @endif
                    </p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Your First Product
                    </a>
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
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load live prices for all products
    loadLivePrices();

    // Auto-refresh prices every 5 minutes
    setInterval(loadLivePrices, 5 * 60 * 1000);
});

// Load live prices for all visible products
function loadLivePrices() {
    const priceDisplays = document.querySelectorAll('.price-display');

    priceDisplays.forEach(display => {
        const productId = display.dataset.productId;

        fetch(`/admin/products/${productId}/live-price`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    display.innerHTML = `
                        <div class="small">
                            <strong class="text-success">$${data.base_price} AUD</strong><br>
                            <small class="text-muted">${data.metal_symbol} $${data.metal_price_usd}/oz</small>
                        </div>
                    `;
                } else {
                    display.innerHTML = `
                        <div class="small text-danger">
                            Price Error<br>
                            <small>Check metal API</small>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading price for product', productId, error);
                display.innerHTML = `
                    <div class="small text-muted">
                        Price Unavailable<br>
                        <small>API Error</small>
                    </div>
                `;
            });
    });
}

// Refresh metal prices manually
function refreshPrices() {
    const btn = document.getElementById('refreshPricesBtn');
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;

    fetch('/admin/metal-prices/refresh', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload prices after refresh
            loadLivePrices();

            // Show success message
            showAlert('success', 'Metal prices refreshed successfully!');
        } else {
            showAlert('error', 'Failed to refresh prices: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error refreshing prices:', error);
        showAlert('error', 'Error refreshing prices. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Change per page
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    window.location.href = url.toString();
}

// Select all products
function selectAll() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    updateBulkActions();
}

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActions();
}

// Toggle select all
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.product-checkbox');

    checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
    updateBulkActions();
}

// Update bulk action buttons
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const count = checkedBoxes.length;

    document.getElementById('selectedCount').textContent = `${count} selected`;

    const bulkButtons = ['bulkActivateBtn', 'bulkDeactivateBtn', 'bulkFeatureBtn', 'bulkDeleteBtn'];
    bulkButtons.forEach(btnId => {
        document.getElementById(btnId).disabled = count === 0;
    });

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.product-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    if (count === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (count === allCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

// Bulk actions
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const productIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (productIds.length === 0) {
        showAlert('warning', 'Please select at least one product.');
        return;
    }

    let confirmMessage;
    let actionText;

    switch(action) {
        case 'activate':
            confirmMessage = `Activate ${productIds.length} selected product(s)?`;
            actionText = 'Activating';
            break;
        case 'deactivate':
            confirmMessage = `Deactivate ${productIds.length} selected product(s)?`;
            actionText = 'Deactivating';
            break;
        case 'feature':
            confirmMessage = `Mark ${productIds.length} selected product(s) as featured?`;
            actionText = 'Updating';
            break;
        case 'delete':
            confirmMessage = `Delete ${productIds.length} selected product(s)? This action cannot be undone.`;
            actionText = 'Deleting';
            break;
    }

    if (!confirm(confirmMessage)) return;

    // Show loading state
    const btn = document.getElementById(`bulk${action.charAt(0).toUpperCase() + action.slice(1)}Btn`);
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${actionText}...`;
    btn.disabled = true;

    fetch('/admin/products/bulk-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            product_ids: productIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Reload page to reflect changes
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Bulk action error:', error);
        showAlert('error', 'An error occurred. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Delete single product
function deleteProduct(productId, productName) {
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('deleteForm').action = `/admin/products/${productId}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Show alert message
function showAlert(type, message) {
    // Create alert element
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

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection
