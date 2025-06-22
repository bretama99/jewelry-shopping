{{-- File: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Gold Jewelry Store') - {{ config('app.name', 'Jewelry Store') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #d4af37;
            --secondary-color: #b8860b;
            --dark-gold: #8b7355;
            --light-gold: #f5e6a3;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.show {
            left: 0;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--light-gold);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
        }

        .sidebar-header {
            padding: 1.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .content-wrapper {
            min-height: 100vh;
            padding-top: 80px;
        }

        .main-content {
            padding: 2rem 0;
        }

        .footer {
            background-color: #2d2d2d;
            color: #fff;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .gold-price-ticker {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .category-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .cart-count {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @media (min-width: 992px) {
            .sidebar {
                position: relative;
                left: 0;
                width: 250px;
            }

            .content-wrapper {
                margin-left: 0;
                padding-top: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Sidebar Toggle -->
            <button class="btn btn-outline-light me-3" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Brand -->
            <a class="navbar-brand" href="{{ route('external.home') }}">
                <i class="fas fa-gem me-2"></i>{{ config('app.name', 'Jewelry Store') }}
            </a>

            <!-- Navbar Toggle for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Side Links -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('external.home') }}">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                            <i class="fas fa-tags me-1"></i>Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            <i class="fas fa-box me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">
                            <i class="fas fa-phone me-1"></i>Contact
                        </a>
                    </li>
                </ul>

                <!-- Right Side Links -->
                <ul class="navbar-nav">
                    <!-- Search -->
                    <li class="nav-item me-2">
                        <form class="d-flex" action="{{ route('products.index') }}" method="GET">
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="search" name="search"
                                       placeholder="Search jewelry..." value="{{ request('search') }}">
                                <button class="btn btn-outline-light btn-sm" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </li>

                    <!-- Cart -->
                    <li class="nav-item me-2">
                        <a class="nav-link position-relative {{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" id="cartCount">
                                {{ \App\Models\Cart::getCartCount(session()->getId(), auth()->id()) ?? 0 }}
                            </span>
                        </a>
                    </li>

                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    @else
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i>{{ Str::limit(Auth::user()->name, 15) }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header">
                                        <i class="fas fa-user-circle me-2"></i>Account
                                    </h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#profile">
                                        <i class="fas fa-user me-2"></i>My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.orders.index') }}">
                                        <i class="fas fa-shopping-bag me-2"></i>My Orders
                                        @php
                                            $orderCount = \App\Models\Order::where('user_id', auth()->id())->where('status', 'pending')->count();
                                        @endphp
                                        @if($orderCount > 0)
                                            <span class="badge bg-primary ms-1">{{ $orderCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#wishlist">
                                        <i class="fas fa-heart me-2"></i>Wishlist
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#address-book">
                                        <i class="fas fa-address-book me-2"></i>Address Book
                                    </a>
                                </li>
                                @if(Auth::user()->is_admin ?? false)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <h6 class="dropdown-header">
                                            <i class="fas fa-cogs me-2"></i>Administration
                                        </h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.orders.index') }}">
                                            <i class="fas fa-clipboard-list me-2"></i>Manage Orders
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5 class="text-white mb-0">
                <i class="fas fa-gem me-2"></i>Navigation
            </h5>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('external.home') }}">
                <i class="fas fa-home me-2"></i>Home
            </a>
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                <i class="fas fa-tags me-2"></i>Categories
            </a>
            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="fas fa-box me-2"></i>Products
            </a>
            <a class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">
                <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                <span class="badge bg-danger ms-auto" id="sidebarCartCount">
                    {{ \App\Models\Cart::getCartCount(session()->getId(), auth()->id()) ?? 0 }}
                </span>
            </a>

            @auth
                <hr style="border-color: rgba(255,255,255,0.1);">
                <div class="px-3 py-2">
                    <small class="text-muted">MY ACCOUNT</small>
                </div>
                <a class="nav-link" href="#profile">
                    <i class="fas fa-user me-2"></i>My Profile
                </a>
                <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-shopping-bag me-2"></i>My Orders
                    @php
                        $orderCount = \App\Models\Order::where('user_id', auth()->id())->where('status', 'pending')->count();
                    @endphp
                    @if($orderCount > 0)
                        <span class="badge bg-primary ms-auto">{{ $orderCount }}</span>
                    @endif
                </a>
                <a class="nav-link" href="#wishlist">
                    <i class="fas fa-heart me-2"></i>Wishlist
                </a>
                <a class="nav-link" href="#address-book">
                    <i class="fas fa-address-book me-2"></i>Address Book
                </a>

                @if(Auth::user()->is_admin ?? false)
                    <hr style="border-color: rgba(255,255,255,0.1);">
                    <div class="px-3 py-2">
                        <small class="text-muted">ADMINISTRATION</small>
                    </div>
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                    </a>
                    <a class="nav-link" href="{{ route('admin.orders.index') }}">
                        <i class="fas fa-clipboard-list me-2"></i>Manage Orders
                    </a>
                    <a class="nav-link" href="{{ route('products.index') }}">
                        <i class="fas fa-gem me-2"></i>Manage Products
                    </a>
                    <a class="nav-link" href="{{ route('categories.index') }}">
                        <i class="fas fa-tags me-2"></i>Manage Categories
                    </a>
                @endif
            @else
                <hr style="border-color: rgba(255,255,255,0.1);">
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a class="nav-link" href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            @endauth

            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="px-3 py-2">
                <small class="text-muted">INFORMATION</small>
            </div>
            <a class="nav-link" href="#about">
                <i class="fas fa-info-circle me-2"></i>About Us
            </a>
            <a class="nav-link" href="#contact">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a class="nav-link" href="#shipping">
                <i class="fas fa-shipping-fast me-2"></i>Shipping Info
            </a>
            <a class="nav-link" href="#returns">
                <i class="fas fa-undo me-2"></i>Returns Policy
            </a>
        </nav>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Gold Price Ticker -->
    <div class="gold-price-ticker text-center" id="goldPriceTicker">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                <span><strong>Live Gold Prices:</strong></span>
                <div id="livePrices">
                    <span class="me-3">10K: $65.20/g</span>
                    <span class="me-3">14K: $85.50/g</span>
                    <span class="me-3">18K: $110.25/g</span>
                    <span class="me-3">22K: $135.80/g</span>
                    <span>24K: $147.90/g</span>
                </div>
                <small class="text-light opacity-75">Updated every 5 minutes</small>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <main class="main-content">
            <!-- Alerts -->
            @if(session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="container">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="container">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-white mb-3">{{ config('app.name') }}</h5>
                    <p class="text-light">Gold jewelry crafted with precision and passion. Each piece tells a story of elegance and sophistication.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Shop</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('categories.index') }}" class="text-light text-decoration-none">All Categories</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-light text-decoration-none">All Products</a></li>
                        <li><a href="{{ route('products.index', ['featured' => 1]) }}" class="text-light text-decoration-none">Featured Items</a></li>
                        <li><a href="{{ route('products.index', ['sort' => 'price_low']) }}" class="text-light text-decoration-none">Best Deals</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li><a href="#contact" class="text-light text-decoration-none">Contact Us</a></li>
                        <li><a href="#shipping" class="text-light text-decoration-none">Shipping Info</a></li>
                        <li><a href="#returns" class="text-light text-decoration-none">Returns & Exchanges</a></li>
                        <li><a href="#size-guide" class="text-light text-decoration-none">Size Guide</a></li>
                        <li><a href="#care-guide" class="text-light text-decoration-none">Jewelry Care</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="text-light mb-2"><i class="fas fa-map-marker-alt me-2"></i>123 Gold Street, Sydney, NSW 2000</li>
                        <li class="text-light mb-2"><i class="fas fa-phone me-2"></i>+61 2 1234 5678</li>
                        <li class="text-light mb-2"><i class="fas fa-envelope me-2"></i>info@jewelrystore.com.au</li>
                        <li class="text-light"><i class="fas fa-clock me-2"></i>Mon-Fri: 9AM-6PM, Sat: 10AM-4PM</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-light mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-light mb-0">
                        <i class="fas fa-shield-alt me-1"></i>Secure Shopping |
                        <i class="fas fa-award me-1"></i>Certified Quality |
                        <i class="fas fa-undo me-1"></i>30-Day Returns
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);

            // Update cart count globally
            window.updateCartCount = function(count) {
                document.getElementById('cartCount').textContent = count;
                document.getElementById('sidebarCartCount').textContent = count;

                // Add animation
                const cartBadges = document.querySelectorAll('#cartCount, #sidebarCartCount');
                cartBadges.forEach(badge => {
                    badge.classList.add('cart-count');
                    setTimeout(() => badge.classList.remove('cart-count'), 2000);
                });
            };

            // Auto-hide alerts
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (alert.querySelector('.btn-close')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Update live gold prices
            updateLivePrices();
            setInterval(updateLivePrices, 300000); // Update every 5 minutes

            function updateLivePrices() {
                fetch('/api/gold-prices')
                    .then(response => response.json())
                    .then(data => {
                        if (data.price) {
                            // Calculate prices for different karats
                            const basePrice = data.price / 31.1035; // Convert from per oz to per gram
                            const prices = {
                                '10K': (basePrice * 0.417).toFixed(2),
                                '14K': (basePrice * 0.583).toFixed(2),
                                '18K': (basePrice * 0.750).toFixed(2),
                                '22K': (basePrice * 0.917).toFixed(2),
                                '24K': basePrice.toFixed(2)
                            };

                            let html = '';
                            for (const [karat, price] of Object.entries(prices)) {
                                html += `<span class="me-3">${karat}: $${price}/g</span>`;
                            }
                            document.getElementById('livePrices').innerHTML = html;
                        }
                    })
                    .catch(error => {
                        console.log('Gold prices temporarily unavailable');
                    });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
