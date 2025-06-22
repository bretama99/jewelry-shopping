@extends('layouts.admin')

@section('title', 'Subcategories Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Jewelry Categories</h1>
            <p class="mb-0 text-muted">Manage jewelry types and categories</p>
        </div>
        <div class="d-flex gap-2">
            
            <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Subcategory
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Subcategories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_subcategories'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Subcategories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_subcategories'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_products'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gem fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">Subcategories List</h6>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Search subcategories..." class="form-control form-control-sm">

                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>

                        <select name="sort" class="form-select form-select-sm">
                            <option value="sort_order" {{ ($filters['sort'] ?? '') == 'sort_order' ? 'selected' : '' }}>Sort Order</option>
                            <option value="name" {{ ($filters['sort'] ?? '') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="created_desc" {{ ($filters['sort'] ?? '') == 'created_desc' ? 'selected' : '' }}>Newest</option>
                            <option value="products_count" {{ ($filters['sort'] ?? '') == 'products_count' ? 'selected' : '' }}>Most Products</option>
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i>
                        </button>

                        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Bulk Actions -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="bulk-actions d-none">
                        <select id="bulkAction" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="unfeature">Unfeature</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="button" id="applyBulkAction" class="btn btn-sm btn-primary ms-2">Apply</button>
                        <span class="ms-2 text-muted"><span id="selectedCount">0</span> selected</span>
                    </div>
                </div>
            </div>

            @if($subcategories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th width="80">Image</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Labor Cost</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subcategories as $subcategory)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input subcategory-checkbox"
                                               value="{{ $subcategory->id }}">
                                    </td>
                                    <td>
                                        @if($subcategory->hasImage())
                                            <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name }}"
                                                 class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center img-thumbnail"
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $subcategory->name }}</strong>
                                            @if($subcategory->description)
                                                <br><small class="text-muted">{{ Str::limit($subcategory->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $subcategory->products_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="text-success">AUD ${{ number_format($subcategory->default_labor_cost, 2) }}/g</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm toggle-status {{ $subcategory->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                data-id="{{ $subcategory->id }}">
                                            {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.subcategories.edit', $subcategory) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger delete-subcategory"
                                                    data-id="{{ $subcategory->id }}"
                                                    data-name="{{ $subcategory->name }}" title="Delete">
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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            Showing {{ $subcategories->firstItem() }} to {{ $subcategories->lastItem() }}
                            of {{ $subcategories->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $subcategories->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-list fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No subcategories found</h4>
                    <p class="text-muted">Start by creating your first subcategory</p>
                    <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Subcategory
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Status
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const subcategoryId = this.dataset.id;

            fetch(`/admin/subcategories/${subcategoryId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            });
        });
    });

    // Toggle Feature
    document.querySelectorAll('.toggle-feature').forEach(button => {
        button.addEventListener('click', function() {
            const subcategoryId = this.dataset.id;

            fetch(`/admin/subcategories/${subcategoryId}/toggle-feature`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update feature status');
                }
            });
        });
    });

    // Delete Subcategory
    document.querySelectorAll('.delete-subcategory').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const subcategoryId = this.dataset.id;
            const subcategoryName = this.dataset.name;

            if (confirm(`Are you sure you want to delete "${subcategoryName}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/subcategories/${subcategoryId}`;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Bulk actions implementation
    const selectAllCheckbox = document.getElementById('selectAll');
    const subcategoryCheckboxes = document.querySelectorAll('.subcategory-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCount = document.getElementById('selectedCount');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            subcategoryCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    subcategoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.subcategory-checkbox:checked');
        const count = checkedBoxes.length;

        if (selectedCount) {
            selectedCount.textContent = count;
        }

        if (bulkActions) {
            if (count > 0) {
                bulkActions.classList.remove('d-none');
            } else {
                bulkActions.classList.add('d-none');
            }
        }
    }

    // Apply bulk action
    const applyBulkActionBtn = document.getElementById('applyBulkAction');
    if (applyBulkActionBtn) {
        applyBulkActionBtn.addEventListener('click', function() {
            const action = document.getElementById('bulkAction').value;
            const checkedBoxes = document.querySelectorAll('.subcategory-checkbox:checked');

            if (!action) {
                alert('Please select an action');
                return;
            }

            if (checkedBoxes.length === 0) {
                alert('Please select at least one subcategory');
                return;
            }

            if (action === 'delete' && !confirm('Are you sure you want to delete the selected subcategories?')) {
                return;
            }

            const subcategoryIds = Array.from(checkedBoxes).map(cb => cb.value);

            fetch('/admin/subcategories/bulk-action', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    subcategory_ids: subcategoryIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to perform bulk action');
                }
            });
        });
    }
});
</script>
@endpush
