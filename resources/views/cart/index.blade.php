@extends('layouts.admin')

@section('title', 'Shopping Cart')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('external.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Shopping Cart</li>
                </ol>
            </nav>
        </div>
    </div>

    @if($cartItems->isEmpty())
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some beautiful jewelry to your cart to get started!</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-gem me-2"></i>Browse Jewelry
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Gold Price Alert -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-coins me-3"></i>
                    <div class="flex-grow-1">
                        <strong>Current Gold Price:</strong> ${{ number_format($goldPrice, 2) }}/oz
                        <small class="ms-3">
                            Market Status:
                            <span class="badge bg-{{ $marketData['status'] === 'open' ? 'success' : 'warning' }}">
                                {{ ucfirst($marketData['status'] ?? 'Unknown') }}
                            </span>
                        </small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPrices()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh Prices
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>Cart Items ({{ $cartCount }})
                        </h4>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                            <i class="fas fa-trash me-1"></i>Clear Cart
                        </button>
                    </div>
                    <div class="card-body p-0">
                        @foreach($cartItems as $item)
                            <div class="cart-item border-bottom p-4" data-item-id="{{ $item->id }}">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="{{ $item->product->image_url }}"
                                             alt="{{ $item->product->name }}"
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <p class="text-muted mb-1">{{ $item->product->category->name ?? 'Jewelry' }}</p>
                                        <span class="badge bg-warning text-dark">{{ $item->karat }} Gold</span>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Weight (grams)</label>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    onclick="adjustWeight({{ $item->id }}, -0.1)">-</button>
                                            <input type="number"
                                                   class="form-control text-center weight-input"
                                                   value="{{ $item->weight }}"
                                                   min="{{ $item->product->weight_min }}"
                                                   max="{{ $item->product->weight_max }}"
                                                   step="0.1"
                                                   onchange="updateItemWeight({{ $item->id }}, this.value)">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    onclick="adjustWeight({{ $item->id }}, 0.1)">+</button>
                                        </div>
                                        <small class="text-muted">
                                            Min: {{ $item->product->weight_min }}g, Max: {{ $item->product->weight_max }}g
                                        </small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="price-breakdown">
                                            <div class="current-price h6 mb-1">{{ $item->formatted_subtotal }}</div>
                                            <small class="text-muted">{{ $item->formatted_price_per_gram }}/g</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="removeItem({{ $item->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal ({{ $cartCount }} items):</span>
                            <span class="cart-subtotal">${{ number_format($cartTotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span class="cart-tax">${{ number_format($cartTotal * 0.10, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping:</span>
                            <span class="cart-shipping">
                                @if($cartTotal > 500)
                                    <span class="text-success">FREE</span>
                                @else
                                    $25.00
                                @endif
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <strong>Total:</strong>
                            <strong class="cart-total">
                                ${{ number_format($cartTotal + ($cartTotal * 0.10) + ($cartTotal > 500 ? 0 : 25), 2) }}
                            </strong>
                        </div>

                        @if($cartTotal <= 500)
                            <div class="alert alert-warning mt-3">
                                <small>
                                    <i class="fas fa-truck me-1"></i>
                                    Add ${{ number_format(500 - $cartTotal, 2) }} more for free shipping!
                                </small>
                            </div>
                        @endif

                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>Secure Checkout
                            </a>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Security & Trust -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body text-center">
                        <h6 class="card-title">Secure Shopping</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                <small class="d-block">SSL Secure</small>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-medal fa-2x text-warning mb-2"></i>
                                <small class="d-block">Certified</small>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-undo fa-2x text-info mb-2"></i>
                                <small class="d-block">30-Day Return</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function adjustWeight(itemId, adjustment) {
    const input = document.querySelector(`[data-item-id="${itemId}"] .weight-input`);
    const currentWeight = parseFloat(input.value);
    const newWeight = Math.max(0.1, currentWeight + adjustment);
    const maxWeight = parseFloat(input.getAttribute('max'));
    const minWeight = parseFloat(input.getAttribute('min'));

    if (newWeight >= minWeight && newWeight <= maxWeight) {
        input.value = newWeight.toFixed(1);
        updateItemWeight(itemId, newWeight);
    }
}

function updateItemWeight(itemId, weight) {
    if (weight < 0.1) {
        showAlert('error', 'Minimum weight is 0.1 grams');
        return;
    }

    fetch(`/cart/${itemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ weight: weight })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update item subtotal
            const itemRow = document.querySelector(`[data-item-id="${itemId}"]`);
            itemRow.querySelector('.current-price').textContent = data.item.subtotal;

            // Update cart totals
            updateCartTotals(data.cart_total);
            showAlert('success', data.message);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to update item weight');
    });
}

function removeItem(itemId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    fetch(`/cart/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            document.querySelector(`[data-item-id="${itemId}"]`).remove();

            // Update cart totals
            updateCartTotals(data.cart_total);

            // Update cart count in header
            document.querySelector('.cart-count').textContent = data.cart_count;

            showAlert('success', data.message);

            // Reload page if cart is empty
            if (data.cart_count === 0) {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to remove item');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }

    fetch('/cart/clear', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to clear cart');
    });
}

function refreshPrices() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    button.disabled = true;

    fetch('/cart/refresh-prices', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update individual item prices
            data.items.forEach(item => {
                const itemRow = document.querySelector(`[data-item-id="${item.id}"]`);
                if (itemRow) {
                    itemRow.querySelector('.current-price').textContent = item.subtotal;
                    itemRow.querySelector('.text-muted').textContent = item.price_per_gram + '/g';
                }
            });

            // Update cart totals
            updateCartTotals(data.cart_total);

            // Update gold price
            const goldPriceSpan = document.querySelector('.alert-info strong').nextSibling;
            goldPriceSpan.textContent = ' ' + data.gold_price + '/oz';

            showAlert('success', data.message);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to refresh prices');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function updateCartTotals(cartTotal) {
    const subtotal = parseFloat(cartTotal.replace(/[$,]/g, ''));
    const tax = subtotal * 0.10;
    const shipping = subtotal > 500 ? 0 : 25;
    const total = subtotal + tax + shipping;

    document.querySelector('.cart-subtotal').textContent = ' + subtotal.toFixed(2);
    document.querySelector('.cart-tax').textContent = ' + tax.toFixed(2);
    document.querySelector('.cart-shipping').innerHTML = shipping === 0 ?
        '<span class="text-success">FREE</span>' : ' + shipping.toFixed(2);
    document.querySelector('.cart-total').textContent = ' + total.toFixed(2);
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(alert => {
        if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
            alert.remove();
        }
    });

    // Add new alert
    document.querySelector('.container').insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector(`.${alertClass}`);
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Auto-refresh prices every 5 minutes
setInterval(() => {
    if (document.querySelector('.cart-item')) {
        refreshPrices();
    }
}, 300000); // 5 minutes
</script>
@endpush
@endsection
