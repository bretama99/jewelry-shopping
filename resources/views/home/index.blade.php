@extends('layouts.admin')

@section('title', 'Gold Jewelry - Exquisite Collection')

@section('content')
@php
    // Define beautiful jewelry images from Unsplash
    $categoryImages = [
        'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Gold rings
        'https://images.unsplash.com/photo-1605100804763-247f67b3557e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Gold necklace
        'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Gold bracelet
        'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Gold earrings
        'https://images.unsplash.com/photo-1611955167811-4711904bb9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Luxury jewelry
        'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Diamond jewelry
        'https://images.unsplash.com/photo-1611652022419-a9419f74343d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Wedding rings
        'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', // Jewelry
    ];

    $featuredImages = [
        'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured ring
        'https://images.unsplash.com/photo-1611652022419-a9419f74343d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured necklace
        'https://images.unsplash.com/photo-1605100804763-247f67b3557e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured bracelet
        'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured earrings
        'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured watch
        'https://images.unsplash.com/photo-1611955167811-4711904bb9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured luxury
        'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured diamond
        'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Featured premium
    ];

    $latestImages = [
        'https://images.unsplash.com/photo-1603561596112-db1d72e9c2d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 1
        'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 2
        'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 3
        'https://images.unsplash.com/photo-1611652022419-a9419f74343d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 4
        'https://images.unsplash.com/photo-1605100804763-247f67b3557e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 5
        'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 6
        'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 7
        'https://images.unsplash.com/photo-1611955167811-4711904bb9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', // Latest design 8
    ];
@endphp

<!-- Hero Section with Parallax Effect -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>

    <div class="container position-relative z-3">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content" data-aos="fade-right" data-aos-duration="1000">
                    <span class="badge bg-warning text-dark mb-3 px-3 py-2 fs-6 rounded-pill">
                        <i class="fas fa-star me-2"></i>Collection
                    </span>

                    <h1 class="display-2 fw-bold mb-4 text-white">
                        Exquisite
                        <span class="text-gradient-gold">Gold Jewelry</span>
                        <br>
                        <span class="subtitle">Crafted to Perfection</span>
                    </h1>

                    <p class="lead mb-5 text-light fs-5">
                        Discover our premium collection of handcrafted gold jewelry with live pricing.
                        Each piece is meticulously crafted using the finest materials and traditional techniques
                        passed down through generations.
                    </p>

                    <!-- Enhanced Stats with Icons -->
                    <div class="stats-container row g-3 mb-5">
                        <div class="col-6 col-md-3">
                            <div class="stat-item text-center p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur">
                                <div class="stat-icon text-warning mb-2">
                                    <i class="fas fa-gem fa-2x"></i>
                                </div>
                                <h3 class="h3 fw-bold mb-1 text-white counter" data-target="{{ $stats['total_products'] }}">0</h3>
                                <small class="text-light">Products</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item text-center p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur">
                                <div class="stat-icon text-warning mb-2">
                                    <i class="fas fa-layer-group fa-2x"></i>
                                </div>
                                <h3 class="h3 fw-bold mb-1 text-white counter" data-target="{{ $stats['total_categories'] }}">0</h3>
                                <small class="text-light">Categories</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item text-center p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur">
                                <div class="stat-icon text-warning mb-2">
                                    <i class="fas fa-medal fa-2x"></i>
                                </div>
                                <h3 class="h3 fw-bold mb-1 text-white counter" data-target="{{ $stats['karat_varieties'] }}">0</h3>
                                <small class="text-light">Karat Options</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item text-center p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur">
                                <div class="stat-icon text-warning mb-2">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                                <h3 class="h3 fw-bold mb-1 text-white">${{ number_format($stats['price_range']['min']) }}+</h3>
                                <small class="text-light">Starting From</small>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced CTA Buttons -->
                    <div class="hero-buttons d-flex flex-wrap gap-3">
                        <a href="{{ route('categories.index') }}" class="btn btn-warning btn-lg px-5 py-3 rounded-pill shadow-lg hover-lift">
                            <i class="fas fa-shopping-bag me-2"></i>Shop Now
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill hover-lift">
                            <i class="fas fa-eye me-2"></i>Browse Collection
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-visual text-center position-relative" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                    <!-- Floating Elements -->
                    <div class="floating-elements">
                        <div class="floating-gem floating-gem-1">
                            <i class="fas fa-gem fa-3x text-warning"></i>
                        </div>
                        <div class="floating-gem floating-gem-2">
                            <i class="fas fa-ring fa-2x text-warning"></i>
                        </div>
                        <div class="floating-gem floating-gem-3">
                            <i class="fas fa-crown fa-2x text-warning"></i>
                        </div>
                    </div>

                    <!-- Main Hero Image -->
                    <div class="hero-image-main position-relative">
                        <div class="gold-shine"></div>
                        <i class="fas fa-gem fa-15x text-warning main-gem"></i>
                    </div>

                    <!-- Enhanced Live Price Display -->
                    <div class="live-price-card position-absolute top-0 end-0 m-4" data-aos="zoom-in" data-aos-delay="600">
                        <div class="card border-0 shadow-lg bg-white rounded-4 overflow-hidden">
                            <div class="card-header bg-gradient-primary text-white p-3 border-0">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-chart-line me-2"></i>Live Gold Prices
                                </h6>
                                <small class="opacity-75">Updated every 10 minutes</small>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    @foreach(array_slice($goldPrices, 0, 3, true) as $karat => $price)
                                        <div class="col-4">
                                            <div class="price-item text-center p-2 rounded-3 bg-light">
                                                <div class="fw-bold text-primary">{{ $karat }}K</div>
                                                <div class="small text-success fw-bold">${{ number_format($price, 0) }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-center mt-3">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fas fa-sync fa-spin"></i> Live
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-line"></div>
        <div class="scroll-text">Scroll</div>
    </div>
</section>

<!-- Popular Categories with Enhanced Design -->
@if($popularCategories->count() > 0)
<section class="categories-section py-6 bg-light">
    <div class="container">
        <div class="section-header text-center mb-6" data-aos="fade-up">
            <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill">
                <i class="fas fa-fire me-2"></i>Popular
            </span>
            <h2 class="display-4 fw-bold text-dark mb-3">Trending Categories</h2>
            <p class="lead text-muted max-width-600 mx-auto">
                Explore our most loved jewelry collections, each piece telling a unique story of elegance and craftsmanship
            </p>
        </div>

        <div class="row g-4">
            @foreach($popularCategories as $index => $category)
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="category-card-enhanced card h-100 border-0 shadow-hover rounded-4 overflow-hidden">
                        <div class="card-image-container position-relative">
                            <img src="{{ $categoryImages[$loop->index % count($categoryImages)] }}"
                                 alt="{{ $category->name }}"
                                 class="card-img-top category-image"
                                 style="height: 280px; object-fit: cover;"
                                 loading="lazy"
                                 onerror="this.src='{{ $categoryImages[0] }}'">
                            <div class="image-overlay"></div>
                            <div class="image-content position-absolute bottom-0 start-0 p-4 text-white">
                                <span class="badge bg-warning text-dark mb-2 rounded-pill">
                                    {{ $category->active_products_count }} Items
                                </span>
                                <h5 class="fw-bold mb-2">{{ $category->name }}</h5>
                                @if($category->description)
                                    <p class="small mb-0 opacity-75">
                                        {{ Str::limit($category->description, 60) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <a href="{{ route('categories.show', $category) }}"
                               class="btn btn-primary w-100 rounded-pill hover-lift">
                                <i class="fas fa-arrow-right me-2"></i>Explore Collection
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="600">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-primary btn-lg px-5 py-3 rounded-pill hover-lift">
                <i class="fas fa-th-large me-2"></i>View All Categories
            </a>
        </div>
    </div>
</section>
@endif

<!-- Featured Products with Design -->
@if($featuredProducts->count() > 0)
<section class="featured-products-section py-6 bg-dark text-white position-relative overflow-hidden">
    <div class="bg-pattern"></div>
    <div class="container position-relative">
        <div class="section-header text-center mb-6" data-aos="fade-up">
            <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">
                <i class="fas fa-star me-2"></i>Featured
            </span>
            <h2 class="display-4 fw-bold mb-3">Masterpiece Collection</h2>
            <p class="lead text-light max-width-600 mx-auto">
                Handpicked pieces that showcase exceptional craftsmanship and timeless elegance
            </p>
        </div>

        <div class="row g-4">
            @foreach($featuredProducts as $index => $product)
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                    <div class="product-card-premium card h-100 bg-white border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="product-image-container position-relative">
                            <img src="{{ $featuredImages[$loop->index % count($featuredImages)] }}"
                                 alt="{{ $product->name }}"
                                 class="card-img-top"
                                 style="height: 220px; object-fit: cover;"
                                 loading="lazy"
                                 onerror="this.src='{{ $featuredImages[0] }}'">
                            <div class="product-badges">
                                <span class="badge bg-warning text-dark">Featured</span>
                                <span class="badge bg-dark">{{ $product->karat }}K</span>
                            </div>
                            <div class="product-overlay">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-light rounded-circle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h6 class="card-title fw-bold mb-2 text-dark">{{ $product->name }}</h6>
                            <p class="small text-muted mb-3">{{ $product->category->name }}</p>

                            <div class="product-details bg-light rounded-3 p-3 mb-3">
                                <div class="row g-2 text-center">
                                    <div class="col-6">
                                        <div class="detail-item">
                                            <i class="fas fa-weight text-primary mb-1"></i>
                                            <div class="small text-muted">Weight</div>
                                            <div class="fw-bold">{{ $product->base_weight }}g</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-item">
                                            <i class="fas fa-tag text-success mb-1"></i>
                                            <div class="small text-muted">Price</div>
                                            <div class="fw-bold text-primary">
                                                ${{ number_format($product->calculatePrice($product->base_weight), 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <a href="{{ route('products.show', $product) }}"
                                   class="btn btn-primary w-100 rounded-pill hover-lift">
                                    <i class="fas fa-shopping-cart me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="800">
            <a href="{{ route('products.index') }}" class="btn btn-warning btn-lg px-5 py-3 rounded-pill hover-lift">
                <i class="fas fa-box me-2"></i>View All Products
            </a>
        </div>
    </div>
</section>
@endif

<!-- Latest Products -->
@if($latestProducts->count() > 0)
<section class="latest-products-section py-6 bg-gradient-light">
    <div class="container">
        <div class="section-header text-center mb-6" data-aos="fade-up">
            <span class="badge bg-success mb-3 px-3 py-2 rounded-pill">
                <i class="fas fa-sparkles me-2"></i>New Arrivals
            </span>
            <h2 class="display-4 fw-bold text-dark mb-3">Latest Collection</h2>
            <p class="lead text-muted max-width-600 mx-auto">
                Discover our newest additions, featuring contemporary designs and timeless classics
            </p>
        </div>

        <div class="row g-4">
            @foreach($latestProducts as $index => $product)
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="product-card-modern card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift">
                        <div class="product-image-wrapper position-relative">
                            <img src="{{ $latestImages[$loop->index % count($latestImages)] }}"
                                 alt="{{ $product->name }}"
                                 class="card-img-top"
                                 style="height: 220px; object-fit: cover;"
                                 loading="lazy"
                                 onerror="this.src='{{ $latestImages[0] }}'">
                            <div class="new-badge">
                                <span class="badge bg-success">New</span>
                            </div>
                            <div class="karat-badge">
                                <span class="badge bg-dark">{{ $product->karat }}K</span>
                            </div>
                        </div>

                        <div class="card-body p-4 bg-white">
                            <h6 class="card-title fw-bold mb-2">{{ $product->name }}</h6>
                            <p class="small text-muted mb-3">{{ $product->category->name }}</p>

                            <div class="price-display bg-light rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-muted">Base Weight:</span>
                                    <span class="fw-bold">{{ $product->base_weight }}g</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">Starting at:</span>
                                    <span class="fw-bold text-primary h6 mb-0">
                                        ${{ number_format($product->calculatePrice($product->base_weight), 2) }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('products.show', $product) }}"
                               class="btn btn-outline-primary w-100 rounded-pill">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Enhanced Features Section -->
<section class="features-section py-6 bg-dark text-white position-relative">
    <div class="features-bg"></div>
    <div class="container position-relative">
        <div class="section-header text-center mb-6" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Why Choose Us</h2>
            <p class="lead text-light">Experience the difference with our premium services</p>
        </div>

        <div class="row g-5">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-item-enhanced text-center">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-warning text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="fas fa-certificate fa-2x"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <h5 class="fw-bold text-warning mb-3">Certified Quality</h5>
                    <p class="text-light mb-4">All jewelry is certified with live gold pricing for complete transparency and authenticity</p>
                    <div class="feature-accent"></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-item-enhanced text-center">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-warning text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="fas fa-shipping-fast fa-2x"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <h5 class="fw-bold text-warning mb-3">Express Delivery</h5>
                    <p class="text-light mb-4">Fast and secure shipping across Australia with premium packaging and insurance</p>
                    <div class="feature-accent"></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-item-enhanced text-center">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-warning text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <h5 class="fw-bold text-warning mb-3">Lifetime Warranty</h5>
                    <p class="text-light mb-4">Comprehensive warranty covering craftsmanship and materials for your peace of mind</p>
                    <div class="feature-accent"></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-item-enhanced text-center">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-warning text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="fas fa-headset fa-2x"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <h5 class="fw-bold text-warning mb-3">Expert Support</h5>
                    <p class="text-light mb-4">Professional consultation and 24/7 customer service from jewelry experts</p>
                    <div class="feature-accent"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced Call to Action -->
<section class="cta-section py-6 bg-gradient-gold text-white position-relative overflow-hidden">
    <div class="cta-particles"></div>
    <div class="container text-center position-relative">
        <div data-aos="zoom-in" data-aos-duration="1000">
            <span class="badge bg-white text-dark mb-3 px-3 py-2 rounded-pill">
                <i class="fas fa-crown me-2"></i>Experience
            </span>
            <h2 class="display-4 fw-bold mb-4">Ready to Find Your Perfect Piece?</h2>
            <p class="lead mb-5 max-width-600 mx-auto">
                Browse our exquisite collection and discover jewelry that tells your unique story.
                Each piece is crafted with love and attention to detail.
            </p>
            <div class="cta-buttons d-flex justify-content-center gap-4 flex-wrap">
                <a href="{{ route('categories.index') }}" class="btn btn-white btn-lg px-5 py-3 rounded-pill shadow-lg hover-lift">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
                <a href="#" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill hover-lift">
                    <i class="fas fa-phone me-2"></i>Contact Expert
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
/* Enhanced Color Scheme */
:root {
    --gold-primary: #FFD700;
    --gold-secondary: #FFA500;
    --gold-dark: #B8860B;
    --dark-primary: #1a1a1a;
    --dark-secondary: #2d2d2d;
}

/* Utility Classes */
.py-6 { padding-top: 4rem; padding-bottom: 4rem; }
.mb-6 { margin-bottom: 4rem; }
.max-width-600 { max-width: 600px; }
.backdrop-blur { backdrop-filter: blur(10px); }
.hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.shadow-hover { transition: box-shadow 0.3s ease; }
.shadow-hover:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important; }

/* Hero Section Enhanced */
.hero-section {
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 1;
}

.hero-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    animation: float 20s ease-in-out infinite;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    z-index: 2;
}

.text-gradient-gold {
    background: linear-gradient(45deg, #FFD700, #FFA500, #FF8C00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    font-size: 0.6em;
    opacity: 0.9;
    font-weight: 400;
}

/* Floating Elements */
.floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.floating-gem {
    position: absolute;
    animation: float 6s ease-in-out infinite;
}

.floating-gem-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.floating-gem-2 {
    top: 60%;
    right: 20%;
    animation-delay: 2s;
}

.floating-gem-3 {
    bottom: 30%;
    left: 20%;
    animation-delay: 4s;
}

.main-gem {
    position: relative;
    z-index: 1;
    filter: drop-shadow(0 0 30px rgba(255, 215, 0, 0.5));
}

.gold-shine {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,215,0,0.3) 0%, transparent 70%);
    border-radius: 50%;
    animation: pulse 3s ease-in-out infinite;
}

/* Live Price Card Enhanced */
.live-price-card {
    animation: slideInRight 1s ease-out;
    z-index: 10;
}

.live-price-card .card {
    min-width: 200px;
    border: 1px solid rgba(255,255,255,0.2);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Scroll Indicator */
.scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: white;
    z-index: 3;
    animation: bounce 2s infinite;
}

.scroll-line {
    width: 2px;
    height: 30px;
    background: linear-gradient(to bottom, transparent, #FFD700);
    margin: 0 auto 10px;
}

.scroll-text {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Category Cards Enhanced */
.category-card-enhanced {
    transition: all 0.3s ease;
    overflow: hidden;
}

.category-card-enhanced:hover {
    transform: translateY(-10px);
}

.card-image-container {
    overflow: hidden;
    position: relative;
}

.category-image {
    transition: transform 0.5s ease;
}

.category-card-enhanced:hover .category-image {
    transform: scale(1.1);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
}

/* Product Cards */
.product-card-premium {
    transition: all 0.3s ease;
}

.product-card-premium:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.2) !important;
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    display: flex;
    justify-content: space-between;
    z-index: 2;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card-premium:hover .product-overlay {
    opacity: 1;
}

.detail-item i {
    font-size: 1.2em;
}

/* Modern Product Cards */
.product-card-modern {
    transition: all 0.3s ease;
}

.product-card-modern:hover {
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.new-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 2;
}

.karat-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
}

/* Features Section Enhanced */
.features-section {
    position: relative;
}

.features-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    opacity: 0.9;
}

.features-bg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(circle at 25% 25%, rgba(255,215,0,0.1) 0%, transparent 50%),
                      radial-gradient(circle at 75% 75%, rgba(255,165,0,0.1) 0%, transparent 50%);
}

.feature-item-enhanced {
    position: relative;
    padding: 2rem;
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.feature-item-enhanced:hover {
    transform: translateY(-10px);
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
}

.feature-icon-wrapper {
    position: relative;
}

.feature-icon {
    width: 80px;
    height: 80px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.icon-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100px;
    height: 100px;
    background: radial-gradient(circle, rgba(255,215,0,0.3) 0%, transparent 70%);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.feature-item-enhanced:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.feature-item-enhanced:hover .icon-glow {
    opacity: 1;
}

.feature-accent {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: linear-gradient(to right, #FFD700, #FFA500);
    border-radius: 2px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.feature-item-enhanced:hover .feature-accent {
    opacity: 1;
}

/* CTA Section Enhanced */
.bg-gradient-gold {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
}

.cta-particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 20% 20%, rgba(255,255,255,0.1) 1px, transparent 1px),
        radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 1px, transparent 1px),
        radial-gradient(circle at 40% 60%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 50px 50px, 80px 80px, 120px 120px;
    animation: particles 20s linear infinite;
}

.btn-white {
    background: white;
    color: #333;
    border: none;
}

.btn-white:hover {
    background: #f8f9fa;
    color: #333;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* Background Gradients */
.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.7; }
    50% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

@keyframes slideInRight {
    0% { transform: translateX(100%); opacity: 0; }
    100% { transform: translateX(0); opacity: 1; }
}

@keyframes particles {
    0% { transform: translateY(0px); }
    100% { transform: translateY(-100px); }
}

/* Counter Animation */
.counter {
    transition: all 0.3s ease;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        min-height: 80vh;
        padding: 2rem 0;
    }

    .display-2 {
        font-size: 2.5rem;
    }

    .py-6 {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }

    .live-price-card {
        position: relative !important;
        top: auto !important;
        right: auto !important;
        margin: 2rem auto 0 !important;
        max-width: 300px;
    }

    .floating-elements {
        display: none;
    }

    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }

    .cta-buttons .btn {
        width: 100%;
        max-width: 300px;
    }
}

@media (max-width: 576px) {
    .stats-container .col-6 {
        margin-bottom: 1rem;
    }

    .hero-buttons {
        flex-direction: column;
        align-items: stretch;
    }

    .hero-buttons .btn {
        margin-bottom: 1rem;
    }
}

/* Loading Animation for AOS */
[data-aos] {
    pointer-events: none;
}

[data-aos].aos-animate {
    pointer-events: auto;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #FFD700, #FFA500);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #FFA500, #FF8C00);
}
</style>
@endpush

@push('scripts')
<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });

    // Counter Animation
    const animateCounters = () => {
        const counters = document.querySelectorAll('.counter');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.dataset.target);
                    animateNumber(entry.target, 0, target, 2000);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    };

    const animateNumber = (element, start, end, duration) => {
        const range = end - start;
        const startTime = performance.now();
        const easeOutQuart = t => 1 - (--t) * t * t * t;

        const step = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOutQuart(progress);
            const value = Math.floor(start + (range * easedProgress));

            element.textContent = value;

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    };

    // Initialize counter animation
    animateCounters();

    // Live Price Update Animation
    const updatePrices = () => {
        const priceElements = document.querySelectorAll('.live-price-card .text-success');
        priceElements.forEach(el => {
            el.style.animation = 'pulse 0.5s ease-in-out';
            setTimeout(() => {
                el.style.animation = '';
            }, 500);
        });
    };

    // Update prices every 30 seconds
    setInterval(updatePrices, 30000);

    // Parallax Effect for Hero Background
    const parallaxElements = document.querySelectorAll('.hero-background');

    const handleParallax = () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;

        parallaxElements.forEach(element => {
            element.style.transform = `translateY(${rate}px)`;
        });
    };

    // Throttled scroll handler
    let ticking = false;
    const handleScroll = () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                handleParallax();
                ticking = false;
            });
            ticking = true;
        }
    };

    window.addEventListener('scroll', handleScroll);

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Product card hover effects
    const productCards = document.querySelectorAll('.product-card-premium, .product-card-modern');

    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Category card tilt effect
    const categoryCards = document.querySelectorAll('.category-card-enhanced');

    categoryCards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;

            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });

    // Feature icons rotation on scroll
    const featureIcons = document.querySelectorAll('.feature-icon');

    const rotateIcons = () => {
        const scrolled = window.pageYOffset;
        featureIcons.forEach((icon, index) => {
            const rotation = scrolled * 0.1 + (index * 45);
            icon.style.transform = `rotate(${rotation}deg)`;
        });
    };

    window.addEventListener('scroll', rotateIcons);

    // Loading screen fade out
    const loadingScreen = document.querySelector('.loading-screen');
    if (loadingScreen) {
        setTimeout(() => {
            loadingScreen.style.opacity = '0';
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }, 1000);
    }

    // Intersection Observer for additional animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.stats-container, .section-header, .feature-item-enhanced').forEach(el => {
        observer.observe(el);
    });

    // Add smooth reveal animation for stats
    const statsItems = document.querySelectorAll('.stat-item');
    statsItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'all 0.6s ease';
        item.style.animationDelay = `${index * 0.1}s`;

        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 500 + (index * 100));
    });

    // Price refresh functionality
    const refreshPriceBtn = document.querySelector('.btn-outline-primary .fa-sync');
    if (refreshPriceBtn) {
        refreshPriceBtn.parentElement.addEventListener('click', function(e) {
            e.preventDefault();
            refreshPriceBtn.classList.add('fa-spin');

            // Simulate API call
            setTimeout(() => {
                refreshPriceBtn.classList.remove('fa-spin');
                updatePrices();
            }, 1500);
        });
    }
});

// Add CSS animation classes
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        animation: fadeInUp 0.8s ease forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>
@endpush
