
@extends('layouts.admin')

@section('title', 'Page Not Found')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <div class="error-page py-5">
                <!-- Error Icon -->
                <div class="error-icon mb-4">
                    <i class="fas fa-search fa-6x text-muted"></i>
                </div>

                <!-- Error Code -->
                <h1 class="display-1 fw-bold text-primary mb-3">404</h1>

                <!-- Error Message -->
                <h2 class="h3 fw-bold text-dark mb-3">Page Not Found</h2>
                <p class="lead text-muted mb-4">
                    Sorry, we couldn't find the page you're looking for.
                    The page might have been moved, deleted, or you might have entered the wrong URL.
                </p>

                <!-- Search Box -->
                <div class="search-section mb-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-search me-2 text-primary"></i>
                                Looking for something specific?
                            </h6>
                            <form action="{{ route('products.index') }}" method="GET" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search our jewelry collection..."
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mb-5">
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('external.home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-tags me-2"></i>Browse Categories
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-box me-2"></i>View All Products
                        </a>
                    </div>
                </div>

              

                <!-- Help Section -->
                <div class="help-section mt-5 pt-4 border-top">
                    <h6 class="fw-bold text-dark mb-3">Need Help?</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="help-item text-center">
                                <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                                <h6>Contact Support</h6>
                                <p class="small text-muted">Get help from our team</p>
                                <a href="#" class="btn btn-outline-primary btn-sm">Contact Us</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="help-item text-center">
                                <i class="fas fa-question-circle fa-2x text-info mb-2"></i>
                                <h6>FAQ</h6>
                                <p class="small text-muted">Find answers to common questions</p>
                                <a href="#" class="btn btn-outline-info btn-sm">View FAQ</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="help-item text-center">
                                <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                                <h6>Store Locator</h6>
                                <p class="small text-muted">Find a store near you</p>
                                <a href="#" class="btn btn-outline-success btn-sm">Find Stores</a>
                            </div>
                        </div>
                    </div>
                </div>
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
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.help-item {
    transition: transform 0.3s ease;
}

.help-item:hover {
    transform: translateY(-3px);
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track 404 errors for analytics (optional)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_not_found', {
            'page_location': window.location.href,
            'page_referrer': document.referrer
        });
    }

    // Auto-focus search input
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.focus();
    }
});
</script>
@endpush
