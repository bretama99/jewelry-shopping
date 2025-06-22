{{-- File: resources/views/roles/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Role</h1>
            <p class="mb-0 text-muted">Modify role: <strong>{{ $role->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roles.show', $role) }}" class="btn btn-info">
                <i class="fas fa-eye me-1"></i> View
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Roles
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Role Information</h6>
                </div>

                <form action="{{ route('roles.update', $role) }}" method="POST" id="roleForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Role Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $role->name) }}" required
                                       {{ $role->isDefault() ? 'readonly' : '' }}>
                                @if($role->isDefault())
                                    <div class="form-text text-warning">
                                        <i class="fas fa-lock me-1"></i> System role name cannot be changed
                                    </div>
                                @endif
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                            <div class="form-text">Brief description of the role's purpose</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                           name="is_active" value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                           {{ $role->isDefault() ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                                @if($role->isDefault())
                                    <div class="form-text text-warning">
                                        <i class="fas fa-lock me-1"></i> System role status cannot be changed
                                    </div>
                                @else
                                    <div class="form-text">Active roles can be assigned to users</div>
                                @endif
                            </div>

                            <!-- Sort Order -->
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $role->sort_order) }}" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPermissions">
                                            Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllPermissions">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>

                                @foreach($permissionGroups as $groupName => $permissions)
                                <div class="col-md-6 mb-4">
                                    <div class="card border">
                                        <div class="card-header py-2 bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $groupName }}</h6>
                                                <button type="button" class="btn btn-sm btn-outline-primary toggle-group"
                                                        data-group="{{ $groupName }}">
                                                    Toggle All
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            @foreach($permissions as $permission)
                                                @if(isset($availablePermissions[$permission]))
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input permission-checkbox"
                                                           type="checkbox"
                                                           id="permission_{{ $permission }}"
                                                           name="permissions[]"
                                                           value="{{ $permission }}"
                                                           data-group="{{ $groupName }}"
                                                           {{ in_array($permission, old('permissions', $role->permissions ?? [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="permission_{{ $permission }}">
                                                        {{ $availablePermissions[$permission] }}
                                                    </label>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                            <div>
                                @if(!$role->isDefault() && $role->canBeDeleted())
                                <button type="button" class="btn btn-danger me-2" onclick="deleteRole()">
                                    <i class="fas fa-trash me-1"></i> Delete Role
                                </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Role
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Info Sidebar -->
        <div class="col-lg-4">
            <!-- Role Statistics -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-1"></i> Role Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <div class="h4 mb-0 text-primary">{{ $role->users->count() }}</div>
                                <div class="small text-muted">Users</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-0 text-success">{{ count($role->permissions ?? []) }}</div>
                            <div class="small text-muted">Permissions</div>
                        </div>
                    </div>
                    <hr>
                    <div class="small">
                        <p><strong>Created:</strong> {{ $role->created_at->format('M d, Y H:i') }}</p>
                        <p><strong>Updated:</strong> {{ $role->updated_at->format('M d, Y H:i') }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge {{ $role->status_badge }}">
                                {{ $role->status_text }}
                            </span>
                        </p>
                        @if($role->isDefault())
                        <p><strong>Type:</strong>
                            <span class="badge bg-info text-dark"><i class="fas fa-lock"></i> System Role</span>
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Users with this Role -->
            @if($role->users->count() > 0)
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-1"></i> Users with this Role
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($role->users->take(5) as $user)
                    <div class="d-flex align-items-center mb-2">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}"
                                 class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2"
                                 style="width: 32px; height: 32px; font-size: 12px;">
                                {{ $user->initials }}
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="small fw-bold">{{ $user->name }}</div>
                            <div class="text-muted small">{{ $user->email }}</div>
                        </div>
                        <span class="badge {{ $user->status_badge }}">{{ $user->status_text }}</span>
                    </div>
                    @endforeach

                    @if($role->users->count() > 5)
                    <div class="text-center mt-3">
                        <a href="{{ route('users.index', ['role' => $role->slug]) }}" class="btn btn-sm btn-outline-primary">
                            View All {{ $role->users->count() }} Users
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Permission Summary -->
            <div class="card shadow mt-4" id="permissionSummary">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt me-1"></i> Permission Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div id="permissionCount" class="text-center mb-3">
                        <div class="h4 mb-0 text-primary">{{ count($role->permissions ?? []) }}</div>
                        <div class="small text-muted">Permissions Selected</div>
                    </div>
                    <div id="permissionsByGroup"></div>
                </div>
            </div>
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
                <p>Are you sure you want to delete the role <strong>{{ $role->name }}</strong>?</p>
                @if(!$role->canBeDeleted())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This role cannot be deleted because it has users assigned to it.
                    </div>
                @else
                    <p class="text-muted small">This action cannot be undone.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                @if($role->canBeDeleted())
                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.card {
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.text-gray-800 {
    color: #5a5c69!important;
}

.permission-checkbox {
    margin-right: 0.5rem;
}

.toggle-group {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.border-end {
    border-right: 1px solid #e3e6f0!important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const permissionCount = document.getElementById('permissionCount');
    const permissionsByGroup = document.getElementById('permissionsByGroup');

    // Auto-generate slug from name (only if not a default role)
    const isDefaultRole = {{ $role->isDefault() ? 'true' : 'false' }};

    if (!isDefaultRole) {
        nameInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated) {
                const slug = this.value.toLowerCase()
                    .replace(/[^a-zA-Z0-9\s]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });

        // Manual slug editing
        slugInput.addEventListener('input', function() {
            if (this.value) {
                this.dataset.autoGenerated = 'false';
            }
        });
    }

    // Permission management
    function updatePermissionSummary() {
        const checkedPermissions = document.querySelectorAll('.permission-checkbox:checked');
        const count = checkedPermissions.length;

        permissionCount.querySelector('.h4').textContent = count;

        // Group by category
        const groupCounts = {};
        checkedPermissions.forEach(checkbox => {
            const group = checkbox.dataset.group;
            groupCounts[group] = (groupCounts[group] || 0) + 1;
        });

        let html = '';
        for (const [group, count] of Object.entries(groupCounts)) {
            html += `<div class="d-flex justify-content-between mb-1">
                <span class="small">${group}</span>
                <span class="badge bg-primary">${count}</span>
            </div>`;
        }
        permissionsByGroup.innerHTML = html;
    }

    // Permission checkbox listeners
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updatePermissionSummary);
    });

    // Select/Deselect all permissions
    document.getElementById('selectAllPermissions').addEventListener('click', function() {
        permissionCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updatePermissionSummary();
    });

    document.getElementById('deselectAllPermissions').addEventListener('click', function() {
        permissionCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updatePermissionSummary();
    });

    // Toggle group permissions
    document.querySelectorAll('.toggle-group').forEach(button => {
        button.addEventListener('click', function() {
            const group = this.dataset.group;
            const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            const checkedCount = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]:checked`).length;
            const shouldCheck = checkedCount !== groupCheckboxes.length;

            groupCheckboxes.forEach(checkbox => {
                checkbox.checked = shouldCheck;
            });

            updatePermissionSummary();
        });
    });

    // Initial summary update
    updatePermissionSummary();

    // Form validation
    document.getElementById('roleForm').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        if (!name) {
            e.preventDefault();
            alert('Role name is required');
            nameInput.focus();
            return;
        }
    });
});

// Global functions
function deleteRole() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
