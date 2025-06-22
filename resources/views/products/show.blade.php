@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('external.home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categories.show', $product->category) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </div>
</nav>

<!-- Product Details -->
<section class="product-section py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Product Image -->
            <div class="col-lg-6">
                <div class="product-image-container">
                    <div class="main-image-wrapper position-relative">
                        <img src="{{ $product->image_url }}"
                             alt="{{ $product->name }}"
                             class="img-fluid rounded shadow-lg main-product-image"
                             id="mainProductImage">

                        <!-- Badges -->
                        <div class="position-absolute top-0 start-0 m-3">
                            @if($product->is_featured)
                                <span class="badge bg-warning text-dark mb-2 d-block">Featured</span>
                            @endif
                            <span class="badge bg-dark">{{ $product->karat }} Gold</span>
                        </div>

                        <!-- Zoom Icon -->
                        <div class="position-absolute top-0 end-0 m-3">
                            <button class="btn btn-light btn-sm rounded-circle" onclick="zoomImage()">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Image Gallery (placeholder for multiple images) -->
                    <div class="image-gallery mt-3">
                        <div class="row g-2">
                            <div class="col-3">
                                <img src="{{ $product->image_url }}"
                                     class="img-fluid rounded gallery-thumb active"
                                     onclick="changeMainImage(this.src)">
                            </div>
                            <!-- Add more thumbnail images here when available -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="col-lg-6">
                <div class="product-info">
                    <!-- Category -->
                    <div class="mb-2">
                        <a href="{{ route('categories.show', $product->category) }}"
                           class="text-decoration-none">
                            <span class="badge bg-primary">{{ $product->category->name }}</span>
                        </a>
                    </div>

                    <!-- Product Name -->
                    <h1 class="product-title fw-bold mb-3">{{ $product->name }}</h1>

                    <!-- SKU -->
                    @if($product->sku)
                        <p class="text-muted mb-3">
                            <strong>SKU:</strong> {{ $product->sku }}
                        </p>
                    @endif

                    <!-- Description -->
                    @if($product->description)
                        <div class="product-description mb-4">
                            <p class="text-muted">{{ $product->description }}</p>
                        </div>
                    @endif

                    <!-- Product Specifications -->
                    <div class="product-specs bg-light rounded p-3 mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Product Specifications
                        </h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="spec-item">
                                    <small class="text-muted d-block">Gold Karat</small>
                                    <strong>{{ $product->karat }} ({{ number_format($product->getKaratPurity($product->karat) * 100, 1) }}% Pure)</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <small class="text-muted d-block">Weight Range</small>
                                    <strong>{{ number_format($product->weight_min ?? 1, 1) }}g - {{ number_format($product->weight_max ?? 10, 1) }}g</strong>
                                </div>
                            </div>
                            @if(isset($product->labor_cost))
                                <div class="col-6">
                                    <div class="spec-item">
                                        <small class="text-muted d-block">Labor Cost</small>
                                        <strong>${{ number_format($product->labor_cost, 2) }}/g</strong>
                                    </div>
                                </div>
                            @endif
                            @if(isset($product->profit_margin))
                                <div class="col-6">
                                    <div class="spec-item">
                                        <small class="text-muted d-block">Markup</small>
                                        <strong>{{ number_format($product->profit_margin, 1) }}%</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Features -->
                    @if($product->features && is_array($product->features) && count($product->features) > 0)
                        <div class="product-features mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-star me-2 text-warning"></i>Key Features
                            </h6>
                            <ul class="list-unstyled">
                                @foreach($product->features as $feature)
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Weight Selector & Pricing -->
                    <div class="weight-price-section border rounded p-4 mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-balance-scale me-2 text-primary"></i>Customize Your Order
                        </h6>

                        <!-- Weight Selector -->
                        <div class="mb-3">
                            <label for="weightSelector" class="form-label">Select Weight (grams)</label>
                            <div class="row g-2">
                                <div class="col-8">
                                    <input type="range"
                                           class="form-range"
                                           id="weightRange"
                                           min="{{ $product->weight_min ?? 1 }}"
                                           max="{{ $product->weight_max ?? 10 }}"
                                           step="0.1"
                                           value="{{ $weight ?? $product->weight_min ?? 1 }}"
                                           oninput="updateWeight(this.value)">
                                </div>
                                <div class="col-4">
                                    <input type="number"
                                           class="form-control"
                                           id="weightInput"
                                           min="{{ $product->weight_min ?? 1 }}"
                                           max="{{ $product->weight_max ?? 10 }}"
                                           step="0.1"
                                           value="{{ $weight ?? $product->weight_min ?? 1 }}"
                                           onchange="updateWeight(this.value)">
                                </div>
                            </div>
                        </div>

                        <!-- Live Price Display -->
                        <div class="price-display bg-primary bg-opacity-10 rounded p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="mb-1 text-primary fw-bold" id="finalPrice">
                                        ${{ number_format($priceBreakdown['final_price'] ?? 100, 2) }}
                                    </h5>
                                    <small class="text-muted">Price for <span id="selectedWeight">{{ $weight ?? $product->weight_min ?? 1 }}</span>g</small>
                                </div>
                                <div class="col-4 text-end">
                                    <button class="btn btn-outline-primary btn-sm" onclick="togglePriceBreakdown()">
                                        <i class="fas fa-calculator me-1"></i>Breakdown
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown (Hidden by default) -->
                        <div class="price-breakdown collapse" id="priceBreakdownDetails">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-bold mb-3">Price Calculation</h6>
                                <div class="breakdown-item d-flex justify-content-between">
                                    <span>Gold Cost (<span id="breakdownWeight">{{ $weight ?? 1 }}</span>g × $<span id="goldPricePerGram">{{ number_format($priceBreakdown['gold_price_per_gram'] ?? 60, 2) }}</span> × {{ number_format($priceBreakdown['purity_percentage'] ?? 58.3, 1) }}%):</span>
                                    <span id="goldCost">${{ number_format($priceBreakdown['gold_cost'] ?? 50, 2) }}</span>
                                </div>
                                <div class="breakdown-item d-flex justify-content-between">
                                    <span>Labor Cost:</span>
                                    <span id="laborCost">${{ number_format($priceBreakdown['labor_cost'] ?? 10, 2) }}</span>
                                </div>
                                <div class="breakdown-item d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">${{ number_format($priceBreakdown['subtotal'] ?? 60, 2) }}</span>
                                </div>
                                <div class="breakdown-item d-flex justify-content-between">
                                    <span>Profit Margin ({{ $priceBreakdown['profit_margin'] ?? 20 }}%):</span>
                                    <span id="profitAmount">${{ number_format($priceBreakdown['profit_amount'] ?? 12, 2) }}</span>
                                </div>
                                <hr>
                                <div class="breakdown-item d-flex justify-content-between fw-bold">
                                    <span>Total Price:</span>
                                    <span id="breakdownTotal">${{ number_format($priceBreakdown['final_price'] ?? 72, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Add to Cart -->
                        <div class="cart-actions mt-3">
                            <div class="row g-2">
                                <div class="col-8">
                                    <button class="btn btn-primary btn-lg w-100" onclick="addToCart()">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-danger btn-lg w-100" onclick="addToWishlist()">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Gold Price Info -->
                    <div class="gold-price-info bg-warning bg-opacity-10 rounded p-3 mb-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-chart-line me-2 text-warning"></i>Live Gold Price
                        </h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $product->karat }} Gold (per gram):</span>
                            <span class="fw-bold text-warning">${{ number_format($goldPrices[$product->karat] ?? 85.50, 2) }}</span>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-sync-alt me-1"></i>Updated every 5 minutes
                        </small>
                    </div>

                    <!-- Share & Actions -->
                    <div class="product-actions d-flex gap-3 mb-4">
                        <button class="btn btn-outline-secondary" onclick="shareProduct()">
                            <i class="fas fa-share-alt me-2"></i>Share
                        </button>
                        <button class="btn btn-outline-info" onclick="compareProduct()">
                            <i class="fas fa-balance-scale me-2"></i>Compare
                        </button>
                        <button class="btn btn-outline-success" onclick="askQuestion()">
                            <i class="fas fa-question-circle me-2"></i>Ask Question
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
@if($relatedProducts->count() > 0)
<section class="related-products-section py-5">
    <div class="container">
        <h4 class="fw-bold mb-4">You May Also Like</h4>
        <div class="row g-4">
            @foreach($relatedProducts as $relatedProduct)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="position-relative">
                            <img src="{{ $relatedProduct->image_url }}"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;"
                                 alt="{{ $relatedProduct->name }}">
                            @if($relatedProduct->is_featured)
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">Featured</span>
                            @endif
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2">{{ $relatedProduct->karat }}</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $relatedProduct->name }}</h6>
                            <p class="text-muted small mb-2">{{ $relatedProduct->category->name }}</p>
                            <div class="mt-auto">
                                <p class="fw-bold text-primary mb-3">
                                    From ${{ number_format($relatedProduct->calculatePrice($relatedProduct->weight_min ?? 1), 2) }}
                                </p>
                                <a href="{{ route('products.show', $relatedProduct->slug ?? $relatedProduct->id) }}"
                                   class="btn btn-primary btn-sm w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Product Details Tabs -->
<section class="product-tabs-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <ul class="nav nav-tabs nav-justified" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab"
                                data-bs-target="#details" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="care-tab" data-bs-toggle="tab"
                                data-bs-target="#care" type="button" role="tab">
                            <i class="fas fa-heart me-2"></i>Care Instructions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="shipping-tab" data-bs-toggle="tab"
                                data-bs-target="#shipping" type="button" role="tab">
                            <i class="fas fa-shipping-fast me-2"></i>Shipping
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="warranty-tab" data-bs-toggle="tab"
                                data-bs-target="#warranty" type="button" role="tab">
                            <i class="fas fa-shield-alt me-2"></i>Warranty
                        </button>
                    </li>
                </ul>
                <div class="tab-content bg-white rounded-bottom p-4" id="productTabsContent">
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <h6 class="fw-bold mb-3">Product Details</h6>
                        <p>{{ $product->description ?: 'This exquisite piece showcases the finest craftsmanship and attention to detail. Made from premium gold, it represents the perfect blend of traditional techniques and contemporary design.' }}</p>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <strong>Material:</strong> {{ $product->karat }} Gold ({{ number_format($product->getKaratPurity($product->karat) * 100, 1) }}% pure)
                            </div>
                            <div class="col-md-6">
                                <strong>Weight Range:</strong> {{ number_format($product->weight_min ?? 1, 1) }}g - {{ number_format($product->weight_max ?? 10, 1) }}g
                            </div>
                            <div class="col-md-6">
                                <strong>Category:</strong> {{ $product->category->name }}
                            </div>
                            @if($product->sku)
                                <div class="col-md-6">
                                    <strong>SKU:</strong> {{ $product->sku }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade" id="care" role="tabpanel">
                        <h6 class="fw-bold mb-3">Care Instructions</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Clean gently with a soft cloth and mild soap</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Store in a dry, cool place away from direct sunlight</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Avoid contact with chemicals, perfumes, and lotions</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Remove before swimming, showering, or exercising</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Have professionally cleaned annually for optimal maintenance</li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="shipping" role="tabpanel">
                        <h6 class="fw-bold mb-3">Shipping Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="shipping-option border rounded p-3">
                                    <h6 class="fw-bold">Standard Delivery</h6>
                                    <p class="mb-1">5-7 business days</p>
                                    <p class="text-primary fw-bold">FREE for orders over $500</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="shipping-option border rounded p-3">
                                    <h6 class="fw-bold">Express Delivery</h6>
                                    <p class="mb-1">2-3 business days</p>
                                    <p class="text-primary fw-bold">$25 AUD</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                All jewelry is shipped with insurance and signature confirmation for your security.
                            </small>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="warranty" role="tabpanel">
                        <h6 class="fw-bold mb-3">Warranty & Returns</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">Lifetime Warranty</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Manufacturing defects</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Craftsmanship issues</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Free repairs and maintenance</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">30-Day Returns</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Full refund if not satisfied</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Original condition required</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Return shipping included</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Image Zoom Modal -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $product->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ $product->image_url }}" class="img-fluid" alt="{{ $product->name }}">
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.main-product-image {
    max-height: 500px;
    width: 100%;
    object-fit: cover;
}

.gallery-thumb {
    height: 80px;
    object-fit: cover;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s;
}

.gallery-thumb:hover,
.gallery-thumb.active {
    opacity: 1;
}

.product-specs,
.weight-price-section,
.gold-price-info {
    border: 1px solid #e9ecef;
}

.spec-item {
    padding: 0.5rem 0;
}

.price-display {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
}

.breakdown-item {
    padding: 0.25rem 0;
    font-size: 0.9rem;
}

.form-range::-moz-range-thumb {
    background: #0d6efd;
    border: none;
}

.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

.nav-tabs .nav-link.active {
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.shipping-option {
    transition: all 0.3s ease;
}

.shipping-option:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

@media (max-width: 768px) {
    .main-product-image {
        max-height: 300px;
    }

    .product-title {
        font-size: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Product data
    const product = {
        id: {{ $product->id }},
        minWeight: {{ $product->weight_min ?? 1 }},
        maxWeight: {{ $product->weight_max ?? 10 }},
        weightStep: 0.1,
        karatPurity: {{ $product->getKaratPurity($product->karat) }},
        laborCost: {{ $product->labor_cost ?? 10 }},
        profitMargin: {{ $product->profit_margin ?? 20 }}
    };

    const goldPricePerGram = {{ $goldPrices[$product->karat] ?? 85.50 }};

    // Update weight and price
    window.updateWeight = function(weight) {
        weight = Math.max(product.minWeight, Math.min(product.maxWeight, parseFloat(weight)));

        // Update both inputs
        document.getElementById('weightRange').value = weight;
        document.getElementById('weightInput').value = weight;
        document.getElementById('selectedWeight').textContent = weight;

        // Calculate new price locally for immediate feedback
        const goldCost = weight * (goldPricePerGram / 31.1035) * product.karatPurity;
        const laborCost = weight * product.laborCost;
        const subtotal = goldCost + laborCost;
        const profitAmount = subtotal * (product.profitMargin / 100);
        const finalPrice = subtotal + profitAmount;

        // Update price display immediately
        document.getElementById('finalPrice').textContent = '$' + finalPrice.toFixed(2);
        document.getElementById('breakdownWeight').textContent = weight;
        document.getElementById('goldCost').textContent = '$' + goldCost.toFixed(2);
        document.getElementById('laborCost').textContent = '$' + laborCost.toFixed(2);
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('profitAmount').textContent = '$' + profitAmount.toFixed(2);
        document.getElementById('breakdownTotal').textContent = '$' + finalPrice.toFixed(2);

        // Also make API call for server-side calculation (optional)
        fetch(`/products/{{ $product->id }}/calculate-price`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ weight: weight })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Update with server calculation if different
                const serverPrice = parseFloat(data.data.subtotal);
                if (Math.abs(serverPrice - finalPrice) > 0.01) {
                    document.getElementById('finalPrice').textContent = '$' + serverPrice.toFixed(2);
                    document.getElementById('breakdownTotal').textContent = '$' + serverPrice.toFixed(2);
                }
            }
        })
        .catch(error => {
            console.log('Price calculation using local fallback');
        });
    };

    // Toggle price breakdown
    window.togglePriceBreakdown = function() {
        const breakdown = document.getElementById('priceBreakdownDetails');
        const collapse = new bootstrap.Collapse(breakdown);
        collapse.toggle();
    };

    // Change main image
    window.changeMainImage = function(src) {
        document.getElementById('mainProductImage').src = src;

        // Update active thumbnail
        document.querySelectorAll('.gallery-thumb').forEach(thumb => {
            thumb.classList.remove('active');
        });
        event.target.classList.add('active');
    };

    // Zoom image
    window.zoomImage = function() {
        const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
        modal.show();
    };

    // Add to cart
    window.addToCart = function() {
        const weight = parseFloat(document.getElementById('weightInput').value);

        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: product.id,
                weight: weight
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in header
                if (window.updateCartCount) {
                    window.updateCartCount(data.cart_count);
                }

                // Show success message
                showAlert('success', data.message);

                // Optional: Show cart preview or redirect
                const userChoice = confirm('Product added to cart! Would you like to view your cart?');
                if (userChoice) {
                    window.location.href = '/cart';
                }
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to add product to cart');
        });
    };

    // Add to wishlist
    window.addToWishlist = function() {
        showAlert('info', 'Wishlist functionality coming soon!');
    };

    // Share product
    window.shareProduct = function() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $product->name }}',
                text: 'Check out this beautiful jewelry piece!',
                url: window.location.href
            });
        } else {
            // Fallback - copy URL to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                showAlert('success', 'Product URL copied to clipboard!');
            }).catch(() => {
                showAlert('info', 'Please copy the URL manually: ' + window.location.href);
            });
        }
    };

    // Compare product
    window.compareProduct = function() {
        showAlert('info', 'Product comparison feature coming soon!');
    };

    // Ask question
    window.askQuestion = function() {
        showAlert('info', 'Contact form coming soon!');
    };

    // Show alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' :
                          type === 'warning' ? 'alert-warning' : 'alert-info';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 1050; min-width: 300px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        document.querySelectorAll('.alert.position-fixed').forEach(alert => alert.remove());

        // Add new alert
        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector(`.${alertClass}.position-fixed`);
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // Initialize with default weight calculation
    updateWeight({{ $weight ?? $product->weight_min ?? 1 }});
});
</script>
@endpush
