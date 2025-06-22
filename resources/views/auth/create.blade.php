{{-- File: resources/views/auth/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New User</h1>
            <p class="mb-0 text-muted">Add a new user to the system</p>
        </div>
        <div>
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

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" id="userForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passport/ID Number -->
                            <div class="col-md-6 mb-3">
                                <label for="passport_id_number" class="form-label">Passport/ID Number</label>
                                <input type="text" class="form-control @error('passport_id_number') is-invalid @enderror"
                                       id="passport_id_number" name="passport_id_number" value="{{ old('passport_id_number') }}">
                                @error('passport_id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Profile Picture -->
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                   id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Upload a profile picture (JPEG, PNG, JPG, GIF - Max: 2MB). Image will be saved to public/images/users/</div>
                            @error('profile_picture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 d-none">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeImage">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Password must be at least 8 characters long</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="confirmPasswordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Role Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="role_id" class="form-label">Role</label>
                                <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id">
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                            @if($role->isDefault())
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

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Admin Status -->
                        <div class="mb-3">
                            <label class="form-label">User Type</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_admin"
                                       name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_admin">
                                    Administrator
                                </label>
                            </div>
                            <div class="form-text">Administrators have enhanced access to admin features</div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Create User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-1"></i> User Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Required Fields:</strong> First name, last name, email, and password are required.</p>
                        <p><strong>Password:</strong> Must be at least 8 characters long for security.</p>
                        <p><strong>Profile Picture:</strong> Optional image that will be saved to public/images/users/ directory and displayed as the user's avatar.</p>
                        <p><strong>Role:</strong> Defines what permissions the user has in the system.</p>
                        <p><strong>Status:</strong>
                            <ul class="mb-2">
                                <li><strong>Active:</strong> User can log in and use the system</li>
                                <li><strong>Inactive:</strong> User account is disabled</li>
                                <li><strong>Suspended:</strong> User account is temporarily blocked</li>
                            </ul>
                        </p>
                        <p><strong>Administrator:</strong> Legacy flag for admin access. Consider using roles instead.</p>
                    </div>
                </div>
            </div>

            <!-- Role Information -->
            <div class="card shadow mt-4" id="roleInfoCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-shield me-1"></i> Role Information
                    </h6>
                </div>
                <div class="card-body">
                    <div id="roleDescription" class="mb-3"></div>
                    <div id="rolePermissions"></div>
                </div>
            </div>

            <!-- Profile Preview -->
            <div class="card shadow mt-4" id="profilePreviewCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-eye me-1"></i> Profile Preview
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div id="avatarPreview" class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                             style="width: 80px; height: 80px; font-size: 24px;" id="initialsPreview">
                            --
                        </div>
                    </div>
                    <h6 id="namePreview">--</h6>
                    <p class="text-muted small mb-2" id="emailPreview">--</p>
                    <span class="badge bg-success" id="statusPreview">Active</span>
                    <span class="badge bg-warning text-dark ms-1" id="adminPreview" style="display: none;">
                        <i class="fas fa-shield-alt"></i> Admin
                    </span>
                    <div class="mt-2" id="rolePreview" style="display: none;">
                        <span class="badge bg-info" id="roleBadge">Role</span>
                    </div>
                </div>
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

.img-thumbnail {
    border: 1px solid #e3e6f0;
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
    const profilePreviewCard = document.getElementById('profilePreviewCard');
    const roleInfoCard = document.getElementById('roleInfoCard');

    // Role data from server
    const roleData = @json($roles->keyBy('id'));

    // Profile preview elements
    const namePreview = document.getElementById('namePreview');
    const emailPreview = document.getElementById('emailPreview');
    const statusPreview = document.getElementById('statusPreview');
    const adminPreview = document.getElementById('adminPreview');
    const rolePreview = document.getElementById('rolePreview');
    const roleBadge = document.getElementById('roleBadge');
    const initialsPreview = document.getElementById('initialsPreview');
    const avatarPreview = document.getElementById('avatarPreview');

    // Role info elements
    const roleDescription = document.getElementById('roleDescription');
    const rolePermissions = document.getElementById('rolePermissions');

    // Update profile preview
    function updateProfilePreview() {
        const firstName = firstNameInput.value.trim();
        const lastName = lastNameInput.value.trim();
        const email = emailInput.value.trim();

        if (firstName || lastName || email) {
            profilePreviewCard.style.display = 'block';

            // Update name
            const fullName = [firstName, lastName].filter(n => n).join(' ') || '--';
            namePreview.textContent = fullName;

            // Update email
            emailPreview.textContent = email || '--';

            // Update initials
            const initials = [firstName.charAt(0), lastName.charAt(0)].filter(i => i).join('').toUpperCase() || '--';
            initialsPreview.textContent = initials;

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
        } else {
            profilePreviewCard.style.display = 'none';
        }
    }

    // Update role information
    function updateRoleInfo() {
        const selectedRoleId = roleSelect.value;
        if (selectedRoleId && roleData[selectedRoleId]) {
            const role = roleData[selectedRoleId];
            roleInfoCard.style.display = 'block';

            // Update description
            roleDescription.innerHTML = `<p class="mb-2"><strong>${role.name}</strong></p>
                <p class="text-muted small">${role.description || 'No description available'}</p>`;

            // Update permissions
            const permissionCount = role.permissions ? role.permissions.length : 0;
            rolePermissions.innerHTML = `<div class="text-center">
                <div class="h5 mb-0 text-info">${permissionCount}</div>
                <div class="small text-muted">Permissions</div>
            </div>`;
        } else {
            roleInfoCard.style.display = 'none';
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
    roleSelect.addEventListener('change', function() {
        updateProfilePreview();
        updateRoleInfo();
    });

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

    // Remove image
    removeImageBtn.addEventListener('click', function() {
        profilePictureInput.value = '';
        imagePreview.classList.add('d-none');
        previewImg.src = '';

        // Reset avatar in profile preview
        const initials = initialsPreview.textContent;
        avatarPreview.innerHTML = `<div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width: 80px; height: 80px; font-size: 24px;">${initials}</div>`;
    });

    // Password confirmation validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');

    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            this.setCustomValidity('Passwords do not match');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
            if (this.value.length > 0) {
                this.classList.add('is-valid');
            }
        }
    });

    passwordInput.addEventListener('input', function() {
        if (confirmPasswordInput.value) {
            confirmPasswordInput.dispatchEvent(new Event('input'));
        }
    });

    // Initial updates
    updateRoleInfo();
});

// Password toggle function
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(inputId === 'password' ? 'passwordToggleIcon' : 'confirmPasswordToggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endpush
