{{-- File: resources/views/products/index.blade.php - CORRECTED VERSION --}}
@extends('layouts.admin')

@section('title', 'Gold Trading Management System')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

<!-- 100% DYNAMIC SYSTEM -->
<div class="container-fluid py-3" id="mainContainer">

    <!-- =====================================================
         SYSTEM INITIALIZATION & LOADING
         ===================================================== -->
    <div class="row justify-content-center" id="systemLoading">
        <div class="col-md-6 text-center py-5">
            <div class="card border-primary shadow">
                <div class="card-body py-5">
                    <i class="fas fa-database fa-3x text-primary mb-3"></i>
                    <h4 class="text-primary mb-3">Initializing Trading System</h4>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             id="loadingProgress" style="width: 0%"></div>
                    </div>
                    <div id="loadingStatus" class="text-muted">
                        <i class="fas fa-spinner fa-spin me-2"></i>Starting system...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         MAIN SYSTEM INTERFACE
         ===================================================== -->
    <div class="d-none" id="systemInterface">

        <!-- Live Prices & System Status Row -->
        <div class="row mb-4">
            <!-- Live Metal Prices -->
            <div class="col-lg-8">
                <div class="card border-success shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-chart-line me-2"></i>
                            <strong>Live Metal Prices</strong>
                            <span class="badge bg-light text-success ms-2">
                                <i class="fas fa-circle live-indicator me-1" style="font-size: 0.5rem;"></i>LIVE
                            </span>
                        </div>
                        <button class="btn btn-light btn-sm" id="refreshLivePrices" onclick="refreshLivePrices()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3" id="livePricesContainer">
                            <!-- Populated dynamically from API -->
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted" id="livePricesLastUpdated">
                                <i class="fas fa-clock me-1"></i>Connecting to metals-api.com...
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="col-lg-4">
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-cogs me-2"></i><strong>System Status</strong>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2" id="systemStatusContainer">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>API Status:</span>
                                    <span class="badge bg-success">Connected</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Database:</span>
                                    <span class="badge bg-success">Online</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Cache:</span>
                                    <span class="badge bg-warning">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metal Navigation & Current Prices -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning shadow-sm">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <!-- Metal Selector -->
                            <div class="col-lg-6">
                                <label class="form-label fw-bold mb-2">
                                    <i class="fas fa-atom me-2"></i>Select Metal:
                                </label>
                                <nav class="nav nav-pills flex-wrap" id="metalNavigation">
                                    <!-- Populated from API -->
                                </nav>
                            </div>
                            <!-- Current Metal Prices -->
                            <div class="col-lg-6">
                                <label class="form-label fw-bold mb-2">
                                    <i class="fas fa-tags me-2"></i>Current Prices (AUD/g):
                                </label>
                                <div class="d-flex justify-content-end flex-wrap gap-1" id="metalPricesDisplay">
                                    <!-- Populated dynamically based on selected metal -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="row">
            <!-- Products Section -->
            <div class="col-lg-8">

                <!-- Module Navigation -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body py-2">
                        <nav class="nav nav-pills justify-content-center">
                            <button class="nav-link nav-module active me-2" data-module="jewelry" onclick="switchModule('jewelry')">
                                <i class="fas fa-gem me-1"></i>Jewelry Sales
                            </button>
                            <button class="nav-link nav-module me-2" data-module="scrap" onclick="switchModule('scrap')">
                                <i class="fas fa-recycle me-1"></i>Scrap Purchase
                            </button>
                            <button class="nav-link nav-module me-2" data-module="bullion_sell" onclick="switchModule('bullion_sell')">
                                <i class="fas fa-coins me-1"></i>Bullion Sales
                            </button>
                            <button class="nav-link nav-module" data-module="bullion_buy" onclick="switchModule('bullion_buy')">
                                <i class="fas fa-handshake me-1"></i>Bullion Purchase
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- =====================================================
                     JEWELRY SALES MODULE
                     ===================================================== -->
                <div class="module-section" id="jewelrySection">

                    <!-- Subcategory Tabs -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body py-2">
                            <nav class="nav nav-pills nav-fill" id="subcategoryTabs">
                                <!-- Populated from API -->
                            </nav>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body py-3">
                            <div class="row g-3 align-items-end">
                                <!-- Search -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Search Products</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="searchInput"
                                               placeholder="Product name..." onkeyup="searchProducts(this.value)">
                                    </div>
                                </div>

                                <!-- Purity Filter -->
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Purity</label>
                                    <select class="form-select" id="purityFilter" onchange="filterProducts()">
                                        <!-- Populated based on current metal -->
                                    </select>
                                </div>

                                <!-- Sort -->
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Sort By</label>
                                    <select class="form-select" id="sortFilter" onchange="filterProducts()">
                                        <option value="name">Name A-Z</option>
                                        <option value="price_low">Price: Low to High</option>
                                        <option value="price_high">Price: High to Low</option>
                                        <option value="weight">Weight</option>
                                    </select>
                                </div>

                                <!-- View Toggle -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">View Mode</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary active"
                                                id="gridViewBtn" onclick="switchToGridView()">
                                            <i class="fas fa-th me-1"></i>Grid View
                                        </button>
                                        <button type="button" class="btn btn-outline-primary"
                                                id="listViewBtn" onclick="switchToListView()">
                                            <i class="fas fa-list me-1"></i>List View
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Display Container -->
                    <div id="productsContainer">

                        <!-- Grid View -->
                        <div class="grid-view" id="gridView">
                            <div class="row g-3" id="gridProductContainer">
                                <!-- Products populated dynamically from API -->
                                <div class="col-12 text-center py-5">
                                    <div class="card shadow-sm">
                                        <div class="card-body py-5">
                                            <div class="spinner-border text-primary mb-3" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <h5 class="text-muted">Loading Products...</h5>
                                            <p class="text-muted">Please wait while we load your jewelry collection</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div class="list-view d-none" id="listView">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-dark" id="listTableHeader">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Category</th>
                                                    <th>Purity</th>
                                                    <th>Weight</th>
                                                    <th>Price/g</th>
                                                    <th>Total</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="listProductContainer">
                                                <!-- Products populated dynamically from API -->
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="spinner-border text-primary mb-3" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <p class="text-muted mb-0">Loading products...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Products Message -->
                        <div class="text-center py-5 d-none" id="noProductsMessage">
                            <div class="card shadow-sm">
                                <div class="card-body py-5">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Products Found</h5>
                                    <p class="text-muted" id="noProductsReason">
                                        Try adjusting your search filters
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =====================================================
                     SCRAP PURCHASE MODULE
                     ===================================================== -->
                <div class="module-section d-none" id="scrapSection">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-recycle me-2"></i>Scrap Metal Purchase
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Weight (grams)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg"
                                               id="scrapWeight" min="0.1" step="0.1" placeholder="0.0">
                                        <span class="input-group-text">grams</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Purity/Karat</label>
                                    <select class="form-select form-select-lg" id="scrapKarat">
                                        <!-- Populated dynamically based on current metal -->
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Price Breakdown</h6>
                                        </div>
                                        <div class="card-body" id="scrapPriceDisplay">
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-calculator fa-2x mb-2"></i>
                                                <p>Enter weight and select purity to see pricing</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-warning btn-lg w-100" onclick="addScrapToCart()">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Purchase Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =====================================================
                     BULLION SALES MODULE
                     ===================================================== -->
                <div class="module-section d-none" id="bullionSellSection">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-coins me-2"></i>Bullion Sales to Customer
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Weight (grams)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg"
                                               id="bullionSellWeight" min="0.1" step="0.1" placeholder="0.0">
                                        <span class="input-group-text">grams</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Bullion Size/Type</label>
                                    <select class="form-select form-select-lg" id="bullionSellSize">
                                        <!-- Populated dynamically -->
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">Sale Price Calculation</h6>
                                        </div>
                                        <div class="card-body" id="bullionSellPriceDisplay">
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-coins fa-2x mb-2"></i>
                                                <p>Enter weight to calculate sale price</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary btn-lg w-100" onclick="addBullionToCart('sell')">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Sales Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =====================================================
                     BULLION PURCHASE MODULE
                     ===================================================== -->
                <div class="module-section d-none" id="bullionBuySection">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-handshake me-2"></i>Bullion Purchase from Customer
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Weight (grams)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg"
                                               id="bullionBuyWeight" min="0.1" step="0.1" placeholder="0.0">
                                        <span class="input-group-text">grams</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Condition</label>
                                    <select class="form-select form-select-lg" id="bullionBuyCondition">
                                        <option value="new">New/Mint Condition</option>
                                        <option value="good">Good Condition</option>
                                        <option value="fair">Fair Condition</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Purchase Price Calculation</h6>
                                        </div>
                                        <div class="card-body" id="bullionBuyPriceDisplay">
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-handshake fa-2x mb-2"></i>
                                                <p>Enter weight to calculate purchase price</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-success btn-lg w-100" onclick="addBullionToCart('buy')">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Purchase Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 CART & CUSTOMER SIDEBAR
                 ===================================================== -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 1rem;">

                    <!-- Transaction Cart -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Transaction Cart
                                <span class="badge bg-light text-primary ms-2" id="cartItemCount">0</span>
                            </h5>
                        </div>

                        <!-- Cart Items -->
                        <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                            <div id="cartItems">
                                <div class="text-center py-5" id="emptyCart">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Cart is Empty</h6>
                                    <p class="text-muted small">Add products to get started</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Summary -->
                        <div class="card-footer">
                            <div class="cart-summary d-none" id="cartSummary">
                                <div id="cartTotalsContainer">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="cartSubtotal">AUD0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>GST (10%):</span>
                                        <span id="cartTax">AUD0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Shipping:</span>
                                        <span id="cartShipping">FREE</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between h5">
                                        <strong>Total:</strong>
                                        <strong id="cartTotal">AUD0.00</strong>
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-success btn-lg" onclick="proceedToCheckout()">
                                        <i class="fas fa-credit-card me-2"></i>Process Transaction
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="clearCart()">
                                        <i class="fas fa-trash me-1"></i>Clear Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Search -->
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-2"></i>Customer Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Search Customer</label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="customerSearch"
                                               placeholder="Name, email, or phone...">
                                    </div>
                                    <div class="dropdown-menu w-100 d-none" id="customerDropdown">
                                        <!-- Customer search results -->
                                    </div>
                                </div>
                            </div>
                            <div class="text-center" id="selectedCustomerInfo">
                                <small class="text-muted">
                                    <i class="fas fa-user-plus me-1"></i>
                                    No customer selected
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     ERROR HANDLING SECTION
     ===================================================== -->
<div class="container py-5 d-none" id="errorSection">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card border-danger shadow">
                <div class="card-body py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-danger mb-4"></i>
                    <h4 class="text-danger mb-3">System Error</h4>
                    <p class="text-muted mb-4" id="errorMessage">
                        Unable to connect to database or external API services
                    </p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" onclick="location.reload()">
                            <i class="fas fa-redo me-2"></i>Retry Connection
                        </button>
                        <button class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- ===================================================== --}}
{{-- STYLES SECTION --}}
{{-- ===================================================== --}}
@push('styles')
<style>
/* Live Price Animations */
@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

.live-indicator {
    animation: blink 1s infinite;
}

@keyframes priceFlash {
    0% { background-color: #d4edda; transform: scale(1.02); }
    100% { background-color: transparent; transform: scale(1); }
}

.price-flash {
    animation: priceFlash 0.8s ease-out;
}

@keyframes priceUpdate {
    0% { background-color: #fff3cd; }
    100% { background-color: transparent; }
}

.price-updated {
    animation: priceUpdate 1s ease-out;
}

/* Loading Animation */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.loading i {
    animation: spin 1s linear infinite;
}

/* Product Cards */
.product-card {
    transition: all 0.3s ease;
    border: none !important;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

/* Cart Animations */
.cart-item {
    transition: background-color 0.2s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

/* Button States */
.btn-added {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

/* Price Card Styling */
.price-card {
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.price-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.price-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #28a745;
}

/* Enhanced Loading States */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sticky-top {
        position: static !important;
        margin-top: 2rem;
    }

    .nav-pills {
        flex-direction: column !important;
    }

    .nav-pills .nav-link {
        margin-bottom: 0.5rem;
    }

    #livePricesContainer .col-md-3 {
        margin-bottom: 1rem;
    }
}

/* Print Styles */
@media print {
    .btn, .nav, .card-header .btn, .alert {
        display: none !important;
    }

    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}

/* Enhanced Navigation */
.nav-metal, .nav-module {
    transition: all 0.2s ease;
}

.nav-metal:hover, .nav-module:hover {
    transform: translateY(-1px);
}

/* System Status Indicators */
.badge {
    font-size: 0.75rem;
}

/* Enhanced Form Controls */
.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Custom Scrollbar for Cart */
#cartItems::-webkit-scrollbar {
    width: 6px;
}

#cartItems::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#cartItems::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#cartItems::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush

{{-- ===================================================== --}}
{{-- JAVASCRIPT SECTION --}}
{{-- ===================================================== --}}
@push('scripts')
<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- Trading System JavaScript -->
<script src="{{ asset('js/optimized-trading-system.js') }}"></script>

<!-- Additional Helper Functions -->
<script>
// Helper functions for HTML onclick handlers
function switchToGridView() {
    if (window.tradingSystem) {
        window.tradingSystem.state.currentView = 'grid';
        document.getElementById('gridView')?.classList.remove('d-none');
        document.getElementById('listView')?.classList.add('d-none');
        document.getElementById('gridViewBtn')?.classList.add('active');
        document.getElementById('listViewBtn')?.classList.remove('active');
        localStorage.setItem('trading_view_preference', 'grid');
    }
}

function switchToListView() {
    if (window.tradingSystem) {
        window.tradingSystem.state.currentView = 'list';
        document.getElementById('gridView')?.classList.add('d-none');
        document.getElementById('listView')?.classList.remove('d-none');
        document.getElementById('gridViewBtn')?.classList.remove('active');
        document.getElementById('listViewBtn')?.classList.add('active');
        localStorage.setItem('trading_view_preference', 'list');
    }
}

function filterProducts() {
    // Basic filter implementation
    console.log('Filtering products...');
}

function searchProducts(query) {
    if (window.tradingSystem) {
        window.tradingSystem.searchProducts(query);
    }
}

// Initialize view preference on load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('trading_view_preference') || 'grid';
    if (savedView === 'list') {
        switchToListView();
    } else {
        switchToGridView();
    }
});
</script>
@endpush
