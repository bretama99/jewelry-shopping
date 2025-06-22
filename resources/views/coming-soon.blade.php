@extends('layouts.admin')

@section('title', $feature . ' - Coming Soon')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <i class="fas fa-tools fa-5x text-warning mb-4"></i>
                <h1 class="display-4 fw-bold text-dark mb-3">{{ $feature }}</h1>
                <h2 class="h4 text-muted mb-4">Coming Soon!</h2>
                <p class="lead text-muted mb-4">
                    We're working hard to bring you this feature. The {{ $feature }} functionality
                    will be available in the next update.
                </p>

                <div class="mb-4">
                    <div class="row g-3 justify-content-center">
                        <div class="col-auto">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">Shopping Cart</h6>
                                    <span class="badge bg-warning">In Development</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">Checkout</h6>
                                    <span class="badge bg-warning">In Development</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-pdf fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">PDF Receipts</h6>
                                    <span class="badge bg-warning">In Development</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('external.home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>Browse Categories
                    </a>
                </div>

                <div class="mt-5 pt-4 border-top">
                    <h6 class="text-muted mb-3">What's Available Now:</h6>
                    <div class="row g-2 justify-content-center">
                        <div class="col-auto">
                            <span class="badge bg-success">✓ Category Management</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success">✓ Product Catalog</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success">✓ Live Gold Pricing</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success">✓ Admin Panel</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success">✓ User Authentication</span>
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
.badge {
    font-size: 0.75rem;
}

.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
