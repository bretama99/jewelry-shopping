{{-- File: resources/views/roles/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New Role</h1>
            <p class="mb-0 text-muted">Define a new role with specific permissions</p>
        </div>
        <div>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Roles
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Role Information</h6>
                </div>

                <form action="{{ route('roles.store') }}" method="POST" id="roleForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Role Name -->
                            <div class="col-md-4 mb-3">
                                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        <!-- Description -->
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                                <div class="form-text">Active roles can be assigned to users</div>
                            </div>

                            <!-- Sort Order -->
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
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
                                <div class="col-md-4 mb-4">
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
                                                           {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
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
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create Role
                            </button>
                        </div>
                    </div>
                </form>
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const permissionSummary = document.getElementById('permissionSummary');
    const permissionCount = document.getElementById('permissionCount');
    const permissionsByGroup = document.getElementById('permissionsByGroup');

    // Auto-generate slug from name
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

    // Permission management
    function updatePermissionSummary() {
        const checkedPermissions = document.querySelectorAll('.permission-checkbox:checked');
        const count = checkedPermissions.length;

        if (count > 0) {
            permissionSummary.style.display = 'block';
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
        } else {
            permissionSummary.style.display = 'none';
        }
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
</script>
@endpush
