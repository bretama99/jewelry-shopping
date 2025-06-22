{{-- File: resources/views/auth/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
            <p class="mb-0 text-muted">Modify user: <strong>{{ $user->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.show', $user) }}" class="btn btn-info">
                <i class="fas fa-eye me-1"></i> View
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>

                <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="userForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passport/ID Number -->
                            <div class="col-md-6 mb-3">
                                <label for="passport_id_number" class="form-label">Passport/ID Number</label>
                                <input type="text" class="form-control @error('passport_id_number') is-invalid @enderror"
                                       id="passport_id_number" name="passport_id_number" value="{{ old('passport_id_number', $user->passport_id_number) }}">
                                @error('passport_id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Current Profile Picture -->
                        @if($user->profile_picture)
                        <div class="mb-3">
                            <label class="form-label">Current Profile Picture</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}"
                                     class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                <div>
                                    <p class="mb-1"><strong>{{ basename($user->profile_picture) }}</strong></p>
                                    <p class="mb-1 text-muted small">Stored in: public/images/users/</p>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_profile_picture" name="remove_profile_picture" value="1">
                                        <label class="form-check-label text-danger" for="remove_profile_picture">
                                            Remove current picture
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Profile Picture -->
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">
                                {{ $user->profile_picture ? 'Replace Profile Picture' : 'Profile Picture' }}
                            </label>
                            <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                   id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Upload a profile picture (JPEG, PNG, JPG, GIF - Max: 2MB). Images are saved to public/images/users/</div>
                            @error('profile_picture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- New Image Preview -->
                            <div id="imagePreview" class="mt-3 d-none">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeImage">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Admin Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Type</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_admin"
                                           name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_admin">
                                        Administrator
                                    </label>
                                </div>
                                <div class="form-text">Administrators have access to the admin panel</div>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role</label>
                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                        @if(method_exists($role, 'isDefault') && $role->isDefault())
                                            (System Role)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Assign a role to define user permissions</div>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                            <div>
                                @if($user->canBeDeleted())
                                <button type="button" class="btn btn-danger me-2" onclick="deleteUser()">
                                    <i class="fas fa-trash me-1"></i> Delete User
                                </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update User
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Info Sidebar -->
        <div class="col-lg-4">
            <!-- User Statistics -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-1"></i> User Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <div class="h4 mb-0 text-primary">{{ $user->orders()->count() ?? 0 }}</div>
                                <div class="small text-muted">Orders</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-0 text-success">{{ $user->created_at->diffForHumans() }}</div>
                            <div class="small text-muted">Member Since</div>
                        </div>
                    </div>
                    <hr>
                    <div class="small">
                        <p><strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i') }}</p>
                        <p><strong>Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge {{ $user->status_badge }}">
                                {{ $user->status_text }}
                            </span>
                        </p>
                        @if($user->role)
                        <p><strong>Role:</strong>
                            <span class="badge {{ $user->role_badge }}">{{ $user->role->name }}</span>
                        </p>
                        @endif
                        @if($user->is_admin)
                        <p><strong>Type:</strong>
                            <span class="badge bg-warning text-dark"><i class="fas fa-shield-alt"></i> Administrator</span>
                        </p>
                        @endif
                        @if($user->profile_picture)
                        <p><strong>Image:</strong>
                            <span class="text-muted small">{{ basename($user->profile_picture) }}</span>
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-1"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleStatus()">
                            <i class="fas {{ $user->status == 'active' ? 'fa-user-slash' : 'fa-user-check' }} me-1"></i>
                            {{ $user->status == 'active' ? 'Deactivate' : 'Activate' }} User
                        </button>

                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="toggleAdmin()">
                            <i class="fas {{ $user->is_admin ? 'fa-user-minus' : 'fa-user-shield' }} me-1"></i>
                            {{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}
                        </button>

                        <a href="mailto:{{ $user->email }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-envelope me-1"></i> Send Email
                        </a>

                        @if($user->phone)
                        <a href="tel:{{ $user->phone }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-phone me-1"></i> Call User
                        </a>
                        @endif

                        @if($user->profile_picture)
                        <a href="{{ $user->profile_picture_url }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-image me-1"></i> View Full Image
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Preview -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-eye me-1"></i> Profile Preview
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div id="avatarPreview" class="mb-3">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}"
                                 class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width: 80px; height: 80px; font-size: 24px;">
                                {{ $user->initials }}
                            </div>
                        @endif
                    </div>
                    <h6 id="namePreview">{{ $user->name }}</h6>
                    <p class="text-muted small mb-2" id="emailPreview">{{ $user->email }}</p>
                    <span class="badge {{ $user->status_badge }}" id="statusPreview">{{ $user->status_text }}</span>
                    @if($user->is_admin)
                    <span class="badge bg-warning text-dark ms-1" id="adminPreview">
                        <i class="fas fa-shield-alt"></i> Admin
                    </span>
                    @else
                    <span class="badge bg-warning text-dark ms-1" id="adminPreview" style="display: none;">
                        <i class="fas fa-shield-alt"></i> Admin
                    </span>
                    @endif
                    @if($user->role)
                    <div class="mt-2" id="rolePreview">
                        <span class="badge {{ $user->role_badge }}" id="roleBadge">{{ $user->role->name }}</span>
                    </div>
                    @else
                    <div class="mt-2" id="rolePreview" style="display: none;">
                        <span class="badge bg-secondary" id="roleBadge">No Role</span>
                    </div>
                    @endif
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
                <p>Are you sure you want to delete the user <strong>{{ $user->name }}</strong>?</p>
                @if(!$user->canBeDeleted())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This user cannot be deleted because they have associated data in the system.
                    </div>
                @else
                    <p class="text-muted small">This action cannot be undone. The profile picture will also be deleted from public/images/users/</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                @if($user->canBeDeleted())
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- CSRF token for JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">
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

.img-thumbnail {
    border: 1px solid #e3e6f0;
}

.border-end {
    border-right: 1px solid #e3e6f0!important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');
    const statusSelect = document.getElementById('status');
    const isAdminCheck = document.getElementById('is_admin');
    const roleSelect = document.getElementById('role_id');
    const profilePictureInput = document.getElementById('profile_picture');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');
    const removeCurrentCheck = document.getElementById('remove_profile_picture');

    // Profile preview elements
    const namePreview = document.getElementById('namePreview');
    const emailPreview = document.getElementById('emailPreview');
    const statusPreview = document.getElementById('statusPreview');
    const adminPreview = document.getElementById('adminPreview');
    const rolePreview = document.getElementById('rolePreview');
    const roleBadge = document.getElementById('roleBadge');
    const avatarPreview = document.getElementById('avatarPreview');

    // Role data from server
    const roleData = @json($roles->keyBy('id'));

    // Update profile preview
    function updateProfilePreview() {
        const firstName = firstNameInput.value.trim();
        const lastName = lastNameInput.value.trim();
        const email = emailInput.value.trim();

        // Update name
        const fullName = [firstName, lastName].filter(n => n).join(' ') || '{{ $user->name }}';
        namePreview.textContent = fullName;

        // Update email
        emailPreview.textContent = email || '{{ $user->email }}';

        // Update status
        const statusText = statusSelect.options[statusSelect.selectedIndex].text;
        statusPreview.textContent = statusText;
        statusPreview.className = `badge ${getStatusBadgeClass(statusSelect.value)}`;

        // Update admin badge
        if (isAdminCheck.checked) {
            adminPreview.style.display = 'inline-block';
        } else {
            adminPreview.style.display = 'none';
        }

        // Update role
        const selectedRoleId = roleSelect.value;
        if (selectedRoleId && roleData[selectedRoleId]) {
            const role = roleData[selectedRoleId];
            rolePreview.style.display = 'block';
            roleBadge.textContent = role.name;
            roleBadge.className = `badge ${getRoleBadgeClass(role.slug)}`;
        } else {
            rolePreview.style.display = 'none';
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'active': return 'bg-success';
            case 'inactive': return 'bg-secondary';
            case 'suspended': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function getRoleBadgeClass(slug) {
        switch(slug) {
            case 'super-admin': return 'bg-danger';
            case 'admin': return 'bg-warning text-dark';
            case 'manager': return 'bg-info';
            case 'sales-rep': return 'bg-primary';
            case 'customer': return 'bg-success';
            default: return 'bg-secondary';
        }
    }

    // Event listeners for preview update
    [firstNameInput, lastNameInput, emailInput].forEach(input => {
        input.addEventListener('input', updateProfilePreview);
    });

    statusSelect.addEventListener('change', updateProfilePreview);
    isAdminCheck.addEventListener('change', updateProfilePreview);
    roleSelect.addEventListener('change', updateProfilePreview);

    // Image preview functionality
    profilePictureInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.remove('d-none');

                // Update avatar in profile preview
                avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">`;
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove new image
    removeImageBtn?.addEventListener('click', function() {
        profilePictureInput.value = '';
        imagePreview.classList.add('d-none');
        previewImg.src = '';

        // Reset avatar in profile preview to original or initials
        @if($user->profile_picture)
            if (!removeCurrentCheck?.checked) {
                avatarPreview.innerHTML = `<img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">`;
            } else {
                avatarPreview.innerHTML = `<div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 80px; height: 80px; font-size: 24px;">{{ $user->initials }}</div>`;
            }
        @else
            avatarPreview.innerHTML = `<div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 80px; height: 80px; font-size: 24px;">{{ $user->initials }}</div>`;
        @endif
    });

    // Handle remove current picture checkbox
    removeCurrentCheck?.addEventListener('change', function() {
        if (this.checked) {
            // Show initials instead of current picture
            const initials = '{{ $user->initials }}';
            avatarPreview.innerHTML = `<div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 80px; height: 80px; font-size: 24px;">${initials}</div>`;
        } else {
            // Show current picture again (unless new image is selected)
            if (!profilePictureInput.files[0]) {
                avatarPreview.innerHTML = `<img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">`;
            }
        }
    });

});

// Global functions for buttons
function deleteUser() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleStatus() {
    fetch(`/users/{{ $user->id }}/toggle-status`, {
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
}

function toggleAdmin() {
    fetch(`/users/{{ $user->id }}/toggle-admin`, {
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
            alert('Failed to update admin status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

</script>
@endpush
