{{-- File: resources/views/auth/register.blade.php --}}
@extends('layouts.guest')

@section('title', 'Create Account')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="text-center mb-4">
        <div class="auth-logo mb-3">
            <i class="fas fa-gem fa-3x text-primary"></i>
        </div>
        <h2 class="fw-bold text-dark">Create Your Account</h2>
        <p class="text-muted">Join our exclusive jewelry collection</p>
    </div>

    <div class="row justify-content-center">
        <!-- Main Registration Form -->
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-user-plus me-2"></i>Personal Information
                    </h6>
                </div>

                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registrationForm">
                    @csrf
                    <div class="card-body p-4">
                        <!-- Name Fields Row -->
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-2 text-muted"></i>First Name <span class="text-danger">*</span>
                                </label>
                                <input id="first_name" type="text"
                                       class="form-control form-control-lg @error('first_name') is-invalid @enderror"
                                       name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" autofocus
                                       placeholder="Enter your first name">
                                @error('first_name')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-2 text-muted"></i>Last Name <span class="text-danger">*</span>
                                </label>
                                <input id="last_name" type="text"
                                       class="form-control form-control-lg @error('last_name') is-invalid @enderror"
                                       name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"
                                       placeholder="Enter your last name">
                                @error('last_name')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Fields Row -->
                        <div class="row">
                            <!-- Email Address -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2 text-muted"></i>Email Address <span class="text-danger">*</span>
                                </label>
                                <input id="email" type="email"
                                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required autocomplete="email"
                                       placeholder="Enter your email address">
                                @error('email')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-2 text-muted"></i>Phone Number
                                </label>
                                <input id="phone" type="tel"
                                       class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone') }}" autocomplete="tel"
                                       placeholder="Enter your phone number (optional)">
                                @error('phone')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Profile Picture -->
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">
                                <i class="fas fa-camera me-2 text-muted"></i>Profile Picture
                            </label>
                            <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                   id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Upload a profile picture (JPEG, PNG, JPG, GIF - Max: 2MB)</div>
                            @error('profile_picture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 d-none">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 120px;">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeImage">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>

                        <!-- Password Fields Row -->
                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2 text-muted"></i>Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input id="password" type="password"
                                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           name="password" required autocomplete="new-password"
                                           placeholder="Create a strong password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <small>Password must be at least 8 characters long</small>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password-confirm" class="form-label">
                                    <i class="fas fa-lock me-2 text-muted"></i>Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input id="password-confirm" type="password"
                                           class="form-control form-control-lg"
                                           name="password_confirmation" required autocomplete="new-password"
                                           placeholder="Confirm your password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password-confirm')">
                                        <i class="fas fa-eye" id="confirmPasswordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Agreement Fields Row -->
                        <div class="row">
                            <!-- Terms and Conditions -->
                            <div class="col-md-6 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the
                                        <a href="#" class="text-decoration-none">Terms of Service</a>
                                        and
                                        <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <!-- Marketing Emails -->
                            <div class="col-md-6 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="marketing" name="marketing"
                                           {{ old('marketing') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="marketing">
                                        I'd like to receive email updates about new collections and special offers
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Login Link -->
                            <div>
                                <p class="mb-0 text-muted small">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="text-decoration-none fw-bold">
                                        Sign In
                                    </a>
                                </p>
                            </div>
                            
                            <!-- Register Button -->
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Social Registration Section -->
            <div class="card shadow-lg border-0 mt-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-dark text-center">
                        <i class="fas fa-share-alt me-2"></i>Quick Registration
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="social-login">
                        <button type="button" class="btn btn-outline-danger w-100 mb-3" disabled>
                            <i class="fab fa-google me-2"></i>Sign up with Google
                            <small class="text-muted ms-2">(Coming Soon)</small>
                        </button>
                        <button type="button" class="btn btn-outline-primary w-100" disabled>
                            <i class="fab fa-facebook-f me-2"></i>Sign up with Facebook
                            <small class="text-muted ms-2">(Coming Soon)</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 col-md-10 mt-4 mt-lg-0">
            <!-- Profile Preview -->
            <div class="card shadow-lg border-0" id="profilePreviewCard" style="display: none;">
                <div class="card-header py-3 bg-success">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-eye me-2"></i>Profile Preview
                    </h6>
                </div>
                <div class="card-body text-center p-4">
                    <div id="avatarPreview" class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                             style="width: 80px; height: 80px; font-size: 24px;" id="initialsPreview">
                            --
                        </div>
                    </div>
                    <h6 id="namePreview" class="fw-bold">--</h6>
                    <p class="text-muted small mb-2" id="emailPreview">--</p>
                    <span class="badge bg-success" id="statusPreview">New Member</span>
                    <div class="mt-3 text-start">
                        <small class="text-muted">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Your profile will be activated immediately
                        </small>
                    </div>
                </div>
            </div>

            <!-- Member Benefits -->
            <div class="card shadow-lg border-0 mt-4">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-crown me-2"></i>Member Benefits
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="benefit-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-shipping-fast text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 small fw-bold">Free Shipping</h6>
                                <small class="text-muted">On orders over $500</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="benefit-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-percentage text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 small fw-bold">Exclusive Discounts</h6>
                                <small class="text-muted">Member-only pricing</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="benefit-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-star text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 small fw-bold">Early Access</h6>
                                <small class="text-muted">New collections first</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-headset text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 small fw-bold">Priority Support</h6>
                                <small class="text-muted">Dedicated customer care</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Information -->
            <div class="card shadow-lg border-0 mt-4">
                <div class="card-header py-3 bg-info">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-info-circle me-2"></i>Registration Help
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="small">
                        <p class="mb-2"><strong>Required Fields:</strong> First name, last name, email, and password are required.</p>
                        <p class="mb-2"><strong>Password:</strong> Must be at least 8 characters long for security.</p>
                        <p class="mb-2"><strong>Profile Picture:</strong> Optional image for your account avatar.</p>
                        <p class="mb-0"><strong>Privacy:</strong> Your information is protected with industry-standard encryption.</p>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="alert alert-success mt-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt me-2"></i>
                    <small>
                        <strong>Secure Registration:</strong> Your personal information is protected with SSL encryption.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.auth-logo {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.card {
    border-radius: 15px;
    transition: transform 0.3s ease;
    border: 1px solid #e3e6f0;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.card-footer {
    border-radius: 0 0 15px 15px !important;
    border-top: 1px solid #e3e6f0;
}

.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.social-login .btn {
    transition: all 0.3s ease;
}

.social-login .btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

.benefit-item {
    transition: transform 0.3s ease;
}

.benefit-item:hover {
    transform: translateX(5px);
}

.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

/* Two-column form enhancements */
.row .col-md-6 {
    position: relative;
}

.row .col-md-6:not(:last-child) {
    padding-right: 1rem;
}

.row .col-md-6:not(:first-child) {
    padding-left: 1rem;
}

/* Form styling */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.input-group .btn {
    border-left: 0;
}

.input-group .form-control:focus + .btn {
    border-color: var(--bs-primary);
}

.img-thumbnail {
    border: 1px solid #e3e6f0;
}

/* Alert customization */
.alert {
    border-radius: 10px;
    border: none;
}

@media (max-width: 576px) {
    .card-body {
        padding: 1.5rem !important;
    }

    /* Stack columns on mobile */
    .row .col-md-6 {
        margin-bottom: 1rem;
        padding-left: 15px !important;
        padding-right: 15px !important;
    }

    /* Adjust form text size for mobile */
    .form-text small {
        font-size: 0.75rem;
    }

    /* Adjust checkbox labels for mobile */
    .form-check-label {
        font-size: 0.875rem;
        line-height: 1.3;
    }

    .benefit-item {
        margin-bottom: 1rem !important;
    }
}

@media (max-width: 768px) {
    /* Reset padding on smaller tablets */
    .row .col-md-6 {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
}

@media (max-width: 991px) {
    .col-lg-8 {
        padding: 0 15px;
    }
}
</style>
@endpush

@push('scripts')
<script>
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

document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password-confirm');
    const profilePictureInput = document.getElementById('profile_picture');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');

    // Preview elements
    const profilePreviewCard = document.getElementById('profilePreviewCard');
    const namePreview = document.getElementById('namePreview');
    const emailPreview = document.getElementById('emailPreview');
    const initialsPreview = document.getElementById('initialsPreview');
    const avatarPreview = document.getElementById('avatarPreview');

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
        } else {
            profilePreviewCard.style.display = 'none';
        }
    }

    // Event listeners for preview update
    [firstNameInput, lastNameInput, emailInput].forEach(input => {
        input.addEventListener('input', updateProfilePreview);
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

    // Real-time password matching
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
        // Reset confirm password validation when password changes
        if (confirmPasswordInput.value) {
            confirmPasswordInput.dispatchEvent(new Event('input'));
        }
    });

    // Form validation feedback
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input[required]');

    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });

    // Email validation
    emailInput.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.classList.add('is-invalid');
            this.setCustomValidity('Please enter a valid email address');
        } else {
            this.setCustomValidity('');
            if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
    });

    // Phone validation (optional but format check)
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('blur', function() {
        if (this.value) {
            // Basic phone format validation (adjust regex as needed)
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(this.value.replace(/[\s\-\(\)]/g, ''))) {
                this.classList.add('is-invalid');
                this.setCustomValidity('Please enter a valid phone number');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
    });

    // Terms checkbox validation
    const termsCheckbox = document.getElementById('terms');
    termsCheckbox.addEventListener('change', function() {
        if (!this.checked) {
            this.setCustomValidity('You must agree to the terms and conditions');
        } else {
            this.setCustomValidity('');
        }
    });

    // Name fields validation (no numbers or special characters)
    ['first_name', 'last_name'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field.addEventListener('input', function() {
            // Remove any numbers or special characters except spaces, hyphens, and apostrophes
            this.value = this.value.replace(/[^a-zA-Z\s\-\']/g, '');
        });

        field.addEventListener('blur', function() {
            const nameRegex = /^[a-zA-Z\s\-\']{2,}$/;
            if (this.value && !nameRegex.test(this.value)) {
                this.classList.add('is-invalid');
                this.setCustomValidity('Please enter a valid name (letters only, minimum 2 characters)');
            } else {
                this.setCustomValidity('');
                if (this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            }
        });
    });

    // Animate form elements on load
    const formElements = document.querySelectorAll('.form-control, .btn, .form-check');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';

        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 30);
    });
});
</script>
@endpush