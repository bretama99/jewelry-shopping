{{-- File: resources/views/auth/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Users Management</h1>
            <p class="mb-0 text-muted">Manage system users and customers</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.export') }}" class="btn btn-outline-success">
                <i class="fas fa-download me-1"></i> Export
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> Add User
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Suspended</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['suspended_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['admin_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['customer_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Users List</h6>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Search users..." class="form-control form-control-sm">

                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ ($filters['status'] ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>

                        <select name="user_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="admin" {{ ($filters['user_type'] ?? '') == 'admin' ? 'selected' : '' }}>Admins</option>
                            <option value="customer" {{ ($filters['user_type'] ?? '') == 'customer' ? 'selected' : '' }}>Customers</option>
                        </select>

                        <select name="role" class="form-select form-select-sm">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}" {{ ($filters['role'] ?? '') == $role->slug ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i>
                        </button>

                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
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
                            <option value="suspend">Suspend</option>
                            <option value="make_admin">Make Admin</option>
                            <option value="remove_admin">Remove Admin</option>
                            <option value="assign_role">Assign Role</option>
                            <option value="delete">Delete</option>
                        </select>
                        <select id="bulkRoleSelect" class="form-select form-select-sm d-inline-block w-auto d-none">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
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
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'email'])) }}"
                           class="btn {{ ($filters['sort'] ?? '') == 'email' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'role'])) }}"
                           class="btn {{ ($filters['sort'] ?? '') == 'role' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-user-shield"></i> Role
                        </a>
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'created_at'])) }}"
                           class="btn {{ ($filters['sort'] ?? 'created_at') == 'created_at' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-clock"></i> Date
                        </a>
                    </div>
                </div>
            </div>

            @if($users->count() > 0)
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th width="60">Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input user-checkbox"
                                               value="{{ $user->id }}">
                                    </td>
                                    <td>
                                        <div class="user-avatar position-relative">
                                            @if($user->profile_picture)
                                                <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}"
                                                     class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"
                                                     title="Image: {{ basename($user->profile_picture) }}">
                                                <div class="position-absolute top-0 start-100 translate-middle">
                                                    <span class="badge rounded-pill bg-success" title="Has profile picture">
                                                        <i class="fas fa-image" style="font-size: 8px;"></i>
                                                    </span>
                                                </div>
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold position-relative"
                                                     style="width: 40px; height: 40px; font-size: 14px;"
                                                     title="No profile picture - showing initials">
                                                    {{ $user->initials }}
                                                    <div class="position-absolute top-0 start-100 translate-middle">
                                                        <span class="badge rounded-pill bg-secondary" title="No profile picture">
                                                            <i class="fas fa-user" style="font-size: 8px;"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->is_admin)
                                                <span class="badge bg-warning text-dark ms-1">
                                                    <i class="fas fa-shield-alt"></i> Admin
                                                </span>
                                            @endif
                                            @if($user->passport_id_number)
                                                <br><small class="text-muted">ID: {{ $user->passport_id_number }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($user->role)
                                            <span class="badge {{ $user->role_badge }}">
                                                {{ $user->role->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->phone)
                                            <a href="tel:{{ $user->phone }}" class="text-decoration-none">
                                                {{ $user->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm toggle-status {{ $user->status_badge }}"
                                                data-id="{{ $user->id }}" data-status="{{ $user->status }}"
                                                title="Click to toggle status">
                                            {{ $user->status_text }}
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm toggle-admin {{ $user->is_admin ? 'btn-warning' : 'btn-outline-warning' }}"
                                                data-id="{{ $user->id }}" title="{{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}">
                                            <i class="fas {{ $user->is_admin ? 'fa-shield-alt' : 'fa-user' }}"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="{{ $user->created_at->format('F d, Y H:i:s') }}">
                                            {{ $user->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('users.show', $user) }}"
                                               class="btn btn-outline-info" title="View User">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}"
                                               class="btn btn-outline-primary" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->profile_picture)
                                            <a href="{{ $user->profile_picture_url }}" target="_blank"
                                               class="btn btn-outline-success" title="View Profile Picture">
                                                <i class="fas fa-image"></i>
                                            </a>
                                            @endif
                                            @if($user->canBeDeleted())
                                            <button type="button" class="btn btn-outline-danger delete-user"
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-outline-secondary" disabled
                                                    title="Cannot delete - user has associated data">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            @endif
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
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }}
                            of {{ $users->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No users found</h4>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status', 'user_type', 'role']))
                            Try adjusting your search criteria or
                            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">clear filters</a>
                        @else
                            Start by creating your first user
                        @endif
                    </p>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Create User
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
                <p>Are you sure you want to delete the user <strong id="userName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone. The user's profile picture (if any) will also be permanently deleted from the server.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
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

.user-avatar {
    transition: transform 0.3s ease;
    cursor: pointer;
}

.user-avatar:hover {
    transform: scale(1.1);
}

.table td {
    vertical-align: middle;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
}

.toggle-status {
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.toggle-status:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.position-relative .badge {
    font-size: 0.5rem;
}

/* Image loading animation */
@keyframes imageLoad {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

.user-avatar img {
    animation: imageLoad 0.3s ease-out;
}

/* Responsive table adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .user-avatar img,
    .user-avatar > div {
        width: 32px !important;
        height: 32px !important;
        font-size: 12px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Status
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            const currentStatus = this.dataset.status;

            // Add loading state
            const originalText = this.textContent;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            fetch(`/users/${userId}/toggle-status`, {
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
                    alert('Failed to update status: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating status');
            })
            .finally(() => {
                this.textContent = originalText;
                this.disabled = false;
            });
        });
    });

    // Toggle Admin
    document.querySelectorAll('.toggle-admin').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            const originalIcon = this.innerHTML;

            // Add loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            fetch(`/users/${userId}/toggle-admin`, {
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
                    alert('Failed to update admin status: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating admin status');
            })
            .finally(() => {
                this.innerHTML = originalIcon;
                this.disabled = false;
            });
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.id;
            const userName = this.dataset.name;

            document.getElementById('userName').textContent = userName;
            document.getElementById('deleteForm').action = `/users/${userId}`;

            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    // Select All Checkboxes
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionSelect = document.getElementById('bulkAction');
    const bulkRoleSelect = document.getElementById('bulkRoleSelect');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    // Show/hide role select based on action
    bulkActionSelect?.addEventListener('change', function() {
        if (this.value === 'assign_role') {
            bulkRoleSelect.classList.remove('d-none');
        } else {
            bulkRoleSelect.classList.add('d-none');
        }
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const count = checkedBoxes.length;

        if (selectedCount) selectedCount.textContent = count;

        if (count > 0) {
            bulkActions?.classList.remove('d-none');
        } else {
            bulkActions?.classList.add('d-none');
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.indeterminate = count > 0 && count < userCheckboxes.length;
            selectAllCheckbox.checked = count === userCheckboxes.length;
        }
    }

    // Bulk Actions
    document.getElementById('applyBulkAction')?.addEventListener('click', function() {
        const action = document.getElementById('bulkAction').value;
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');

        if (!action) {
            alert('Please select an action');
            return;
        }

        if (checkedBoxes.length === 0) {
            alert('Please select at least one user');
            return;
        }

        if (action === 'assign_role' && !document.getElementById('bulkRoleSelect').value) {
            alert('Please select a role');
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete the selected users? This will also delete their profile pictures permanently.')) {
            return;
        }

        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        const requestData = {
            action: action,
            user_ids: userIds
        };

        if (action === 'assign_role') {
            requestData.role_id = document.getElementById('bulkRoleSelect').value;
        }

        // Add loading state
        const originalText = this.textContent;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        this.disabled = true;

        fetch('/users/bulk-action', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
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
            alert('An error occurred while performing bulk action');
        })
        .finally(() => {
            this.textContent = originalText;
            this.disabled = false;
        });
    });

    // Image loading error handling
    document.querySelectorAll('.user-avatar img').forEach(img => {
        img.addEventListener('error', function() {
            // Replace broken image with initials
            const userName = this.alt;
            const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase();

            const initialsDiv = document.createElement('div');
            initialsDiv.className = 'bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold';
            initialsDiv.style.cssText = 'width: 40px; height: 40px; font-size: 14px;';
            initialsDiv.textContent = initials;
            initialsDiv.title = 'Image failed to load';

            this.parentNode.replaceChild(initialsDiv, this);
        });
    });

    // Image hover effects
    document.querySelectorAll('.user-avatar').forEach(avatar => {
        avatar.addEventListener('click', function() {
            const img = this.querySelector('img');
            if (img) {
                // Create modal or lightbox effect
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Profile Picture</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${img.src}" alt="${img.alt}" class="img-fluid rounded">
                            </div>
                            <div class="modal-footer">
                                <a href="${img.src}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> Open Full Size
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                new bootstrap.Modal(modal).show();

                // Remove modal from DOM when hidden
                modal.addEventListener('hidden.bs.modal', function() {
                    modal.remove();
                });
            }
        });
    });

    // Search form enhancements
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            if (this.value.length >= 3) {
                searchTimeout = setTimeout(() => {
                    // Auto-submit search after 1 second of no typing
                    this.form.submit();
                }, 1000);
            }
        });
    }

    // Tooltip initialization for better UX
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Status update animation
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Real-time stats update (optional enhancement)
    function updateStats() {
        fetch('/users/stats', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stat cards with new data
                Object.keys(data.stats).forEach(key => {
                    const element = document.querySelector(`[data-stat="${key}"]`);
                    if (element) {
                        element.textContent = data.stats[key];
                    }
                });
            }
        })
        .catch(error => console.log('Stats update failed:', error));
    }

    // Update stats every 30 seconds (optional)
    // setInterval(updateStats, 30000);
});

// Global utility functions
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            // Show success message
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check me-1"></i> Copied to clipboard!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            new bootstrap.Toast(toast).show();

            setTimeout(() => toast.remove(), 3000);
        });
    }
}

// Export function for developer tools
window.userManagement = {
    updateStats,
    previewImage,
    copyToClipboard,
    version: '1.0.0'
};
</script>
@endpush
