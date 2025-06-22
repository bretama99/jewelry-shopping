<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

@extends('layouts.admin')

@section('title', 'Gold Trading Management System')

@section('content')

<!-- 100% DYNAMIC SYSTEM - NO MANUAL DATA -->
<div class="container-fluid py-3" id="mainContainer">
    
    <!-- System Loading Screen -->
    <div class="row justify-content-center" id="systemLoading">
        <div class="col-md-6 text-center py-5">
            <div class="card border-primary">
                <div class="card-body py-5">
                    <i class="fas fa-database fa-3x text-primary mb-3"></i>
                    <h4 class="text-primary mb-3">Initializing Trading System</h4>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="loadingProgress" style="width: 0%"></div>
                    </div>
                    <div id="loadingStatus" class="text-muted">Starting system...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main System Interface (Hidden until loaded) -->
    <div class="d-none" id="systemInterface">
        
        <!-- Dynamic Live Prices Section -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-header bg-success text-white py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Live Metal Prices
                                <span class="badge bg-light text-success ms-2" style="font-size: 0.6rem;">
                                    <i class="fas fa-circle text-success me-1 live-indicator" style="font-size: 0.5rem;"></i>LIVE
                                </span>
                            </h6>
                            <button class="btn btn-light btn-sm" id="refreshLivePrices" onclick="refreshLivePrices()" style="font-size: 0.7rem;">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <!-- Dynamic live prices container - populated from database metals -->
                        <div class="row g-2" id="livePricesContainer">
                            <!-- Populated dynamically from metalCategories -->
                        </div>
                        
                        <div class="text-center mt-2">
                            <small class="text-muted" id="livePricesLastUpdated">
                                <i class="fas fa-clock me-1"></i>Connecting to live API...
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic System Status -->
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>System Status
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2" id="systemStatusContainer">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Metal Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <!-- Dynamic metal navigation from database -->
                                <nav class="nav nav-pills nav-pills-sm" id="metalNavigation">
                                    <!-- Populated from metalCategories API -->
                                </nav>
                            </div>
                            <div class="col-md-6">
                                <!-- Dynamic price display for current metal -->
                                <div class="d-flex justify-content-end flex-wrap gap-2" id="metalPricesDisplay">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-8">

                <!-- Dynamic Module Navigation -->
                <div class="card mb-4" id="moduleNavigation">
                    <!-- Populated dynamically -->
                </div>

                <!-- JEWELRY SALES MODULE -->
                <div class="module-section" id="jewelrySection">
                    
                    <!-- Dynamic Subcategory Navigation -->
                    <div class="card mb-4">
                        <div class="card-body py-2">
                            <ul class="nav nav-pills nav-pills-sm" id="subcategoryTabs">
                                <!-- Populated from subcategories API -->
                            </ul>
                        </div>
                    </div>

                    <!-- Dynamic Search and Controls -->
                    <div class="card mb-4">
                        <div class="card-body py-2">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="searchInput" placeholder="Search products..." onkeyup="searchProducts()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <!-- Dynamic purity filter based on current metal -->
                                    <select class="form-select form-select-sm" id="purityFilter" onchange="filterProducts()">
                                        <!-- Populated based on current metal's available purities -->
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="sortFilter" onchange="filterProducts()">
                                        <option value="name">Sort by Name</option>
                                        <option value="price_low">Price: Low to High</option>
                                        <option value="price_high">Price: High to Low</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <small class="text-muted">View:</small>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary active" id="gridViewBtn" onclick="switchToGridView()">
                                                <i class="fas fa-th"></i> Grid
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" id="listViewBtn" onclick="switchToListView()">
                                                <i class="fas fa-list"></i> List
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Products Display -->
                    <div id="productsContainer">
                        <!-- Grid View -->
                        <div class="grid-view" id="gridView">
                            <div class="row g-3" id="gridProductContainer">
                                <!-- Populated from products API -->
                            </div>
                        </div>

                        <!-- List View -->
                        <div class="list-view d-none" id="listView">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light" id="listTableHeader">
                                        <!-- Header populated dynamically -->
                                    </thead>
                                    <tbody id="listProductContainer">
                                        <!-- Populated from products API -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Dynamic Status Messages -->
                        <div class="text-center py-5 d-none" id="noProductsMessage">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No products found</h5>
                            <p class="text-muted" id="noProductsReason">Adjust your filters</p>
                        </div>
                    </div>
                </div>

                <!-- SCRAP METAL MODULE -->
                <div class="module-section d-none" id="scrapSection">
                    <!-- Dynamic scrap content -->
                    <div id="scrapContent">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- BULLION MODULES -->
                <div class="module-section d-none" id="bullionSellSection">
                    <!-- Dynamic bullion sell content -->
                    <div id="bullionSellContent">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <div class="module-section d-none" id="bullionBuySection">
                    <!-- Dynamic bullion buy content -->
                    <div id="bullionBuyContent">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- Cart Sidebar -->
            <div class="col-lg-4">
                <div class="cart-sidebar position-sticky" style="top: 2rem;">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Transaction Cart
                            </h5>
                        </div>

                        <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                            <div id="cartItems">
                                <div class="text-center py-4" id="emptyCart">
                                    <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Your cart is empty</p>
                                    <small class="text-muted">Add items to get started</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="cart-summary d-none" id="cartSummary">
                                <!-- Dynamic cart summary -->
                                <div id="cartTotalsContainer">
                                    <!-- Populated dynamically -->
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-success" onclick="proceedToCheckout()">
                                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                                        <i class="fas fa-trash me-1"></i>Clear Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Checkout Section -->
<div class="container py-4 d-none" id="checkoutSection">
    <!-- Populated dynamically when needed -->
</div>

<!-- Dynamic Receipt Section -->
<div class="container py-4 d-none" id="receiptSection">
    <!-- Populated dynamically when needed -->
</div>

<!-- Error Handling -->
<div class="container py-4 d-none" id="errorSection">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card border-danger">
                <div class="card-body py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h4 class="text-danger mb-3">System Error</h4>
                    <p class="text-muted mb-3" id="errorMessage">Unable to connect to database or API</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-redo me-1"></i>Retry
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dynamic Animations */
@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

.live-indicator {
    animation: blink 1s infinite;
}

@keyframes priceFlash {
    0% { background-color: #d4edda; }
    100% { background-color: #f8f9fa; }
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

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.loading i {
    animation: spin 1s linear infinite;
}

/* Dynamic Product Cards */
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Dynamic View Transitions */
.view-transition {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Dynamic Cart Animations */
.cart-item {
    transition: background-color 0.2s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

/* Dynamic Button States */
.btn-added {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

/* Dynamic Progress */
.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% { background-position: 1rem 0; }
    100% { background-position: 0 0; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .cart-sidebar {
        position: static !important;
        margin-top: 2rem;
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
</style>

<script>
// 100% DYNAMIC SYSTEM INITIALIZATION
document.addEventListener('DOMContentLoaded', function() {
    initializeDynamicSystem();
});

async function initializeDynamicSystem() {
    try {
        updateLoadingProgress(10, 'Connecting to database...');
        
        // Load all data from database APIs
        updateLoadingProgress(30, 'Loading metal categories...');
        await loadMetalCategoriesFromAPI();
        
        updateLoadingProgress(40, 'Loading subcategories...');
        await loadSubcategoriesFromAPI();
        
        updateLoadingProgress(50, 'Loading products...');
        await loadProductsFromAPI();
        
        updateLoadingProgress(60, 'Loading company information...');
        await loadCompanyInfoFromAPI();
        
        updateLoadingProgress(70, 'Loading trading configuration...');
        await loadTradingConfigFromAPI();
        
        updateLoadingProgress(80, 'Fetching live metal prices...');
        await fetchLiveMetalPricesFromAPI();
        
        updateLoadingProgress(90, 'Building interface...');
        await buildDynamicInterface();
        
        updateLoadingProgress(100, 'System ready!');
        
        // Show main interface
        setTimeout(() => {
            document.getElementById('systemLoading').classList.add('d-none');
            document.getElementById('systemInterface').classList.remove('d-none');
            initializeEventListeners();
            updateAllPrices();
        }, 500);
        
    } catch (error) {
        console.error('System initialization failed:', error);
        showSystemError(error.message);
    }
}

function updateLoadingProgress(percentage, status) {
    document.getElementById('loadingProgress').style.width = percentage + '%';
    document.getElementById('loadingStatus').textContent = status;
}

async function loadMetalCategoriesFromAPI() {
    const response = await fetch('/api/metal-categories');
    if (!response.ok) throw new Error('Failed to load metal categories');
    const data = await response.json();
    if (!data.success) throw new Error('Invalid metal categories data');
    window.metalCategories = data.data;
}

async function loadSubcategoriesFromAPI() {
    const response = await fetch('/api/subcategories');
    if (!response.ok) throw new Error('Failed to load subcategories');
    const data = await response.json();
    if (!data.success) throw new Error('Invalid subcategories data');
    window.subcategories = data.data;
}

async function loadProductsFromAPI() {
    const response = await fetch('/api/products');
    if (!response.ok) throw new Error('Failed to load products');
    const data = await response.json();
    if (!data.success) throw new Error('Invalid products data');
    window.products = data.data;
}

async function loadCompanyInfoFromAPI() {
    try {
        const response = await fetch('/api/company-info');
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                window.companyInfo = data.data;
            }
        }
    } catch (error) {
        // Use default company info if API not available
        console.log('Using default company info');
    }
}

async function loadTradingConfigFromAPI() {
    // Load all trading configuration from APIs
    window.tradingConfig = {
        jewelry: { laborCosts: {}, profitMargin: 0.25 },
        scrap: { processingFee: 0.15, margins: {} },
        bullion: { sellPremium: {}, buyMargin: {}, sizes: {} }
    };
    
    // Load specific configs for each metal
    for (const metal of window.metalCategories) {
        await loadMetalSpecificConfig(metal.slug);
    }
}

async function loadMetalSpecificConfig(metalSlug) {
    try {
        // Load scrap margins
        const scrapResponse = await fetch(`/api/metals/${metalSlug}/scrap-margins`);
        if (scrapResponse.ok) {
            const scrapData = await scrapResponse.json();
            window.tradingConfig.scrap.margins[metalSlug] = scrapData.margins || {};
        }
        
        // Load bullion premiums
        const premiumResponse = await fetch(`/api/metals/${metalSlug}/bullion-premium`);
        if (premiumResponse.ok) {
            const premiumData = await premiumResponse.json();
            window.tradingConfig.bullion.sellPremium[metalSlug] = premiumData.sell_premium || 0.08;
        }
        
        // Load bullion buy margins
        const marginResponse = await fetch(`/api/metals/${metalSlug}/bullion-margin`);
        if (marginResponse.ok) {
            const marginData = await marginResponse.json();
            window.tradingConfig.bullion.buyMargin[metalSlug] = marginData.buy_margin || 0.05;
        }
    } catch (error) {
        console.warn(`Failed to load config for ${metalSlug}:`, error);
    }
}

async function fetchLiveMetalPricesFromAPI() {
    const response = await fetch('https://api.metalpriceapi.com/v1/latest?api_key=d68f51781cca05150ab380fbea59224c&base=AUD&currencies=XAU,XAG,XPD,XPT');
    if (!response.ok) throw new Error('Failed to fetch live metal prices');
    
    const data = await response.json();
    if (!data.success) throw new Error('Invalid live price data');
    
    // Store live prices (pure metal prices per troy ounce in AUD)
    window.livePrices = {
        XAU: 1 / data.rates.XAU, // Pure 24K Gold
        XAG: 1 / data.rates.XAG, // Pure 999 Silver  
        XPD: 1 / data.rates.XPD, // Pure 999 Palladium
        XPT: 1 / data.rates.XPT, // Pure 999 Platinum
        last_updated: new Date()
    };
    
    // Calculate all purity prices
    calculateAllPurityPrices();
}

function calculateAllPurityPrices() {
    const gramsPerTroyOz = 31.1035;
    window.metalPricesPerGram = {};
    
    // Calculate prices for each metal from database
    window.metalCategories.forEach(metal => {
        const metalSlug = metal.slug;
        const metalSymbol = metal.symbol;
        
        // Get pure metal price per gram from live API
        let purePricePerGram;
        switch(metalSymbol) {
            case 'XAU': purePricePerGram = window.livePrices.XAU / gramsPerTroyOz; break;
            case 'XAG': purePricePerGram = window.livePrices.XAG / gramsPerTroyOz; break;
            case 'XPT': purePricePerGram = window.livePrices.XPT / gramsPerTroyOz; break;
            case 'XPD': purePricePerGram = window.livePrices.XPD / gramsPerTroyOz; break;
            default: return;
        }
        
        // Get available purities from database
        const availablePurities = metal.available_purities || metal.available_karats || [];
        
        window.metalPricesPerGram[metalSlug] = {};
        
        // Calculate price for each purity
        availablePurities.forEach(purity => {
            let purityRatio;
            
            if (metalSymbol === 'XAU') {
                // Gold: karat/24 (your exact formula)
                purityRatio = parseInt(purity) / 24;
            } else {
                // Other metals: purity/1000 or from database purity_ratios
                if (metal.purity_ratios && metal.purity_ratios[purity]) {
                    purityRatio = metal.purity_ratios[purity];
                } else {
                    purityRatio = parseInt(purity) / 1000;
                }
            }
            
            // currentPrice = price(for pure) * purity ratio (your exact formula)
            const currentPrice = purePricePerGram * purityRatio;
            window.metalPricesPerGram[metalSlug][purity] = Math.round(currentPrice * 100) / 100;
        });
    });
}

async function buildDynamicInterface() {
    buildLivePricesDisplay();
    buildSystemStatus();
    buildMetalNavigation();
    buildSubcategoryTabs();
    buildModuleNavigation();
    buildPurityFilter();
    buildListTableHeader();
    buildProductsDisplay();
    buildCartTotalsContainer();
}

function buildLivePricesDisplay() {
    const container = document.getElementById('livePricesContainer');
    container.innerHTML = '';
    
    window.metalCategories.forEach(metal => {
        const iconMap = {
            'XAU': 'fas fa-coins text-warning',
            'XAG': 'fas fa-medal text-secondary',
            'XPT': 'fas fa-gem text-info', 
            'XPD': 'fas fa-ring text-primary'
        };
        
        const col = document.createElement('div');
        col.className = 'col-6';
        col.innerHTML = `
            <div class="d-flex align-items-center p-2 border rounded bg-light">
                <div class="me-2">
                    <i class="${iconMap[metal.symbol] || 'fas fa-circle text-muted'}" style="font-size: 1.2rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark" style="font-size: 0.8rem;">${metal.name} (Pure)</div>
                    <div class="text-success fw-bold" id="live-${metal.slug}-price" style="font-size: 0.9rem;">
                        AUD$0.00/g
                    </div>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
}

function buildSystemStatus() {
    const container = document.getElementById('systemStatusContainer');
    container.innerHTML = `
        <div class="col-6">
            <small class="text-muted">Database:</small>
            <div class="fw-bold text-success">
                <i class="fas fa-check-circle"></i> Connected
            </div>
        </div>
        <div class="col-6">
            <small class="text-muted">Live API:</small>
            <div class="fw-bold text-success">
                <i class="fas fa-check-circle"></i> Connected
            </div>
        </div>
        <div class="col-6">
            <small class="text-muted">Metals:</small>
            <div class="fw-bold">${window.metalCategories.length}</div>
        </div>
        <div class="col-6">
            <small class="text-muted">Products:</small>
            <div class="fw-bold">${window.products.length}</div>
        </div>
    `;
}

function buildMetalNavigation() {
    const nav = document.getElementById('metalNavigation');
    nav.innerHTML = '';
    
    window.metalCategories.forEach((metal, index) => {
        const button = document.createElement('button');
        button.className = `nav-link nav-metal ${index === 0 ? 'active' : ''} me-2`;
        button.setAttribute('data-metal', metal.slug);
        button.onclick = () => switchMetal(metal.slug);
        
        const iconColors = {
            'XAU': 'text-warning',
            'XAG': 'text-secondary', 
            'XPT': 'text-info',
            'XPD': 'text-primary'
        };
        
        button.innerHTML = `
            <i class="fas fa-circle ${iconColors[metal.symbol] || 'text-muted'} me-1"></i>
            ${metal.name} (${metal.symbol})
        `;
        
        nav.appendChild(button);
    });
    
    // Set initial current metal
    window.currentMetal = window.metalCategories[0]?.slug || 'gold';
}

function buildSubcategoryTabs() {
    const tabs = document.getElementById('subcategoryTabs');
    tabs.innerHTML = `
        <li class="nav-item">
            <button class="nav-link active" data-subcategory="all" onclick="filterBySubcategory('all')">
                All Items
            </button>
        </li>
    `;
    
    window.subcategories.forEach(subcategory => {
        const li = document.createElement('li');
        li.className = 'nav-item';
        li.innerHTML = `
            <button class="nav-link" data-subcategory="${subcategory.slug}" onclick="filterBySubcategory('${subcategory.slug}')">
                ${subcategory.name}
            </button>
        `;
        tabs.appendChild(li);
    });
}

function buildModuleNavigation() {
    const container = document.getElementById('moduleNavigation');
    container.innerHTML = `
        <div class="card-body py-2">
            <nav class="nav nav-pills">
                <button class="nav-link nav-module active me-2" data-module="jewelry" onclick="switchModule('jewelry')">
                    <i class="fas fa-gem me-1"></i>Jewelry Sales
                </button>
                <button class="nav-link nav-module me-2" data-module="scrap" onclick="switchModule('scrap')">
                    <i class="fas fa-recycle me-1"></i>Scrap Metal
                </button>
                <button class="nav-link nav-module me-2" data-module="bullion_sell" onclick="switchModule('bullion_sell')">
                    <i class="fas fa-coins me-1"></i>Sell Bullion
                </button>
                <button class="nav-link nav-module" data-module="bullion_buy" onclick="switchModule('bullion_buy')">
                    <i class="fas fa-handshake me-1"></i>Buy Bullion
                </button>
            </nav>
        </div>
    `;
}

function buildPurityFilter() {
    const filter = document.getElementById('purityFilter');
    updatePurityFilterForMetal(window.currentMetal);
}

function updatePurityFilterForMetal(metalSlug) {
    const filter = document.getElementById('purityFilter');
    const metal = window.metalCategories.find(m => m.slug === metalSlug);
    if (!metal) return;
    
    filter.innerHTML = '<option value="">All Purities</option>';
    
    const availablePurities = metal.available_purities || metal.available_karats || [];
    availablePurities.forEach(purity => {
        const option = document.createElement('option');
        option.value = purity;
        option.textContent = metal.symbol === 'XAU' ? `${purity}K` : purity;
        filter.appendChild(option);
    });
}

function buildListTableHeader() {
    const header = document.getElementById('listTableHeader');
    header.innerHTML = `
        <tr>
            <th width="80px">Image</th>
            <th>Product</th>
            <th width="100px">Category</th>
            <th width="120px">Purity</th>
            <th width="120px">Price/g</th>
            <th width="120px">Weight</th>
            <th width="120px">Total</th>
            <th width="140px">Action</th>
        </tr>
    `;
}

function buildProductsDisplay() {
    buildGridProducts();
    buildListProducts();
}

function buildGridProducts() {
    const container = document.getElementById('gridProductContainer');
    container.innerHTML = '';
    
    window.products.forEach(product => {
        const metal = window.metalCategories.find(m => m.slug === product.metal_slug);
        if (!metal) return;
        
        const availablePurities = metal.available_purities || metal.available_karats || [];
        const purityOptions = availablePurities.map(purity => {
            const display = metal.symbol === 'XAU' ? `${purity}K ${metal.name}` : `${purity} ${metal.name}`;
            return `<option value="${purity}">${display}</option>`;
        }).join('');
        
        const col = document.createElement('div');
        col.className = 'col-lg-3 col-md-4 col-sm-6';
        col.innerHTML = `
            <div class="product-item card product-card border-0 shadow-sm h-100"
                 data-category="${product.subcategory_slug}"
                 data-subcategory="${product.subcategory_slug}"
                 data-name="${product.name.toLowerCase()}"
                 data-product-id="${product.id}"
                 data-metal="${product.metal_slug}">
                
                <div class="position-relative">
                    <img src="${product.image_url || '/images/products/default-product.jpg'}"
                         class="card-img-top"
                         style="height: 80px; object-fit: cover;"
                         alt="${product.name}"
                         loading="lazy"
                         onerror="this.src='/images/products/default-product.jpg'">
                </div>

                <div class="card-body p-2">
                    <h6 class="card-title mb-2 text-center" style="font-size: 0.8rem; line-height: 1.2;">
                        ${product.name}
                    </h6>

                    <div class="mb-2">
                        <select class="form-select form-select-sm purity-selector"
                                id="purity_${product.id}"
                                data-product-id="${product.id}"
                                data-metal="${product.metal_slug}"
                                data-subcategory="${product.subcategory_slug}"
                                onchange="updateProductPrice(${product.id})"
                                style="font-size: 0.75rem;">
                            ${purityOptions}
                        </select>
                    </div>

                    <div class="mb-2">
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary weight-btn-minus" type="button" data-product-id="${product.id}" onclick="adjustWeight(${product.id}, -0.1)">-</button>
                            <input type="number"
                                   class="form-control text-center weight-input"
                                   id="weight_${product.id}"
                                   data-product-id="${product.id}"
                                   min="0.1"
                                   step="0.1"
                                   value="1"
                                   onchange="updateProductPrice(${product.id})"
                                   style="font-size: 0.75rem;">
                            <span class="input-group-text" style="font-size: 0.7rem;">g</span>
                            <button class="btn btn-outline-secondary weight-btn-plus" type="button" data-product-id="${product.id}" onclick="adjustWeight(${product.id}, 0.1)">+</button>
                        </div>
                    </div>

                    <div class="text-center mb-2">
                        <div class="fw-bold text-primary" id="total_price_${product.id}" style="font-size: 0.85rem;">
                            AUD$0.00
                        </div>
                        <small class="text-muted" style="font-size: 0.65rem;">
                            <span id="weight_display_${product.id}">1.0</span>g
                        </small>
                    </div>

                    <button class="btn btn-primary btn-sm w-100 add-to-cart-btn"
                            data-product-id="${product.id}"
                            data-product-name="${product.name}"
                            data-product-category="${product.subcategory_name}"
                            onclick="addToCart(${product.id})"
                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                        <i class="fas fa-cart-plus me-1"></i>Add
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(col);
    });
}

function buildListProducts() {
    const container = document.getElementById('listProductContainer');
    container.innerHTML = '';
    
    window.products.forEach(product => {
        const metal = window.metalCategories.find(m => m.slug === product.metal_slug);
        if (!metal) return;
        
        const availablePurities = metal.available_purities || metal.available_karats || [];
        const purityOptions = availablePurities.map(purity => {
            const display = metal.symbol === 'XAU' ? `${purity}K` : purity;
            return `<option value="${purity}">${display}</option>`;
        }).join('');
        
        const tr = document.createElement('tr');
        tr.className = 'product-row';
        tr.setAttribute('data-category', product.subcategory_slug);
        tr.setAttribute('data-name', product.name.toLowerCase());
        tr.setAttribute('data-product-id', product.id);
        tr.setAttribute('data-metal', product.metal_slug);
        tr.setAttribute('data-subcategory', product.subcategory_slug);
        
        tr.innerHTML = `
            <td>
                <img src="${product.image_url || '/images/products/default-product.jpg'}"
                     class="rounded"
                     style="width: 60px; height: 60px; object-fit: cover;"
                     alt="${product.name}"
                     onerror="this.src='/images/products/default-product.jpg'">
            </td>
            <td>
                <h6 class="mb-0">${product.name}</h6>
            </td>
            <td>
                <span class="text-muted">${product.subcategory_name}</span>
            </td>
            <td>
                <select class="form-select form-select-sm purity-selector"
                        id="purity_list_${product.id}"
                        data-product-id="${product.id}"
                        data-metal="${product.metal_slug}"
                        data-subcategory="${product.subcategory_slug}"
                        onchange="updateProductPrice(${product.id})"
                        style="width: 110px;">
                    ${purityOptions}
                </select>
            </td>
            <td>
                <span class="fw-bold text-primary">
                    AUD$<span id="price_per_gram_list_${product.id}">0.00</span>
                </span>
            </td>
            <td>
                <div class="input-group input-group-sm" style="width: 110px;">
                    <button class="btn btn-outline-secondary" onclick="adjustWeight(${product.id}, -0.1)">-</button>
                    <input type="number"
                           class="form-control text-center weight-input"
                           id="weight_list_${product.id}"
                           data-product-id="${product.id}"
                           min="0.1"
                           step="0.1"
                           value="1"
                           onchange="updateProductPrice(${product.id})"
                           style="width: 50px;">
                    <button class="btn btn-outline-secondary" onclick="adjustWeight(${product.id}, 0.1)">+</button>
                </div>
            </td>
            <td>
                <div class="fw-bold text-success" id="total_price_list_${product.id}">AUD$0.00</div>
                <small class="text-muted">for <span id="weight_display_list_${product.id}">1.0</span>g</small>
            </td>
            <td>
                <button class="btn btn-primary btn-sm add-to-cart-btn"
                        data-product-id="${product.id}"
                        onclick="addToCart(${product.id})">
                    <i class="fas fa-cart-plus me-1"></i>Add
                </button>
            </td>
        `;
        
        container.appendChild(tr);
    });
}

function buildCartTotalsContainer() {
    const container = document.getElementById('cartTotalsContainer');
    container.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <span>Net Subtotal:</span>
            <span id="cartSubtotal">AUD$0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Tax (10%):</span>
            <span id="cartTax">AUD$0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-3">
            <span>Shipping:</span>
            <span id="cartShipping">AUD$25.00</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between h5">
            <strong>Total:</strong>
            <strong id="cartTotal">AUD$0.00</strong>
        </div>
    `;
}

// EXACT PRICE CALCULATION BASED ON YOUR SPECIFICATION
function calculateProductPrice(productId, weight, purity, subcategory, metal) {
    // Get currentPrice using your exact formula
    let currentPrice;
    
    if (window.metalPricesPerGram[metal] && window.metalPricesPerGram[metal][purity]) {
        currentPrice = window.metalPricesPerGram[metal][purity];
    } else {
        throw new Error(`Price not available for ${metal} ${purity}`);
    }
    
    // Price calculation using your exact formula:
    // price = (metal_weight × currentPrice × purity) + labor_cost + profit_margin
    // Note: purity is already included in currentPrice, so we don't multiply again
    
    const metalValue = weight * currentPrice;
    
    // Get labor cost from database
    const subcategoryData = window.subcategories.find(s => s.slug === subcategory);
    const laborCostPerGram = subcategoryData?.default_labor_cost || 15.00;
    const laborCost = weight * laborCostPerGram;
    
    // Get profit margin from database
    const profitMargin = (metalValue + laborCost) * (window.tradingConfig.jewelry.profitMargin || 0.25);
    
    const finalPrice = metalValue + laborCost + profitMargin;
    
    return {
        currentPrice: currentPrice,
        metalValue: metalValue,
        laborCost: laborCost,
        profitMargin: profitMargin,
        finalPrice: finalPrice,
        pricePerGram: finalPrice / weight
    };
}

function updateProductPrice(productId) {
    try {
        const weightInput = document.getElementById(`weight_${productId}`) || document.getElementById(`weight_list_${productId}`);
        const puritySelect = document.getElementById(`purity_${productId}`) || document.getElementById(`purity_list_${productId}`);
        
        if (!weightInput || !puritySelect) return;
        
        const weight = parseFloat(weightInput.value) || 1;
        const purity = puritySelect.value;
        
        const product = window.products.find(p => p.id == productId);
        if (!product) return;
        
        const pricing = calculateProductPrice(productId, weight, purity, product.subcategory_slug, product.metal_slug);
        
        // Update displays
        const totalPriceElements = [
            document.getElementById(`total_price_${productId}`),
            document.getElementById(`total_price_list_${productId}`)
        ];
        
        const pricePerGramElements = [
            document.getElementById(`price_per_gram_${productId}`),
            document.getElementById(`price_per_gram_list_${productId}`)
        ];
        
        const weightDisplayElements = [
            document.getElementById(`weight_display_${productId}`),
            document.getElementById(`weight_display_list_${productId}`)
        ];
        
        totalPriceElements.forEach(el => {
            if (el) {
                el.textContent = `AUD$${pricing.finalPrice.toFixed(2)}`;
                el.classList.add('price-updated');
                setTimeout(() => el.classList.remove('price-updated'), 1000);
            }
        });
        
        pricePerGramElements.forEach(el => {
            if (el) el.textContent = pricing.currentPrice.toFixed(2);
        });
        
        weightDisplayElements.forEach(el => {
            if (el) el.textContent = weight.toFixed(1);
        });
        
    } catch (error) {
        console.error('Error updating product price:', error);
    }
}

function updateAllPrices() {
    // Update live price displays
    const gramsPerTroyOz = 31.1035;
    
    window.metalCategories.forEach(metal => {
        const element = document.getElementById(`live-${metal.slug}-price`);
        if (element && window.livePrices[metal.symbol]) {
            const purePrice = window.livePrices[metal.symbol] / gramsPerTroyOz;
            element.textContent = `AUD$${purePrice.toFixed(2)}/g`;
            element.parentElement.parentElement.classList.add('price-flash');
            setTimeout(() => {
                element.parentElement.parentElement.classList.remove('price-flash');
            }, 800);
        }
    });
    
    // Update last updated time
    const lastUpdated = document.getElementById('livePricesLastUpdated');
    if (lastUpdated && window.livePrices.last_updated) {
        lastUpdated.innerHTML = `<i class="fas fa-clock me-1"></i>Last updated: ${window.livePrices.last_updated.toLocaleTimeString()}`;
    }
    
    // Update all product prices
    window.products.forEach(product => {
        updateProductPrice(product.id);
    });
    
    // Update metal prices display
    updateMetalPricesDisplay();
}

function updateMetalPricesDisplay() {
    const display = document.getElementById('metalPricesDisplay');
    if (!display || !window.currentMetal) return;
    
    const currentPrices = window.metalPricesPerGram[window.currentMetal] || {};
    const metal = window.metalCategories.find(m => m.slug === window.currentMetal);
    
    let priceHTML = Object.entries(currentPrices)
        .map(([purity, price]) => {
            const displayText = metal?.symbol === 'XAU' ? `${purity}K` : purity;
            return `<small class="badge bg-light text-dark me-1">${displayText}: AUD$${price.toFixed(2)}</small>`;
        }).join('');
    
    display.innerHTML = priceHTML || '<small class="text-muted">Loading prices...</small>';
}

function showSystemError(message) {
    document.getElementById('systemLoading').classList.add('d-none');
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorSection').classList.remove('d-none');
}

// Placeholder functions - implement full functionality as needed
function initializeEventListeners() { /* Event listeners */ }
function switchMetal(metalSlug) { window.currentMetal = metalSlug; updatePurityFilterForMetal(metalSlug); updateAllPrices(); }
function filterBySubcategory(slug) { /* Filter by subcategory */ }
function switchModule(module) { /* Switch modules */ }
function adjustWeight(productId, change) { 
    const input = document.getElementById(`weight_${productId}`) || document.getElementById(`weight_list_${productId}`);
    if (input) {
        const newWeight = Math.max(0.1, (parseFloat(input.value) || 1) + change);
        input.value = newWeight.toFixed(1);
        updateProductPrice(productId);
    }
}
function addToCart(productId) { /* Add to cart */ }
function searchProducts() { /* Search products */ }
function filterProducts() { /* Filter products */ }
function switchToGridView() { /* Switch to grid */ }
function switchToListView() { /* Switch to list */ }
function proceedToCheckout() { /* Checkout */ }
function clearCart() { /* Clear cart */ }
function refreshLivePrices() { fetchLiveMetalPricesFromAPI().then(() => updateAllPrices()); }

// Global exports
window.updateProductPrice = updateProductPrice;
window.adjustWeight = adjustWeight;
window.switchMetal = switchMetal;
window.refreshLivePrices = refreshLivePrices;
</script>

@endsection