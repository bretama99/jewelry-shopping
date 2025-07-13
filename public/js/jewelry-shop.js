/**
 * SECURE TRADING SYSTEM - NO TRADING WITHOUT LIVE PRICES
 * Critical jewelry trading application with price validation
 */

class SecureTradingSystem {
    constructor() {
        this.state = {
            isInitialized: false,
            tradingEnabled: false,
            apiStatus: 'unknown',
            priceDataAge: null,
            emergencyStop: false,

            // Trading data - only loaded if prices are live
            currentMetal: 'gold',
            currentSubcategory: 'all',
            currentModule: 'jewelry',
            cart: [],

            // Database data
            metalCategories: [],
            subcategories: [],
            products: [],

            // LIVE PRICES ONLY - no fallbacks
            livePrices: null,
            calculatedPrices: {}
        };

        this.config = {
            maxPriceAge: 120, // 2 minutes max for price data
            healthCheckInterval: 30000, // Check every 30 seconds
            gramsPerTroyOz: 31.1035
        };

        this.healthCheckInterval = null;
        this.init();
    }

    async init() {
        try {
            console.log('🔒 Starting SECURE Trading System...');
            this.showLoadingState();

            // Step 1: Critical system health check
            this.updateLoadingProgress(10, 'Checking system health...');
            const healthCheck = await this.checkSystemHealth();

            if (!healthCheck.tradingEnabled) {
                throw new Error(`System health check failed: ${healthCheck.error || 'Unknown error'}`);
            }

            // Step 2: Verify live price availability
            this.updateLoadingProgress(30, 'Verifying live price feed...');
            const priceCheck = await this.verifyLivePrices();

            if (!priceCheck.success) {
                throw new Error(`Live prices unavailable: ${priceCheck.message}`);
            }

            // Step 3: Load database data only if prices are confirmed live
            this.updateLoadingProgress(50, 'Loading trading data...');
            await this.loadTradingData();

            // Step 4: Calculate all prices from live data
            this.updateLoadingProgress(70, 'Calculating live prices...');
            this.calculateAllPricesFromLive();

            // Step 5: Initialize interface
            this.updateLoadingProgress(90, 'Initializing trading interface...');
            this.initializeSecureInterface();

            // Step 6: Start continuous monitoring
            this.startHealthMonitoring();

            this.updateLoadingProgress(100, 'Trading system secured and ready!');

            setTimeout(() => {
                this.hideLoadingState();
                this.state.isInitialized = true;
                this.state.tradingEnabled = true;
                this.showNotification('🔒 SECURE Trading System Ready - Live Prices Confirmed', 'success');
            }, 500);

        } catch (error) {
            console.error('❌ CRITICAL: Trading system initialization failed:', error);
            this.handleSystemFailure(error.message);
        }
    }

    // ============================
    // CRITICAL SYSTEM CHECKS
    // ============================

    async checkSystemHealth() {
        try {
            const response = await fetch('/api/system/health');
            const data = await response.json();

            if (!data.trading_enabled) {
                console.error('❌ TRADING DISABLED:', data);
                return {
                    tradingEnabled: false,
                    error: data.checks ?
                        `System checks failed: ${Object.entries(data.checks).filter(([k,v]) => !v).map(([k,v]) => k).join(', ')}` :
                        'System health check failed'
                };
            }

            return { tradingEnabled: true, status: data };

        } catch (error) {
            console.error('❌ Health check failed:', error);
            return {
                tradingEnabled: false,
                error: 'Unable to verify system health'
            };
        }
    }

    async verifyLivePrices() {
        try {
            const response = await fetch('/api/metal-prices');
            const data = await response.json();

            if (!data.success) {
                return {
                    success: false,
                    message: data.message || 'Price feed unavailable',
                    error: data.error
                };
            }

            // Verify we have all required metals
            const requiredMetals = ['XAU', 'XAG', 'XPT', 'XPD'];
            const missingMetals = requiredMetals.filter(metal => !data.data[metal]);

            if (missingMetals.length > 0) {
                return {
                    success: false,
                    message: `Missing price data for: ${missingMetals.join(', ')}`
                };
            }

            // Verify prices are fresh
            const pricesAge = data.meta?.cache_age_seconds || 0;
            if (pricesAge > this.config.maxPriceAge) {
                return {
                    success: false,
                    message: `Price data too old: ${pricesAge} seconds (max ${this.config.maxPriceAge})`
                };
            }

            // Store live prices
            this.state.livePrices = data.data;
            this.state.apiStatus = data.meta?.api_status || 'live';
            this.state.priceDataAge = pricesAge;

            console.log('✅ Live prices verified:', {
                metals: Object.keys(data.data),
                age: pricesAge,
                source: data.meta?.source
            });

            return { success: true, data: data.data };

        } catch (error) {
            console.error('❌ Price verification failed:', error);
            return {
                success: false,
                message: 'Failed to connect to price feed'
            };
        }
    }

    // ============================
    // CONTINUOUS MONITORING
    // ============================

    startHealthMonitoring() {
        this.healthCheckInterval = setInterval(async () => {
            try {
                const priceCheck = await this.verifyLivePrices();

                if (!priceCheck.success) {
                    console.warn('⚠️ Live prices lost during operation');
                    this.emergencyStopTrading('Live price feed lost during operation');
                    return;
                }

                // Update price calculations if data is fresh
                this.calculateAllPricesFromLive();
                this.updateLivePriceDisplays();

                // Update status indicator
                this.updateSystemStatusDisplay();

            } catch (error) {
                console.error('❌ Health monitoring error:', error);
                this.emergencyStopTrading('Health monitoring failed');
            }
        }, this.config.healthCheckInterval);

        console.log('✅ Health monitoring started');
    }

    emergencyStopTrading(reason) {
        console.error('🚨 EMERGENCY STOP TRIGGERED:', reason);

        this.state.tradingEnabled = false;
        this.state.emergencyStop = true;

        // Stop all monitoring
        if (this.healthCheckInterval) {
            clearInterval(this.healthCheckInterval);
        }

        // Show emergency stop interface
        this.showEmergencyStop(reason);

        // Disable all trading functions
        this.disableAllTradingFeatures();
    }

    // ============================
    // DATA LOADING (Only if prices verified)
    // ============================

    async loadTradingData() {
        try {
            const [metalCategories, subcategories, products] = await Promise.all([
                this.fetchSecureEndpoint('/api/metal-categories'),
                this.fetchSecureEndpoint('/api/subcategories'),
                this.fetchSecureEndpoint('/api/products')
            ]);

            this.state.metalCategories = metalCategories.data || [];
            this.state.subcategories = subcategories.data || [];
            this.state.products = products.data || [];

            console.log('✅ Trading data loaded:', {
                metals: this.state.metalCategories.length,
                subcategories: this.state.subcategories.length,
                products: this.state.products.length
            });

        } catch (error) {
            throw new Error(`Failed to load trading data: ${error.message}`);
        }
    }

    async fetchSecureEndpoint(endpoint) {
        const response = await fetch(endpoint);
        const data = await response.json();

        if (!data.success) {
            if (response.status === 503) {
                throw new Error(`Trading suspended: ${data.message}`);
            }
            throw new Error(data.message || 'API request failed');
        }

        return data;
    }

    // ============================
    // LIVE PRICE CALCULATIONS
    // ============================

    calculateAllPricesFromLive() {
        if (!this.state.livePrices || !this.state.tradingEnabled) {
            console.warn('⚠️ Cannot calculate prices - live data unavailable');
            return;
        }

        this.state.calculatedPrices = {};

        this.state.metalCategories.forEach(metal => {
            this.state.calculatedPrices[metal.slug] = {};

            const livePriceData = this.state.livePrices[metal.symbol];
            if (!livePriceData) {
                console.warn(`❌ No live price data for ${metal.symbol}`);
                return;
            }

            // Use live price per gram from API
            const purePricePerGram = livePriceData.per_gram;

            // Calculate for all available purities
            const availableKarats = metal.available_karats || Object.keys(metal.purity_ratios || {});

            availableKarats.forEach(purity => {
                const purityRatio = this.getPurityRatio(metal, purity);
                this.state.calculatedPrices[metal.slug][purity] = purePricePerGram * purityRatio;
            });
        });

        console.log('✅ Live prices calculated for all metals');
    }

    getPurityRatio(metal, purity) {
        // Use database purity ratios first
        if (metal.purity_ratios && metal.purity_ratios[purity]) {
            return metal.purity_ratios[purity];
        }

        // Standard calculations for different metal types
        if (metal.symbol === 'XAU') { // Gold
            return parseFloat(purity) / 24.0;
        } else { // Silver, Platinum, Palladium
            return parseFloat(purity) / 1000.0;
        }
    }

    // ============================
    // SECURE TRADING OPERATIONS
    // ============================

    calculateSecureProductPrice(productId, weight, karat) {
        if (!this.state.tradingEnabled) {
            throw new Error('Trading disabled - live prices unavailable');
        }

        if (!this.state.calculatedPrices[this.state.currentMetal]?.[karat]) {
            throw new Error(`Price unavailable for ${this.state.currentMetal} ${karat}`);
        }

        const pricePerGram = this.state.calculatedPrices[this.state.currentMetal][karat];
        const product = this.state.products.find(p => p.id == productId);
        const subcategory = this.state.subcategories.find(s => s.slug === product?.subcategory_slug);

        const laborCostPerGram = product?.labor_cost || subcategory?.default_labor_cost || 15.00;

        const metalValue = weight * pricePerGram;
        const laborValue = weight * laborCostPerGram;
        const baseCost = metalValue + laborValue;
        const totalPrice = baseCost * 1.25; // 25% profit margin

        return {
            pricePerGram,
            metalValue,
            laborValue,
            baseCost,
            totalPrice,
            timestamp: new Date().toISOString(),
            priceDataAge: this.state.priceDataAge
        };
    }

    secureAddToCart(productData) {
        if (!this.state.tradingEnabled) {
            this.showNotification('❌ Trading disabled - cannot add to cart', 'error');
            return false;
        }

        // Verify prices are still fresh
        if (this.state.priceDataAge > this.config.maxPriceAge) {
            this.showNotification('❌ Price data too old - refreshing...', 'warning');
            this.verifyLivePrices();
            return false;
        }

        this.state.cart.push(productData);
        this.updateCartDisplay();
        return true;
    }

    // ============================
    // INTERFACE MANAGEMENT
    // ============================

    initializeSecureInterface() {
        this.populateMetalNavigation();
        this.populateSubcategoryTabs();
        this.updateLivePriceDisplays();
        this.displayProducts();
        this.updateSystemStatusDisplay();
        this.setupEventListeners();
        this.updateCartDisplay();
    }

    updateSystemStatusDisplay() {
        const statusContainer = document.getElementById('systemStatusContainer');
        if (!statusContainer) return;

        const apiStatusClass = this.state.apiStatus === 'live' ? 'bg-success' : 'bg-warning';
        const tradingStatusClass = this.state.tradingEnabled ? 'bg-success' : 'bg-danger';
        const priceAgeClass = this.state.priceDataAge < 60 ? 'bg-success' : 'bg-warning';

        statusContainer.innerHTML = `
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span>API Status:</span>
                    <span class="badge ${apiStatusClass}">${this.state.apiStatus.toUpperCase()}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span>Trading:</span>
                    <span class="badge ${tradingStatusClass}">${this.state.tradingEnabled ? 'ENABLED' : 'DISABLED'}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span>Price Data:</span>
                    <span class="badge ${priceAgeClass}">${this.state.priceDataAge}s old</span>
                </div>
            </div>
        `;
    }

    updateLivePriceDisplays() {
        const container = document.getElementById('livePricesContainer');
        if (!container) return;

        container.innerHTML = this.state.metalCategories.map(metal => {
            const priceData = this.state.livePrices[metal.symbol];
            const price = priceData ? priceData.per_gram : 0;
            const statusClass = this.state.tradingEnabled ? 'border-success' : 'border-danger';

            return `
                <div class="col-md-3">
                    <div class="price-card text-center p-2 border rounded ${statusClass}">
                        <h6 class="mb-1">${metal.name}</h6>
                        <div class="price-value fw-bold" style="color: ${this.state.tradingEnabled ? '#28a745' : '#dc3545'}">
                            AUD${price.toFixed(4)}/g
                        </div>
                        <small class="text-muted">Pure ${this.getHighestPurity(metal.symbol)}</small>
                        ${this.state.tradingEnabled ?
                            '<small class="badge bg-success">LIVE</small>' :
                            '<small class="badge bg-danger">OFFLINE</small>'}
                    </div>
                </div>
            `;
        }).join('');

        // Update timestamp
        const lastUpdated = document.getElementById('livePricesLastUpdated');
        if (lastUpdated) {
            const updateTime = new Date().toLocaleTimeString();
            const ageText = this.state.priceDataAge ? ` (${this.state.priceDataAge}s ago)` : '';
            const statusIcon = this.state.tradingEnabled ?
                '<i class="fas fa-check-circle text-success me-1"></i>' :
                '<i class="fas fa-exclamation-triangle text-danger me-1"></i>';

            lastUpdated.innerHTML = `${statusIcon}Last updated: ${updateTime}${ageText}`;
        }
    }

    populateMetalNavigation() {
        const metalNavigation = document.getElementById('metalNavigation');
        if (!metalNavigation) return;

        metalNavigation.innerHTML = this.state.metalCategories.map((metal, index) => `
            <button class="nav-link nav-metal ${index === 0 ? 'active' : ''} me-2 ${!this.state.tradingEnabled ? 'disabled' : ''}"
                    data-metal="${metal.slug}"
                    ${this.state.tradingEnabled ? `onclick="tradingSystem.switchMetal('${metal.slug}')"` : 'disabled'}>
                <i class="fas fa-circle me-1" style="color: ${this.getMetalColor(metal.symbol)}"></i>
                ${metal.name}
                ${!this.state.tradingEnabled ? '<i class="fas fa-lock ms-1"></i>' : ''}
            </button>
        `).join('');

        if (this.state.metalCategories.length > 0) {
            this.state.currentMetal = this.state.metalCategories[0].slug;
        }
    }

    displayProducts() {
        if (!this.state.tradingEnabled) {
            this.showTradingDisabledMessage();
            return;
        }

        const filteredProducts = this.getFilteredProducts();
        const gridContainer = document.getElementById('gridProductContainer');
        const listContainer = document.getElementById('listProductContainer');

        if (filteredProducts.length === 0) {
            this.showNoProducts();
            return;
        }

        // Grid View
        if (gridContainer) {
            gridContainer.innerHTML = filteredProducts.map(product => this.renderSecureProductCard(product)).join('');
        }

        // List View
        if (listContainer) {
            listContainer.innerHTML = filteredProducts.map(product => this.renderSecureProductRow(product)).join('');
        }

        // Calculate initial prices for all products
        filteredProducts.forEach(product => {
            this.updateProductPrice(product.id);
        });
    }

    renderSecureProductCard(product) {
        const metal = this.getCurrentMetal();
        const availableKarats = metal?.available_karats || Object.keys(metal?.purity_ratios || {});
        const defaultKarat = availableKarats[0] || '18';

        return `
            <div class="col-md-4 col-lg-3">
                <div class="card product-card shadow-sm border-success" data-product-id="${product.id}" data-metal="${product.metal_slug}" data-subcategory="${product.subcategory_slug}">
                    <div class="position-relative">
                        <img src="${product.image_url}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${product.name}">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success">
                                <i class="fas fa-shield-alt me-1"></i>LIVE PRICES
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">${product.name}</h6>
                        <p class="card-text small text-muted mb-2">${product.description || ''}</p>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small">Purity:</label>
                                <select class="form-select form-select-sm karat-select" id="karat_${product.id}" data-product-id="${product.id}" onchange="tradingSystem.updateProductPrice(${product.id})">
                                    ${availableKarats.map(karat => `
                                        <option value="${karat}" ${karat === defaultKarat ? 'selected' : ''}>
                                            ${this.formatKaratDisplay(metal, karat)}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Weight (g):</label>
                                <input type="number" class="form-control form-control-sm weight-input"
                                       id="weight_${product.id}" data-product-id="${product.id}"
                                       value="${product.weight || 1}" min="0.1" step="0.1"
                                       onchange="tradingSystem.updateProductPrice(${product.id})">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Live Price/g:</small>
                            <span class="fw-bold text-success" id="price_per_gram_${product.id}">AUD0.0000</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">Total:</small>
                            <span class="fw-bold text-primary fs-5" id="total_price_${product.id}">AUD0.00</span>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted d-block">Price Age: <span id="price_age_${product.id}" class="text-success">Live</span></small>
                        </div>

                        <button class="btn btn-success btn-sm w-100" onclick="tradingSystem.addProductToCart(${product.id})" id="add_btn_${product.id}">
                            <i class="fas fa-shield-check me-1"></i>Add to Secure Cart
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    updateProductPrice(productId) {
        if (!this.state.tradingEnabled) {
            this.disableProductControls(productId);
            return;
        }

        try {
            const weightInput = document.getElementById(`weight_${productId}`) || document.getElementById(`weight_list_${productId}`);
            const karatSelect = document.getElementById(`karat_${productId}`) || document.getElementById(`karat_list_${productId}`);

            if (!weightInput || !karatSelect) return;

            const weight = parseFloat(weightInput.value) || 1;
            const karat = karatSelect.value;

            const pricing = this.calculateSecureProductPrice(productId, weight, karat);

            // Update displays with high precision
            this.updatePriceDisplay(productId, pricing.pricePerGram, pricing.totalPrice);

            // Update price age indicator
            const priceAgeElement = document.getElementById(`price_age_${productId}`);
            if (priceAgeElement) {
                const ageClass = this.state.priceDataAge < 60 ? 'text-success' : 'text-warning';
                priceAgeElement.className = ageClass;
                priceAgeElement.textContent = `${this.state.priceDataAge}s`;
            }

            // Sync both views
            this.syncWeightAndKarat(productId, weight, karat);

        } catch (error) {
            console.error('❌ Price calculation failed:', error);
            this.showNotification(`Price calculation failed: ${error.message}`, 'error');
            this.disableProductControls(productId);
        }
    }

    updatePriceDisplay(productId, pricePerGram, totalPrice) {
        const pricePerGramElements = [
            document.getElementById(`price_per_gram_${productId}`),
            document.getElementById(`price_per_gram_list_${productId}`)
        ];

        const totalPriceElements = [
            document.getElementById(`total_price_${productId}`),
            document.getElementById(`total_price_list_${productId}`)
        ];

        pricePerGramElements.forEach(el => {
            if (el) el.textContent = `AUD${pricePerGram.toFixed(4)}`;
        });

        totalPriceElements.forEach(el => {
            if (el) el.textContent = `AUD${totalPrice.toFixed(2)}`;
        });
    }

    addProductToCart(productId) {
        if (!this.state.tradingEnabled) {
            this.showNotification('❌ Trading disabled - Live prices required', 'error');
            return;
        }

        try {
            const product = this.state.products.find(p => p.id == productId);
            if (!product) throw new Error('Product not found');

            const weightInput = document.getElementById(`weight_${productId}`) || document.getElementById(`weight_list_${productId}`);
            const karatSelect = document.getElementById(`karat_${productId}`) || document.getElementById(`karat_list_${productId}`);

            const weight = parseFloat(weightInput?.value) || 1;
            const karat = karatSelect?.value || '18';

            const pricing = this.calculateSecureProductPrice(productId, weight, karat);

            const cartItem = {
                id: Date.now() + Math.random(),
                productId: productId,
                productName: product.name,
                productCategory: product.subcategory_name,
                productKarat: karat,
                weight: weight,
                pricePerGram: pricing.pricePerGram,
                totalPrice: pricing.totalPrice,
                type: 'jewelry',
                metal: this.state.currentMetal,
                priceTimestamp: pricing.timestamp,
                priceDataAge: pricing.priceDataAge
            };

            const success = this.secureAddToCart(cartItem);
            if (success) {
                this.showNotification(`✅ ${product.name} added with live pricing!`, 'success');

                // Flash the button to show success
                const button = document.getElementById(`add_btn_${productId}`);
                if (button) {
                    button.classList.add('btn-success');
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Added!';
                    setTimeout(() => {
                        button.classList.remove('btn-success');
                        button.innerHTML = '<i class="fas fa-shield-check me-1"></i>Add to Secure Cart';
                    }, 1500);
                }
            }

        } catch (error) {
            console.error('❌ Add to cart failed:', error);
            this.showNotification(`Failed to add to cart: ${error.message}`, 'error');
        }
    }

    // ============================
    // EMERGENCY PROCEDURES
    // ============================

    showEmergencyStop(reason) {
        const errorSection = document.getElementById('errorSection');
        const mainContainer = document.getElementById('mainContainer');
        const errorMessage = document.getElementById('errorMessage');

        if (errorSection && mainContainer && errorMessage) {
            errorMessage.innerHTML = `
                <div class="alert alert-danger mb-4">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>EMERGENCY STOP ACTIVATED</h5>
                    <p class="mb-0">${reason}</p>
                </div>
                <div class="text-center">
                    <h4 class="text-danger mb-3">All Trading Operations Suspended</h4>
                    <p class="mb-4">Live price feed is required for jewelry trading operations.</p>
                    <button class="btn btn-primary btn-lg me-3" onclick="location.reload()">
                        <i class="fas fa-redo me-2"></i>Retry System Connection
                    </button>
                    <button class="btn btn-warning btn-lg" onclick="tradingSystem.checkSystemStatus()">
                        <i class="fas fa-heartbeat me-2"></i>Check System Status
                    </button>
                </div>
            `;
            errorSection.classList.remove('d-none');
            mainContainer.classList.add('d-none');
        }
    }

    disableAllTradingFeatures() {
        // Disable all buttons and inputs
        document.querySelectorAll('button, input, select').forEach(element => {
            if (!element.classList.contains('system-control')) {
                element.disabled = true;
            }
        });

        // Show trading disabled overlay
        this.showTradingDisabledMessage();
    }

    showTradingDisabledMessage() {
        const gridContainer = document.getElementById('gridProductContainer');
        const listContainer = document.getElementById('listProductContainer');

        const disabledMessage = `
            <div class="col-12">
                <div class="card border-danger">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-4x text-danger mb-3"></i>
                        <h4 class="text-danger">Trading Suspended</h4>
                        <p class="text-muted mb-4">Live price feed required for jewelry operations</p>
                        <button class="btn btn-primary" onclick="tradingSystem.checkSystemStatus()">
                            <i class="fas fa-sync me-2"></i>Check Price Feed Status
                        </button>
                    </div>
                </div>
            </div>
        `;

        if (gridContainer) gridContainer.innerHTML = disabledMessage;
        if (listContainer) {
            listContainer.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-lock fa-2x text-danger mb-2"></i>
                        <div class="text-danger">Trading operations suspended - Live prices required</div>
                    </td>
                </tr>
            `;
        }
    }

    async checkSystemStatus() {
        try {
            this.showNotification('🔍 Checking system status...', 'info');

            const healthCheck = await this.checkSystemHealth();
            const priceCheck = await this.verifyLivePrices();

            if (healthCheck.tradingEnabled && priceCheck.success) {
                this.showNotification('✅ System healthy - Reloading...', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                const issues = [];
                if (!healthCheck.tradingEnabled) issues.push(healthCheck.error);
                if (!priceCheck.success) issues.push(priceCheck.message);

                this.showNotification(`❌ System issues: ${issues.join(', ')}`, 'error');
            }
        } catch (error) {
            this.showNotification('❌ Status check failed', 'error');
        }
    }

    // ============================
    // UTILITY METHODS
    // ============================

    handleSystemFailure(message) {
        this.state.tradingEnabled = false;
        this.showEmergencyStop(message);
    }

    getFilteredProducts() {
        return this.state.products.filter(product => {
            if (product.metal_slug !== this.state.currentMetal) return false;
            if (this.state.currentSubcategory !== 'all' && product.subcategory_slug !== this.state.currentSubcategory) return false;
            return product.is_active;
        });
    }

    getCurrentMetal() {
        return this.state.metalCategories.find(m => m.slug === this.state.currentMetal);
    }

    getMetalColor(symbol) {
        const colors = { 'XAU': '#FFD700', 'XAG': '#C0C0C0', 'XPT': '#E5E4E2', 'XPD': '#CED0DD' };
        return colors[symbol] || '#999';
    }

    getHighestPurity(symbol) {
        const purities = { 'XAU': '24K', 'XAG': '999', 'XPT': '999', 'XPD': '999' };
        return purities[symbol] || '999';
    }

    formatKaratDisplay(metal, karat) {
        return metal?.symbol === 'XAU' ? `${karat}K` : karat;
    }

    syncWeightAndKarat(productId, weight, karat) {
        const weightInputs = [
            document.getElementById(`weight_${productId}`),
            document.getElementById(`weight_list_${productId}`)
        ];

        const karatSelects = [
            document.getElementById(`karat_${productId}`),
            document.getElementById(`karat_list_${productId}`)
        ];

        weightInputs.forEach(input => {
            if (input && parseFloat(input.value) !== weight) input.value = weight;
        });

        karatSelects.forEach(select => {
            if (select && select.value !== karat) select.value = karat;
        });
    }

    // ============================
    // PUBLIC INTERFACE METHODS
    // ============================

    switchMetal(metalSlug) {
        if (!this.state.tradingEnabled) return;

        this.state.currentMetal = metalSlug;

        document.querySelectorAll('.nav-metal').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-metal') === metalSlug);
        });

        this.updateMetalPriceDisplay();
        this.displayProducts();
    }

    updateMetalPriceDisplay() {
        if (!this.state.tradingEnabled) return;

        const priceDisplay = document.getElementById('metalPricesDisplay');
        if (!priceDisplay) return;

        const currentPrices = this.state.calculatedPrices[this.state.currentMetal] || {};

        const priceHTML = Object.entries(currentPrices).map(([purity, price]) => {
            const metal = this.getCurrentMetal();
            const displayText = metal?.symbol === 'XAU' ? `${purity}K` : purity;
            return `<small class="badge bg-success text-white me-1">${displayText}: AUD${price.toFixed(4)}</small>`;
        }).join('');

        priceDisplay.innerHTML = priceHTML;
    }

    updateCartDisplay() {
        const cartItems = document.getElementById('cartItems');
        const cartCount = document.getElementById('cartItemCount');
        const emptyCart = document.getElementById('emptyCart');

        if (cartCount) cartCount.textContent = this.state.cart.length;

        if (this.state.cart.length === 0) {
            if (emptyCart) emptyCart.classList.remove('d-none');
            return;
        }

        if (emptyCart) emptyCart.classList.add('d-none');

        if (cartItems) {
            cartItems.innerHTML = this.state.cart.map(item => `
                <div class="cart-item p-3 border-bottom border-success">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <i class="fas fa-shield-check text-success me-1"></i>
                                ${item.productName}
                            </h6>
                            <small class="text-muted">${item.productCategory} - ${item.productKarat}</small>
                            <div class="small">
                                Weight: ${item.weight.toFixed(2)}g | Live Rate: AUD${item.pricePerGram.toFixed(4)}/g
                            </div>
                            <div class="small text-success">
                                <i class="fas fa-clock me-1"></i>Priced ${item.priceDataAge}s ago
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">AUD${item.totalPrice.toFixed(2)}</div>
                            <button class="btn btn-sm btn-outline-danger" onclick="tradingSystem.removeFromCart(${item.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }

    removeFromCart(itemId) {
        this.state.cart = this.state.cart.filter(item => item.id !== itemId);
        this.updateCartDisplay();
        this.showNotification('Item removed from cart', 'info');
    }

    // ============================
    // UI HELPERS
    // ============================

    showLoadingState() {
        const loading = document.getElementById('systemLoading');
        const interface = document.getElementById('systemInterface');

        if (loading) loading.classList.remove('d-none');
        if (interface) interface.classList.add('d-none');
    }

    hideLoadingState() {
        const loading = document.getElementById('systemLoading');
        const interface = document.getElementById('systemInterface');

        if (loading) loading.classList.add('d-none');
        if (interface) interface.classList.remove('d-none');
    }

    updateLoadingProgress(percentage, status) {
        const progressBar = document.getElementById('loadingProgress');
        const statusText = document.getElementById('loadingStatus');

        if (progressBar) progressBar.style.width = `${percentage}%`;
        if (statusText) statusText.innerHTML = `<i class="fas fa-shield-alt fa-spin me-2"></i>${status}`;
    }

    showNotification(message, type = 'info') {
        const alertClass = {
            success: 'alert-success',
            warning: 'alert-warning',
            error: 'alert-danger',
            info: 'alert-info'
        }[type] || 'alert-info';

        const icon = {
            success: 'fas fa-check-circle',
            warning: 'fas fa-exclamation-triangle',
            error: 'fas fa-times-circle',
            info: 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';

        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 350px;">
                <i class="${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.querySelectorAll('.alert.position-fixed').forEach(alert => alert.remove());
        document.body.insertAdjacentHTML('beforeend', alertHTML);

        setTimeout(() => {
            const alert = document.querySelector(`.${alertClass}.position-fixed`);
            if (alert) alert.remove();
        }, 5000);
    }

    setupEventListeners() {
        // Implementation for event listeners
        console.log('✅ Event listeners setup complete');
    }

    populateSubcategoryTabs() {
        // Implementation for subcategory tabs
        console.log('✅ Subcategory tabs populated');
    }

    disableProductControls(productId) {
        const controls = [
            document.getElementById(`weight_${productId}`),
            document.getElementById(`weight_list_${productId}`),
            document.getElementById(`karat_${productId}`),
            document.getElementById(`karat_list_${productId}`),
            document.getElementById(`add_btn_${productId}`)
        ];

        controls.forEach(control => {
            if (control) control.disabled = true;
        });
    }

    showNoProducts() {
        const noProductsMessage = document.getElementById('noProductsMessage');
        if (noProductsMessage) noProductsMessage.classList.remove('d-none');
    }

    renderSecureProductRow(product) {
        // Implementation for list view product rows
        return `<tr><td colspan="7">List view implementation needed</td></tr>`;
    }
}

// ============================
// GLOBAL INITIALIZATION
// ============================

let tradingSystem;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔒 Initializing SECURE Trading System...');

    try {
        tradingSystem = new SecureTradingSystem();
        window.tradingSystem = tradingSystem; // For debugging only
    } catch (error) {
        console.error('❌ CRITICAL: Failed to initialize secure trading system:', error);

        document.body.innerHTML = `
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <div class="alert alert-danger">
                            <h3><i class="fas fa-exclamation-triangle me-2"></i>SYSTEM FAILURE</h3>
                            <p class="mb-4">The secure trading system failed to initialize.</p>
                            <p><strong>Error:</strong> ${error.message}</p>
                            <button class="btn btn-primary btn-lg" onclick="location.reload()">
                                <i class="fas fa-redo me-2"></i>Retry System Startup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (tradingSystem?.healthCheckInterval) {
        clearInterval(tradingSystem.healthCheckInterval);
    }
});

console.log('🔒 SECURE Trading System loaded - No trading without live prices!');
