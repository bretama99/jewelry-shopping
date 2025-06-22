{{-- File: resources/views/roles/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Roles Management</h1>
            <p class="mb-0 text-muted">Manage user roles and permissions</p>
        </div>
        <div class="d-flex gap-2">
            
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Role
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Inactive</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">System Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['system_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Custom Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['custom_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Users with Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users_with_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Roles List</h6>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Search roles..." class="form-control form-control-sm">

                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i>
                        </button>

                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
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
                            <option value="delete">Delete</option>
                        </select>
                        <button type="button" id="applyBulkAction" class="btn btn-sm btn-primary ms-2">Apply</button>
                        <span class="ms-2 text-muted"><span id="selectedCount">0</span> selected</span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'name'])) }}"
                           class="btn {{ ($filters['sort'] ?? '') == 'name' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-sort-alpha-down"></i> Name
                        </a>
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'users_count'])) }}"
                           class="btn {{ ($filters['sort'] ?? '') == 'users_count' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'created_at'])) }}"
                           class="btn {{ ($filters['sort'] ?? 'sort_order') == 'created_at' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-clock"></i> Date
                        </a>
                    </div>
                </div>
            </div>

            @if($roles->count() > 0)
                <!-- Roles Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Role</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input role-checkbox"
                                               value="{{ $role->id }}" {{ $role->isDefault() ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $role->name }}</strong>
                                            @if($role->isDefault())
                                                <span class="badge bg-info text-dark ms-1">
                                                    <i class="fas fa-lock"></i> System
                                                </span>
                                            @endif
                                            <br><small class="text-muted">{{ $role->slug }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ Str::limit($role->description, 60) ?: 'No description' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ count($role->permissions ?? []) }} permissions
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('users.index', ['role' => $role->slug]) }}" class="text-decoration-none">
                                            {{ $role->users_count }} users
                                        </a>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm toggle-status {{ $role->status_badge }}"
                                                data-id="{{ $role->id }}" data-status="{{ $role->is_active }}">
                                            {{ $role->status_text }}
                                        </button>
                                    </td>
                                
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- <a href="{{ route('roles.show', $role) }}"
                                               class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a> -->
                                            <a href="{{ route('roles.edit', $role) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!$role->isDefault())
                                            <button type="button" class="btn btn-outline-danger delete-role"
                                                    data-id="{{ $role->id }}"
                                                    data-name="{{ $role->name }}" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                                        data-bs-toggle="dropdown" title="More">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form action="{{ route('roles.duplicate', $role) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-copy me-1"></i> Duplicate
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
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
                            Showing {{ $roles->firstItem() }} to {{ $roles->lastItem() }}
                            of {{ $roles->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $roles->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-shield fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No roles found</h4>
                    <p class="text-muted">Start by creating your first role</p>
                    <a href="{{ route('roles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Role
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
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the role <strong id="roleName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CSRF token for JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('styles')
<style>
.border-left-secondary {
    border-left: 0.25rem solid #858796!important;
}

.table td {
    vertical-align: middle;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
}

.dropdown-menu {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Status
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const roleId = this.dataset.id;

            fetch(`/roles/${roleId}/toggle-status`, {
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
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    });

    // Delete Role
    document.querySelectorAll('.delete-role').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const roleId = this.dataset.id;
            const roleName = this.dataset.name;

            if (confirm(`Are you sure you want to delete "${roleName}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/roles/${roleId}`;

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

    // Select All Checkboxes
    const selectAllCheckbox = document.getElementById('selectAll');
    const roleCheckboxes = document.querySelectorAll('.role-checkbox:not(:disabled)');
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCount = document.getElementById('selectedCount');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            roleCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    roleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.role-checkbox:checked:not(:disabled)');
        const count = checkedBoxes.length;

        selectedCount.textContent = count;

        if (count > 0) {
            bulkActions.classList.remove('d-none');
        } else {
            bulkActions.classList.add('d-none');
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.indeterminate = count > 0 && count < roleCheckboxes.length;
            selectAllCheckbox.checked = count === roleCheckboxes.length;
        }
    }

    // Bulk Actions
    document.getElementById('applyBulkAction')?.addEventListener('click', function() {
        const action = document.getElementById('bulkAction').value;
        const checkedBoxes = document.querySelectorAll('.role-checkbox:checked:not(:disabled)');

        if (!action) {
            alert('Please select an action');
            return;
        }

        if (checkedBoxes.length === 0) {
            alert('Please select at least one role');
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete the selected roles?')) {
            return;
        }

        const roleIds = Array.from(checkedBoxes).map(cb => cb.value);

        fetch('/roles/bulk-action', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                role_ids: roleIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to perform bulk action');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    });
});
</script>
@endpush
