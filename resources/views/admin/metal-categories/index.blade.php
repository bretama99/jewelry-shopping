{{-- File: resources/views/admin/metal-categories/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Metal Categories Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Metal Categories</h1>
            <p class="mb-0 text-muted">Manage precious metals with live pricing from metals-api.com</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" onclick="refreshAllPrices()">
                <i class="fas fa-sync-alt me-2"></i>Refresh All Prices
            </button>
            <a href="{{ route('admin.metal-categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Metal Category
            </a>
        </div>
    </div>

    <!-- Live Price Overview -->
    <div class="row mb-4">
        @foreach($metalCategories as $category)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-{{ $category->symbol == 'XAU' ? 'warning' : ($category->symbol == 'XAG' ? 'secondary' : 'info') }} shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $category->symbol == 'XAU' ? 'warning' : ($category->symbol == 'XAG' ? 'secondary' : 'info') }} text-uppercase mb-1">
                                    {{ $category->name }} ({{ $category->symbol }})
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="price-{{ $category->symbol }}">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                                <div class="text-xs text-muted" id="update-{{ $category->symbol }}">Loading from metals-api.com...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-coins fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Metal Categories Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Metal Categories ({{ $metalCategories->count() }})</h6>
        </div>
        <div class="card-body">
            @if($metalCategories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Metal</th>
                                <th>Symbol</th>
                                <th>Current Price (AUD/oz)</th>
                                <th>Available Karats</th>
                                <th>Products Count</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metalCategories as $category)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-3"
                                                 style="width: 20px; height: 20px; background-color: {{ $category->color ?? '#6c757d' }};"></div>
                                            <strong>{{ $category->name }}</strong>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $category->symbol }}</span></td>
                                    <td>
                                        <div id="table-price-{{ $category->symbol }}">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($category->symbol == 'XAU')
                                                9K, 10K, 14K, 18K, 21K, 22K, 24K
                                            @elseif($category->symbol == 'XAG')
                                                925, 950, 999
                                            @elseif($category->symbol == 'XPT')
                                                900, 950, 999
                                            @elseif($category->symbol == 'XPD')
                                                500, 950, 999
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $category->products_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted" id="table-updated-{{ $category->symbol }}">
                                            {{ $category->updated_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.metal-categories.show', $category) }}"
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.metal-categories.edit', $category) }}"
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    title="Refresh Price" onclick="refreshPrice('{{ $category->symbol }}')">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            @if($category->products_count == 0)
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Metal Categories Found</h5>
                    <p class="text-muted">Add your first metal category to start managing precious metals pricing.</p>
                    <a href="{{ route('admin.metal-categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Metal Category
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAllLivePrices();
    // Auto-refresh every 5 minutes
    setInterval(loadAllLivePrices, 5 * 60 * 1000);
});

// Function to fetch live prices from metals-api.com and update displays
async function loadAllLivePrices() {
    console.log('Fetching live metal prices from metals-api.com...');

    try {
        // Fetch live prices directly in AUD using the new API
        const response = await fetch('https://metals-api.com/api/latest?access_key=1c70eyqb8hpkqcg8bmtfwalg6u84j20qv9gq8fq7k2h8f6fi9m7p61sxkmng&base=AUD&symbols=XAU%2CXAG%2CXPD%2CXPT');

        if (!response.ok) {
            throw new Error(`API request failed: ${response.status}`);
        }

        const data = await response.json();
        console.log('Live prices API response:', data);

        if (data.success && data.rates) {
            // Convert troy ounces to grams
            const gramsPerTroyOz = 31.1035;

            // Update prices for each symbol that exists in the DOM
            const symbols = ['XAU', 'XAG', 'XPT', 'XPD'];

            symbols.forEach(symbol => {
                const priceElement = document.getElementById(`price-${symbol}`);
                const tablePriceElement = document.getElementById(`table-price-${symbol}`);

                // Only update if the element exists (metal category exists in database)
                if (priceElement || tablePriceElement) {
                    const pricePerOz = data.rates[symbol]; // Already in AUD per troy ounce
                    const pricePerGram = (pricePerOz / gramsPerTroyOz);

                    const priceData = {
                        price_aud: `AUD${pricePerOz.toFixed(2)}`,
                        price_per_gram_aud: `AUD${pricePerGram.toFixed(2)}`,
                        last_updated: new Date().toLocaleString(),
                        success: true,
                        api_source: 'metals-api.com'
                    };

                    updatePriceDisplay(symbol, priceData);
                    console.log(`Updated ${symbol}: ${priceData.price_aud} per oz (${priceData.price_per_gram_aud} per gram)`);
                }
            });

        } else {
            throw new Error('Invalid API response format');
        }

    } catch (error) {
        console.error('Error fetching live prices from metals-api.com:', error);
        showFallbackMessage();
    }
}

function updatePriceDisplay(symbol, priceData) {
    // Update card display (overview cards)
    const priceElement = document.getElementById(`price-${symbol}`);
    const updateElement = document.getElementById(`update-${symbol}`);

    if (priceElement && priceData.success) {
        priceElement.innerHTML = priceData.price_aud;
        if (updateElement) {
            updateElement.innerHTML = `Updated: ${priceData.last_updated} (${priceData.api_source})`;
        }

        // Add flash animation
        priceElement.classList.add('price-flash');
        setTimeout(() => {
            priceElement.classList.remove('price-flash');
        }, 1000);
    }

    // Update table display
    const tablePriceElement = document.getElementById(`table-price-${symbol}`);
    const tableUpdatedElement = document.getElementById(`table-updated-${symbol}`);

    if (tablePriceElement && priceData.success) {
        tablePriceElement.innerHTML = `<strong>${priceData.price_aud}</strong><br><small class="text-muted">${priceData.price_per_gram_aud}/gram</small>`;
        if (tableUpdatedElement) {
            tableUpdatedElement.innerHTML = `${priceData.last_updated}<br><small class="text-success">${priceData.api_source}</small>`;
        }
    }
}

function showFallbackMessage() {
    console.log('Showing fallback message for API failure...');

    const symbols = ['XAU', 'XAG', 'XPT', 'XPD'];

    symbols.forEach(symbol => {
        const priceElement = document.getElementById(`price-${symbol}`);
        const tablePriceElement = document.getElementById(`table-price-${symbol}`);
        const updateElement = document.getElementById(`update-${symbol}`);
        const tableUpdatedElement = document.getElementById(`table-updated-${symbol}`);

        // Only update if the element exists
        if (priceElement) {
            priceElement.innerHTML = '<span class="text-danger">API Error</span>';
        }

        if (tablePriceElement) {
            tablePriceElement.innerHTML = '<span class="text-danger">Unable to fetch</span>';
        }

        if (updateElement) {
            updateElement.innerHTML = 'metals-api.com unavailable';
        }

        if (tableUpdatedElement) {
            tableUpdatedElement.innerHTML = '<span class="text-danger">API Error</span>';
        }
    });
}

function refreshPrice(symbol) {
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    // Fetch individual price for this specific symbol
    refreshIndividualPrice(symbol)
        .then(priceData => {
            if (priceData.success) {
                updatePriceDisplay(symbol, priceData);
                showAlert('success', `${symbol} price refreshed: ${priceData.price_per_gram_aud}/gram from metals-api.com`);
            } else {
                showAlert('error', `Failed to refresh ${symbol} price from metals-api.com`);
            }
        })
        .catch(error => {
            console.error('Error refreshing price:', error);
            showAlert('error', 'Error refreshing price from metals-api.com. Please try again.');
        })
        .finally(() => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
}

async function refreshIndividualPrice(symbol) {
    try {
        const response = await fetch(`https://metals-api.com/api/latest?access_key=1c70eyqb8hpkqcg8bmtfwalg6u84j20qv9gq8fq7k2h8f6fi9m7p61sxkmng&base=AUD&symbols=${symbol}`);
        const data = await response.json();

        if (data.success && data.rates && data.rates[symbol]) {
            const gramsPerTroyOz = 31.1035;
            const pricePerOz = data.rates[symbol]; // Already in AUD
            const pricePerGram = pricePerOz / gramsPerTroyOz;

            return {
                price_aud: `AUD${pricePerOz.toFixed(2)}`,
                price_per_gram_aud: `AUD${pricePerGram.toFixed(2)}`,
                last_updated: new Date().toLocaleString(),
                success: true,
                api_source: 'metals-api.com'
            };
        } else {
            throw new Error('Invalid response from metals-api.com');
        }
    } catch (error) {
        console.error(`Error fetching ${symbol} price from metals-api.com:`, error);
        return { success: false };
    }
}

function refreshAllPrices() {
    const btn = event.target;
    const originalContent = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;

    loadAllLivePrices()
        .then(() => {
            showAlert('success', 'All metal prices refreshed with live market data from metals-api.com!');
        })
        .catch(error => {
            console.error('Error refreshing all prices:', error);
            showAlert('error', 'Error refreshing prices from metals-api.com. Please try again.');
        })
        .finally(() => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
}

function deleteCategory(categoryId, categoryName) {
    document.getElementById('deleteCategoryName').textContent = categoryName;
    document.getElementById('deleteForm').action = `/admin/metal-categories/${categoryId}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alert);
    setTimeout(() => {
        if (alert.parentNode) alert.remove();
    }, 5000);
}

// For the create page - live price preview (only if elements exist)
function updateKaratInfo() {
    const symbolSelect = document.getElementById('symbol');
    const karatInfo = document.getElementById('karatInfo');
    const pricePreview = document.getElementById('pricePreview');

    if (!symbolSelect) return; // Not on create page

    const symbol = symbolSelect.value;

    let karatText = '';

    switch(symbol) {
        case 'XAU':
            karatText = `
                <h6 class="text-warning mb-2">Gold Karat Options (9K, 10K, 14K, 18K, 21K, 22K, 24K):</h6>
                <div class="row g-2 small">
                    <div class="col-6">9K (37.5% Gold)</div>
                    <div class="col-6">10K (41.7% Gold)</div>
                    <div class="col-6">14K (58.3% Gold)</div>
                    <div class="col-6">18K (75% Gold)</div>
                    <div class="col-6">21K (87.5% Gold)</div>
                    <div class="col-6">22K (91.7% Gold)</div>
                    <div class="col-6">24K (99.9% Pure)</div>
                </div>
            `;
            break;
        case 'XAG':
            karatText = `
                <h6 class="text-secondary mb-2">Silver Purity Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">925 (Sterling Silver)</div>
                    <div class="col-6">950 (Higher Grade)</div>
                    <div class="col-6">999 (99.9% Pure)</div>
                </div>
            `;
            break;
        case 'XPT':
            karatText = `
                <h6 class="text-info mb-2">Platinum Purity Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">900 (90% Platinum)</div>
                    <div class="col-6">950 (95% Platinum)</div>
                    <div class="col-6">999 (99.9% Pure)</div>
                </div>
            `;
            break;
        case 'XPD':
            karatText = `
                <h6 class="text-dark mb-2">Palladium Purity Options:</h6>
                <div class="row g-2 small">
                    <div class="col-6">500 (50% Palladium)</div>
                    <div class="col-6">950 (95% Palladium)</div>
                    <div class="col-6">999 (99.9% Pure)</div>
                </div>
            `;
            break;
        default:
            karatText = '<small class="text-muted">Select a metal symbol to see available purity levels</small>';
    }

    if (karatInfo) {
        karatInfo.innerHTML = karatText;
    }

    // Load live price preview for create page
    if (symbol && pricePreview) {
        pricePreview.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading from metals-api.com...</span></div>';

        refreshIndividualPrice(symbol)
            .then(data => {
                if (data.success) {
                    pricePreview.innerHTML = `
                        <div class="text-center">
                            <h4 class="text-success mb-1">${data.price_aud}</h4>
                            <small class="text-muted">AUD per ounce (pure metal)</small>
                            <hr>
                            <div class="small">
                                <div><strong>Price per gram:</strong> ${data.price_per_gram_aud}</div>
                                <div><strong>Source:</strong> ${data.api_source}</div>
                                <div class="text-muted mt-2">Last updated: ${data.last_updated}</div>
                            </div>
                        </div>
                    `;
                } else {
                    pricePreview.innerHTML = `
                        <div class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Unable to fetch current price from metals-api.com</p>
                            <small>Please try again later</small>
                        </div>
                    `;
                }
            })
            .catch(error => {
                pricePreview.innerHTML = `
                    <div class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Error loading price from metals-api.com</p>
                    </div>
                `;
            });
    } else if (pricePreview) {
        pricePreview.innerHTML = `
            <i class="fas fa-coins fa-3x text-muted mb-3"></i>
            <p class="text-muted">Select a metal symbol to see current market price from metals-api.com</p>
        `;
    }
}

// Add price flash animation CSS
const style = document.createElement('style');
style.textContent = `
    .price-flash {
        animation: priceFlash 1s ease-out;
    }

    @keyframes priceFlash {
        0% { background-color: #d4edda; transform: scale(1.05); }
        50% { background-color: #c3e6cb; }
        100% { background-color: transparent; transform: scale(1); }
    }
`;
document.head.appendChild(style);

</script>
@endsection
