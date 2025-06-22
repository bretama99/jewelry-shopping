{{-- File: resources/views/profile/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-circle me-2"></i>My Profile
            </h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fas fa-key me-1"></i>Change Password
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="enableEdit()">
                    <i class="fas fa-edit me-1"></i>Edit Profile
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<div id="alertContainer"></div>

<div class="row">
    <!-- Profile Information Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('profilePictureInput').click()">
                    <i class="fas fa-camera me-1"></i>Change
                </button>
            </div>
            <div class="card-body text-center">
                <div class="profile-picture-container position-relative mb-3">
                    @if(Auth::user()->profile_picture)
                        <img id="profilePicture" src="{{ Auth::user()->profile_picture_url }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="rounded-circle shadow-sm" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div id="profilePicture" class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center shadow-sm"
                             style="width: 150px; height: 150px;">
                            <span class="text-white fw-bold" style="font-size: 3rem;">{{ Auth::user()->initials }}</span>
                        </div>
                    @endif
                    
                    @if(Auth::user()->profile_picture)
                        <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                style="top: 0; right: 0; border-radius: 50%;"
                                onclick="removeProfilePicture()"
                                title="Remove Picture">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
                
                <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                <p class="text-muted mb-2">{{ Auth::user()->email }}</p>
                
                @if(Auth::user()->role)
                    <span class="badge badge-{{ Auth::user()->role->name === 'admin' ? 'primary' : 'success' }} px-3 py-2">
                        <i class="fas fa-{{ Auth::user()->role->name === 'admin' ? 'user-shield' : 'user' }} me-1"></i>
                        {{ ucfirst(Auth::user()->role->name) }}
                    </span>
                @endif
                
                <div class="mt-3 pt-3 border-top">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-muted small">Member Since</div>
                            <div class="fw-bold">{{ Auth::user()->created_at->format('M Y') }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Last Login</div>
                            <div class="fw-bold">{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->diffForHumans() : 'N/A' }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Status</div>
                            <div class="fw-bold">
                                <span class="badge badge-{{ Auth::user()->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst(Auth::user()->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden file input for profile picture -->
                <input type="file" id="profilePictureInput" accept="image/*" style="display: none;" onchange="uploadProfilePicture(this)">
            </div>
        </div>
    </div>

    <!-- Profile Details Form -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
            </div>
            <div class="card-body">
                <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="{{ Auth::user()->first_name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="{{ Auth::user()->last_name }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ Auth::user()->email }}" readonly>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="passport_id_number" class="form-label">Passport/ID Number</label>
                        <input type="text" class="form-control" id="passport_id_number" name="passport_id_number" 
                               value="{{ Auth::user()->passport_id_number }}" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="{{ Auth::user()->phone }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="{{ Auth::user()->date_of_birth }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" readonly>{{ Auth::user()->address }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="{{ Auth::user()->city }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="{{ Auth::user()->state }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                       value="{{ Auth::user()->postal_code }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-control" id="country" name="country" disabled>
                            <option value="">Select Country</option>
                            <option value="AU" {{ Auth::user()->country === 'AU' ? 'selected' : '' }}>Australia</option>
                            <option value="US" {{ Auth::user()->country === 'US' ? 'selected' : '' }}>United States</option>
                            <option value="CA" {{ Auth::user()->country === 'CA' ? 'selected' : '' }}>Canada</option>
                            <option value="GB" {{ Auth::user()->country === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="IT" {{ Auth::user()->country === 'IT' ? 'selected' : '' }}>Italy</option>
                            <option value="FR" {{ Auth::user()->country === 'FR' ? 'selected' : '' }}>France</option>
                            <option value="DE" {{ Auth::user()->country === 'DE' ? 'selected' : '' }}>Germany</option>
                            <option value="JP" {{ Auth::user()->country === 'JP' ? 'selected' : '' }}>Japan</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    
                    <!-- Bio Section -->
                    <div class="form-group mb-4">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" 
                                  placeholder="Tell us a little about yourself..." readonly>{{ Auth::user()->bio }}</textarea>
                    </div>
                    
                    <!-- Form Actions (Hidden by default) -->
                    <div id="formActions" class="d-none">
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Activity & Preferences Section -->
<div class="row mt-4">
    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
            </div>
            <div class="card-body">
                <div class="activity-timeline">
                    <div class="activity-item">
                        <div class="activity-icon bg-success">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Logged in</h6>
                            <p class="text-muted small mb-0">{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->diffForHumans() : 'Never' }}</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Profile updated</h6>
                            <p class="text-muted small mb-0">{{ Auth::user()->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon bg-info">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="mb-1">Account created</h6>
                            <p class="text-muted small mb-0">{{ Auth::user()->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preferences -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Preferences</h6>
            </div>
            <div class="card-body">
                <form id="preferencesForm">
                    <div class="form-group mb-3">
                        <label class="form-label">Notification Settings</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                            <label class="form-check-label" for="emailNotifications">
                                Email notifications
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="orderUpdates" checked>
                            <label class="form-check-label" for="orderUpdates">
                                Order status updates
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="marketingEmails">
                            <label class="form-check-label" for="marketingEmails">
                                Marketing emails
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="timezone" class="form-label">Timezone</label>
                        <select class="form-control" id="timezone">
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">Eastern Time</option>
                            <option value="America/Chicago">Central Time</option>
                            <option value="America/Denver">Mountain Time</option>
                            <option value="America/Los_Angeles">Pacific Time</option>
                            <option value="Europe/London">London</option>
                            <option value="Europe/Paris">Paris</option>
                            <option value="Asia/Tokyo">Tokyo</option>
                            <option value="Australia/Sydney" selected>Sydney</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="language" class="form-label">Language</label>
                        <select class="form-control" id="language">
                            <option value="en" selected>English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="it">Italian</option>
                            <option value="ja">Japanese</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>Save Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Account Security Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Security</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-gray-800 mb-3">Password</h6>
                        <p class="text-muted mb-3">Keep your account secure with a strong password.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-1"></i>Change Password
                        </button>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-gray-800 mb-3">Two-Factor Authentication</h6>
                        <p class="text-muted mb-3">Add an extra layer of security to your account.</p>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="showComingSoon('Two-Factor Authentication')">
                            <i class="fas fa-shield-alt me-1"></i>Enable 2FA
                        </button>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-gray-800 mb-3">Login Sessions</h6>
                        <p class="text-muted mb-3">Monitor and manage your active sessions.</p>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="showComingSoon('Session Management')">
                            <i class="fas fa-desktop me-1"></i>View Sessions
                        </button>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-gray-800 mb-3">Account Deletion</h6>
                        <p class="text-muted mb-3">Permanently delete your account and all data.</p>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmAccountDeletion()">
                            <i class="fas fa-trash me-1"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.profile-picture-container {
    position: relative;
    display: inline-block;
}

.activity-timeline {
    position: relative;
}

.activity-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e3e6f0;
}

.activity-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.form-control:read-only {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
}

.form-control:read-only:focus {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
    box-shadow: none;
}

.badge {
    font-size: 0.75rem;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-sm {
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .d-sm-flex {
        flex-direction: column;
        align-items: stretch !important;
    }
    
    .d-sm-flex .d-flex {
        margin-top: 1rem;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
function enableEdit() {
    // Remove readonly attribute and enable form fields
    const formInputs = document.querySelectorAll('#profileForm input, #profileForm textarea, #profileForm select');
    formInputs.forEach(input => {
        input.removeAttribute('readonly');
        input.removeAttribute('disabled');
        input.classList.remove('form-control');
        input.classList.add('form-control');
    });
    
    // Show form actions
    document.getElementById('formActions').classList.remove('d-none');
    
    // Change button text
    event.target.innerHTML = '<i class="fas fa-times me-1"></i>Cancel Edit';
    event.target.setAttribute('onclick', 'cancelEdit()');
}

function cancelEdit() {
    // Add readonly attribute back
    const formInputs = document.querySelectorAll('#profileForm input, #profileForm textarea');
    formInputs.forEach(input => {
        input.setAttribute('readonly', 'readonly');
    });
    
    // Disable select fields
    const selectInputs = document.querySelectorAll('#profileForm select');
    selectInputs.forEach(select => {
        select.setAttribute('disabled', 'disabled');
    });
    
    // Hide form actions
    document.getElementById('formActions').classList.add('d-none');
    
    // Reset button
    const editBtn = document.querySelector('[onclick="cancelEdit()"]');
    editBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit Profile';
    editBtn.setAttribute('onclick', 'enableEdit()');
    
    // Reset form to original values
    location.reload();
}

function uploadProfilePicture(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('profile_picture', input.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Show loading state
        showAlert('info', 'Uploading profile picture...');
        
        fetch('{{ route("profile.upload-picture") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update profile picture
                const profilePicture = document.getElementById('profilePicture');
                profilePicture.innerHTML = `<img src="${data.url}" alt="Profile Picture" class="rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">`;
                
                showAlert('success', 'Profile picture updated successfully!');
            } else {
                showAlert('danger', data.message || 'Failed to upload profile picture.');
            }
        })
        .catch(error => {
            showAlert('danger', 'An error occurred while uploading the profile picture.');
        });
    }
}

function removeProfilePicture() {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        fetch('{{ route("profile.remove-picture") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset to initials
                const profilePicture = document.getElementById('profilePicture');
                profilePicture.innerHTML = `<div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 150px; height: 150px;">
                    <span class="text-white fw-bold" style="font-size: 3rem;">{{ Auth::user()->initials }}</span>
                </div>`;
                
                showAlert('success', 'Profile picture removed successfully!');
            } else {
                showAlert('danger', data.message || 'Failed to remove profile picture.');
            }
        })
        .catch(error => {
            showAlert('danger', 'An error occurred while removing the profile picture.');
        });
    }
}

function confirmAccountDeletion() {
    if (confirm('Are you sure you want to permanently delete your account? This action cannot be undone.')) {
        if (confirm('This will permanently delete all your data. Are you absolutely sure?')) {
            showAlert('warning', 'Account deletion feature is coming soon.');
        }
    }
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Handle profile form submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Profile updated successfully!');
            cancelEdit(); // Exit edit mode
        } else {
            showAlert('danger', data.message || 'Failed to update profile.');
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while updating your profile.');
    });
});

// Handle password change form
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("profile.change-password") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Password changed successfully!');
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
        } else {
            showAlert('danger', data.message || 'Failed to change password.');
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while changing your password.');
    });
});

// Handle preferences form
document.getElementById('preferencesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showAlert('info', 'Preferences saved! (Demo - not actually saved)');
});

// Coming soon function
function showComingSoon(feature) {
    showAlert('info', `${feature} feature is coming soon!`);
}
</script>
@endpush