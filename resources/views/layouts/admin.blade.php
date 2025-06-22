{{-- File: resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ asset('css/jewelry-shop.css') }}" rel="stylesheet">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Jewelry Store') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Admin Styles -->
    <style>
        :root {
            --admin-primary: #4e73df;
            --admin-secondary: #858796;
            --admin-success: #1cc88a;
            --admin-info: #36b9cc;
            --admin-warning: #f6c23e;
            --admin-danger: #e74a3b;
            --admin-light: #f8f9fc;
            --admin-dark: #5a5c69;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--admin-light);
            font-size: 0.9rem;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #2d3a87 0%, #1e2872 100%);
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-brand {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .sidebar-brand:hover {
            color: white;
        }

        .sidebar.collapsed .sidebar-brand-text {
            display: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav .nav-item {
            margin-bottom: 0.25rem;
        }

        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

        .sidebar.collapsed .nav-link-text {
            display: none;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0;
        }

        .sidebar-heading {
            padding: 0.75rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 0.05rem;
        }

        .sidebar.collapsed .sidebar-heading {
            display: none;
        }

        /* Sub-navigation styles */
        .nav-item-sub {
            margin-left: 1rem;
        }

        .nav-item-sub .nav-link {
            padding-left: 2.5rem;
            font-size: 0.85rem;
        }

        .sidebar.collapsed .nav-item-sub {
            margin-left: 0;
        }

        .sidebar.collapsed .nav-item-sub .nav-link {
            padding-left: 1.5rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Topbar */
        .topbar {
            height: 80px;
            background: white;
            border-bottom: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .topbar-divider {
            width: 0;
            border-right: 1px solid #e3e6f0;
            height: 2rem;
            margin: auto 1rem;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.35rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        /* Border left colors for cards */
        .border-left-primary {
            border-left: 0.25rem solid var(--admin-primary) !important;
        }

        .border-left-success {
            border-left: 0.25rem solid var(--admin-success) !important;
        }

        .border-left-info {
            border-left: 0.25rem solid var(--admin-info) !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--admin-warning) !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid var(--admin-danger) !important;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }

        .btn-success {
            background-color: var(--admin-success);
            border-color: var(--admin-success);
        }

        .btn-info {
            background-color: var(--admin-info);
            border-color: var(--admin-info);
        }

        .btn-warning {
            background-color: var(--admin-warning);
            border-color: var(--admin-warning);
        }

        .btn-danger {
            background-color: var(--admin-danger);
            border-color: var(--admin-danger);
        }

        /* Tables */
        .table {
            color: #5a5c69;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            color: #5a5c69;
        }

        .table td {
            border-top: 1px solid #e3e6f0;
            vertical-align: middle;
        }

        /* Text colors */
        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-gray-900 {
            color: #3a3b45 !important;
        }

        .text-primary {
            color: var(--admin-primary) !important;
        }

        .text-success {
            color: var(--admin-success) !important;
        }

        .text-info {
            color: var(--admin-info) !important;
        }

        .text-warning {
            color: var(--admin-warning) !important;
        }

        .text-danger {
            color: var(--admin-danger) !important;
        }

        /* Badge styles */
        .badge {
            font-size: 0.75rem;
        }

        .badge-primary {
            background-color: var(--admin-primary);
        }

        .badge-success {
            background-color: var(--admin-success);
        }

        .badge-info {
            background-color: var(--admin-info);
        }

        .badge-warning {
            background-color: var(--admin-warning);
        }

        .badge-danger {
            background-color: var(--admin-danger);
        }

        .badge-secondary {
            background-color: var(--admin-secondary);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
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
        }

        /* Form enhancements */
        .form-control:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .form-select:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Alert styles */
        .alert {
            border: none;
            border-radius: 0.35rem;
        }

        .alert-success {
            background-color: #d1eddf;
            color: #0a3622;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <!-- Sidebar Brand -->
        <a class="sidebar-brand" href="{{ route('external.home') }}">
            <i class="fas fa-gem me-2"></i>
            <span class="sidebar-brand-text">
@auth
    @if(auth()->user()->hasRole('customer'))
        Customer Panel
    @else
        Admin Panel
    @endif
@else
    {{-- User not authenticated, redirect to login --}}
    <script>
        window.location.href = '{{ route("login") }}';
    </script>
    Guest Panel
@endauth
            </span>
        </a>

        <!-- Sidebar Navigation -->
        <ul class="nav flex-column sidebar-nav">
            <!-- Dashboard - Only show if user has admin access -->
            <!-- @if(auth()->user()->hasPermission('admin.dashboard')) -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>
            <!-- @endif -->

            <!-- Categories Section - Reorganized with Metal Categories and Subcategories -->
            @if(auth()->user()->hasPermission('categories.view') || auth()->user()->hasPermission('admin.metal-categories.view') || auth()->user()->hasPermission('admin.subcategories.view'))
            <div class="sidebar-heading">Categories</div>

            @if(auth()->user()->hasPermission('admin.metal-categories.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.metal-categories.*') ? 'active' : '' }}"
                   href="{{ route('admin.metal-categories.index') }}">
                    <i class="fas fa-coins"></i>
                    <span class="nav-link-text">Metal Categories</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('admin.subcategories.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}"
                   href="{{ route('admin.subcategories.index') }}">
                    <i class="fas fa-layer-group"></i>
                    <span class="nav-link-text">Subcategories</span>
                </a>
            </li>
            @endif
            @endif

            <!-- Products Section -->
            @if(auth()->user()->hasPermission('products.view') || auth()->user()->hasPermission('admin.products.view'))
            <div class="sidebar-divider"></div>
            <div class="sidebar-heading">Products</div>

            @if(auth()->user()->hasPermission('products.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('products.*') && !request()->routeIs('admin.products.*') ? 'active' : '' }}"
                   href="{{ route('products.index') }}">
                    <i class="fas fa-store"></i>
                    <span class="nav-link-text">Shop</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('admin.products.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                   href="{{ route('admin.products.index') }}">
                    <i class="fas fa-gem"></i>
                    <span class="nav-link-text">Manage Products</span>
                </a>
            </li>
            @endif
            @endif

            <!-- Orders Section - Only for users with order permissions -->
            @if(auth()->user()->hasPermission('orders.view') || auth()->user()->hasPermission('admin.orders.view'))
            <div class="sidebar-divider"></div>
            <div class="sidebar-heading">Orders</div>

            @if(auth()->user()->hasPermission('orders.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.*') && !request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                   href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="nav-link-text">My Orders</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('admin.orders.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                   href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-receipt"></i>
                    <span class="nav-link-text">All Orders</span>
                </a>
            </li>
            @endif

            @endif

            <!-- Users Section - Only for users with user management permissions -->
            @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view'))
            <div class="sidebar-divider"></div>
            <div class="sidebar-heading">Users</div>

            @if(auth()->user()->hasPermission('users.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') || request()->routeIs('auth.*') ? 'active' : '' }}"
                   href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users"></i>
                    <span class="nav-link-text">All Users</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('roles.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                   href="{{ route('admin.roles.index') }}">
                    <i class="fas fa-user-shield"></i>
                    <span class="nav-link-text">Roles & Permissions</span>
                </a>
            </li>
            @endif
            @endif

        </ul>
    </nav>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top">
            <!-- Sidebar Toggle -->
            <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Sidebar Toggle (Desktop) -->
            <button class="btn btn-link d-none d-md-inline rounded-circle me-3" id="sidebarToggle">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Page Title -->
            <div class="d-none d-sm-inline-block">
                <h1 class="h5 mb-0 text-gray-800">@yield('title', 'Dashboard')</h1>
            </div>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Metal Prices - Only show for users who can view products -->
                @if(auth()->user()->hasPermission('products.view') || auth()->user()->hasPermission('admin.products.view'))
                <li class="nav-item dropdown no-arrow me-3">
                    <a class="nav-link dropdown-toggle" href="#" id="metalPricesDropdown" role="button"
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-coins fa-fw text-warning"></i>
                        <span class="badge badge-danger badge-counter" id="priceUpdateIndicator"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                        <h6 class="dropdown-header">Live Metal Prices (AUD/g)</h6>
                        <div id="adminMetalPrices">
                            <div class="row mb-2">
                                <div class="col-4"><strong>Gold 18K:</strong></div>
                                <div class="col-8 text-end text-success">$63.40</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Silver 925:</strong></div>
                                <div class="col-8 text-end text-info">$1.20</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Platinum:</strong></div>
                                <div class="col-8 text-end text-primary">$45.00</div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <small class="text-muted">Updated every 10 minutes</small>
                    </div>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>
                @endif

                <!-- User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline text-gray-600 small">
                            {{ Auth::user()->name }}
                            @if(auth()->user()->role)
                                <small class="text-muted">({{ auth()->user()->role->name }})</small>
                            @endif
                        </span>
                        @if(Auth::user()->profile_picture)
                            <img src="{{ Auth::user()->profile_picture_url }}" alt="{{ Auth::user()->name }}"
                                 class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center"
                                 style="width: 32px; height: 32px;">
                                <span class="text-white fw-bold" style="font-size: 14px;">{{ Auth::user()->initials }}</span>
                            </div>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header text-center">
                            <strong>{{ Auth::user()->name }}</strong>
                            @if(auth()->user()->role)
                                <br><small class="text-muted">{{ auth()->user()->role->name }}</small>
                            @endif
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.index') }}" class="dropdown-item">
    <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>Profile
</a>

                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Coming Soon Modal -->
    <div class="modal fade" id="comingSoonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feature Coming Soon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-tools fa-3x text-warning mb-3"></i>
                    <h6 id="featureName">Feature</h6>
                    <p class="text-muted">This feature is currently under development and will be available in the next update.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/jewelry-shop.js') }}"></script>
<script src="{{ asset('js/smart-image-loader.js') }}"></script>
    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarToggleTop = document.getElementById('sidebarToggleTop');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Desktop sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }

            // Mobile sidebar toggle
            if (sidebarToggleTop) {
                sidebarToggleTop.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (alert.querySelector('.btn-close')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Load metal prices
            loadMetalPrices();
            setInterval(loadMetalPrices, 300000); // Update every 5 minutes
        });

        // Load metal prices function
        function loadMetalPrices() {
            fetch('/api/live-prices')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.prices) {
                        updateMetalPricesDisplay(data.prices);
                    }
                })
                .catch(error => {
                    console.log('Using fallback metal prices');
                });
        }

        // Update metal prices display
        function updateMetalPricesDisplay(prices) {
            const pricesContainer = document.getElementById('adminMetalPrices');
            if (!pricesContainer) return;

            let html = '';
            
            // Gold prices
            if (prices.gold && prices.gold['18']) {
                html += `<div class="row mb-2">
                    <div class="col-4"><strong>Gold 18K:</strong></div>
                    <div class="col-8 text-end text-warning">${prices.gold['18'].toFixed(2)}</div>
                </div>`;
            }
            
            // Silver prices
            if (prices.silver && prices.silver['925']) {
                html += `<div class="row mb-2">
                    <div class="col-4"><strong>Silver 925:</strong></div>
                    <div class="col-8 text-end text-info">${prices.silver['925'].toFixed(2)}</div>
                </div>`;
            }
            
            // Platinum prices
            if (prices.platinum && prices.platinum['950']) {
                html += `<div class="row mb-2">
                    <div class="col-4"><strong>Platinum:</strong></div>
                    <div class="col-8 text-end text-primary">${prices.platinum['950'].toFixed(2)}</div>
                </div>`;
            }

            if (html) {
                pricesContainer.innerHTML = html;
            }
        }

        // Coming Soon Modal Function
        function showComingSoon(featureName) {
            document.getElementById('featureName').textContent = featureName;
            const modal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
            modal.show();
        }
    </script>

    @stack('scripts')
</body>
</html>