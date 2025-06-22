{{-- File: resources/views/auth/login.blade.php --}}
@extends('layouts.guestl')

@section('title', 'Login')
@section('subtitle', 'Sign in to access your jewelry store account')

@section('content')
<div class="row">
        <div class="col-lg-12 col-md-12">

<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope me-2 text-muted"></i>{{ __('Email Address') }}
        </label>
        <div class="input-group ">
            <span class="input-group-text">
                <i class="fas fa-envelope"></i>
            </span>
            <input id="email" 
                   type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="email" 
                   autofocus
                   placeholder="Enter your email address">
        </div>
        @error('email')
            <div class="invalid-feedback d-block">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <!-- Password -->
    <div class="form-group">
        <label for="password">
            <i class="fas fa-lock me-2 text-muted"></i>{{ __('Password') }}
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input id="password" 
                   type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   placeholder="Enter your password">
            <button class="btn btn-outline-secondary" 
                    type="button" 
                    id="togglePassword"
                    style="border-left: none; border-radius: 0 12px 12px 0; border-color: #e3e6f0;">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" 
                   type="checkbox" 
                   name="remember" 
                   id="remember" 
                   {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                {{ __('Remember Me') }}
            </label>
        </div>

        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: var(--primary-color); font-weight: 500; font-size: 0.9rem;">
                {{ __('Forgot Password?') }}
            </a>
        @endif
    </div>

    <!-- Submit Button -->
    <div class="form-group mb-0">
        <button type="submit" 
                class="btn btn-primary w-100"
                data-original-text="Sign In">
            <i class="fas fa-sign-in-alt me-2"></i>
            {{ __('Sign In') }}
        </button>
    </div>
</form>
</div>
</div>

@endsection

@section('auth-links')
    <div class="row text-center">
        @if (Route::has('register'))
        <div class="col-12 mb-2">
            <span class="text-muted">Don't have an account?</span>
            <a href="{{ route('register') }}" class="fw-bold ms-1">
                {{ __('Create Account') }}
            </a>
        </div>
        @endif
        
    </div>
@endsection

@push('styles')
<style>
.demo-account:hover .card {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.demo-account .demo-icon {
    transition: transform 0.3s ease;
}

.demo-account:hover .demo-icon {
    transform: scale(1.1);
}

.demo-accounts-section {
    margin-top: 1rem;
}

.social-login .btn {
    transition: all 0.3s ease;
    font-weight: 500;
}

.social-login .btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

/* Enhanced form animations */
.form-control {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-control:focus {
    transform: translateY(-1px);
}

.input-group-text {
    transition: all 0.3s ease;
}

/* Loading animation for submit button */
.btn-primary.loading {
    position: relative;
    color: transparent;
}

.btn-primary.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin-top: -10px;
    margin-left: -10px;
    border: 2px solid #ffffff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Pulse effect for demo accounts */
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(78, 115, 223, 0); }
    100% { box-shadow: 0 0 0 0 rgba(78, 115, 223, 0); }
}

.demo-account.clicked {
    animation: pulse 0.6s;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility with enhanced animation
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
                icon.style.transform = 'scale(1)';
            }, 150);
        });
    }

    // Enhanced demo account auto-fill
    const demoAccounts = document.querySelectorAll('.demo-account');
    demoAccounts.forEach(account => {
        account.addEventListener('click', function() {
            const isAdmin = this.classList.contains('admin-demo');
            const email = isAdmin ? 'admin@jewelrystore.com' : 'customer@example.com';

            // Add clicked animation
            this.classList.add('clicked');
            setTimeout(() => {
                this.classList.remove('clicked');
            }, 600);

            // Fill form with smooth animation
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            // Clear inputs first
            emailInput.value = '';
            passwordInput.value = '';
            
            // Type effect for email
            let i = 0;
            const typeEmail = () => {
                if (i < email.length) {
                    emailInput.value += email.charAt(i);
                    i++;
                    setTimeout(typeEmail, 50);
                } else {
                    // Type password after email is done
                    let j = 0;
                    const typePassword = () => {
                        if (j < 'password123'.length) {
                            passwordInput.value += 'password123'.charAt(j);
                            j++;
                            setTimeout(typePassword, 50);
                        }
                    };
                    setTimeout(typePassword, 200);
                }
            };
            
            setTimeout(typeEmail, 300);

            // Visual feedback
            const card = this.querySelector('.card');
            const originalTransform = card.style.transform;
            card.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                card.style.transform = originalTransform;
            }, 200);
        });
    });

    // Form submission with loading state
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;

    form.addEventListener('submit', function(e) {
        submitButton.classList.add('loading');
        submitButton.disabled = true;
        
        // Re-enable after 5 seconds as fallback
        setTimeout(() => {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }, 5000);
    });

    // Enhanced input validation with real-time feedback
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
                this.style.borderColor = 'var(--danger-color)';
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                this.style.borderColor = 'var(--success-color)';
            }
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                this.style.borderColor = 'var(--success-color)';
            }
        });

        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary-color)';
        });
    });

    // Animate form elements on load
    const formElements = document.querySelectorAll('.form-group, .demo-accounts-section, .social-login');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';

        setTimeout(() => {
            element.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100 + 200);
    });
});
</script>
@endpush