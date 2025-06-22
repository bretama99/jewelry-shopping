
@extends('layouts.admin')

@section('title', 'Access Forbidden')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <div class="error-page py-5">
                <!-- Error Icon -->
                <div class="error-icon mb-4">
                    <i class="fas fa-shield-alt fa-6x text-warning"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold text-danger mb-3">403</h1>

                <!-- Error Message -->
                <h2 class="h3 fw-bold text-dark mb-3">Access Forbidden</h2>
                <p class="lead text-muted mb-4">
                    Sorry, you don't have permission to access this page.
                    This area might be restricted to administrators or require special privileges.
                </p>

                <!-- Additional Info -->
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Access Denied:</strong> You don't have the required permissions to view this content.
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mb-5">
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('external.home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        @else
                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Go Back
                            </a>
                        @endguest
                    </div>
                </div>

                <!-- What you can do -->
                <div class="alternatives">
                    <h5 class="fw-bold text-dark mb-3">What you can do instead:</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-shopping-bag fa-2x text-primary mb-3"></i>
                                    <h6 class="card-title">Shop Our Collection</h6>
                                    <p class="small text-muted">Browse our beautiful jewelry pieces</p>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                                        Browse Products
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-tags fa-2x text-success mb-3"></i>
                                    <h6 class="card-title">Explore Categories</h6>
                                    <p class="small text-muted">Discover different jewelry types</p>
                                    <a href="{{ route('categories.index') }}" class="btn btn-outline-success btn-sm">
                                        View Categories
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-headset fa-2x text-info mb-3"></i>
                                    <h6 class="card-title">Contact Support</h6>
                                    <p class="small text-muted">Get help from our team</p>
                                    <a href="#" class="btn btn-outline-info btn-sm">
                                        Contact Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Access Info -->
                @guest
                    <div class="admin-info mt-5 pt-4 border-top">
                        <h6 class="fw-bold text-dark mb-3">Looking for Admin Access?</h6>
                        <p class="text-muted mb-3">
                            If you're an administrator, please log in with your admin credentials.
                        </p>
                        <a href="{{ route('login') }}" class="btn btn-warning">
                            <i class="fas fa-user-shield me-2"></i>Admin Login
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.error-page {
    min-height: 70vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.error-icon {
    animation: shake 2s ease-in-out infinite;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.btn {
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .display-1 {
        font-size: 4rem;
    }

    .btn-lg {
        font-size: 1rem;
        padding: 0.75rem 1.5rem;
    }
}
</style>
@endpush
