@extends('layouts.admin')

@section('title', 'Secure Checkout')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('external.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Cart</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Checkout Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <div class="step-label">Cart</div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-label">Checkout</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
        @csrf
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <!-- Customer Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                           id="customer_name" name="customer_name"
                                           value="{{ old('customer_name', $user->name ?? '') }}" required>
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control @error('customer_email') is-invalid @enderror"
                                           id="customer_email" name="customer_email"
                                           value="{{ old('customer_email', $user->email ?? '') }}" required>
                                    @error('customer_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror"
                                           id="customer_phone" name="customer_phone"
                                           value="{{ old('customer_phone', $user->phone ?? '') }}" required>
                                    @error('customer_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Billing Address
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="billing_address" class="form-label">Street Address *</label>
                            <textarea class="form-control @error('billing_address') is-invalid @enderror"
                                      id="billing_address" name="billing_address" rows="3" required>{{ old('billing_address') }}</textarea>
                            @error('billing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Include street number, street name, suburb, state, and postcode</div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>Shipping Address
                        </h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="same-as-billing" checked>
                            <label class="form-check-label" for="same-as-billing">
                                Same as billing address
                            </label>
                        </div>
                    </div>
                    <div class="card-body" id="shipping-address-section" style="display: none;">
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Street Address</label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                      id="shipping_address" name="shipping_address" rows="3">{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Include street number, street name, suburb, state, and postcode</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>Payment Method
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                           id="payment_card" value="card" checked>
                                    <label class="form-check-label" for="payment_card">
                                        <i class="fas fa-credit-card me-2"></i>Credit/Debit Card
                                        <small class="d-block text-muted">Secure payment via Stripe</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                           id="payment_bank" value="bank_transfer">
                                    <label class="form-check-label" for="payment_bank">
                                        <i class="fas fa-university me-2"></i>Bank Transfer
                                        <small class="d-block text-muted">Direct bank transfer</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                           id="payment_paypal" value="paypal">
                                    <label class="form-check-label" for="payment_paypal">
                                        <i class="fab fa-paypal me-2"></i>PayPal
                                        <small class="d-block text-muted">Pay with your PayPal account</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                           id="payment_cash" value="cash">
                                    <label class="form-check-label" for="payment_cash">
                                        <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                                        <small class="d-block text-muted">Pay when you receive</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('payment_method')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>Order Notes (Optional)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Special delivery instructions, gift message, or any other notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror"
                                   type="checkbox" id="terms_accepted" name="terms_accepted" required>
                            <label class="form-check-label" for="terms_accepted">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> *
                            </label>
                            @error('terms_accepted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="newsletter_subscribe" name="newsletter_subscribe">
                            <label class="form-check-label" for="newsletter_subscribe">
                                Subscribe to our newsletter for exclusive offers and updates
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm position-sticky" style="top: 2rem;">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Order Items -->
                        <div class="order-items mb-3">
                            @foreach($cartItems as $item)
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <img src="{{ $item->product->image_url }}"
                                         alt="{{ $item->product->name }}"
                                         class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <small class="text-muted">
                                            {{ $item->karat }} Gold • {{ $item->formatted_weight }}
                                        </small>
                                        <div class="fw-bold">{{ $item->formatted_subtotal }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="pricing-breakdown">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ count($cartItems) }} items):</span>
                                <span id="checkout-subtotal">${{ number_format($cartTotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10% GST):</span>
                                <span id="checkout-tax">${{ number_format($taxAmount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping:</span>
                                <span id="checkout-shipping">
                                    @if($shippingCost == 0)
                                        <span class="text-success">FREE</span>
                                    @else
                                        ${{ number_format($shippingCost, 2) }}
                                    @endif
                                </span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between h5">
                                <strong>Total:</strong>
                                <strong id="checkout-total">${{ number_format($finalTotal, 2) }}</strong>
                            </div>
                        </div>

                        <!-- Gold Price Info -->
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-coins me-1"></i>
                                <strong>Gold Price:</strong> ${{ number_format($goldPrice, 2) }}/oz
                                <br>
                                <strong>Market Status:</strong>
                                <span class="badge bg-{{ $marketData['status'] === 'open' ? 'success' : 'warning' }}">
                                    {{ ucfirst($marketData['status'] ?? 'Unknown') }}
                                </span>
                            </small>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2 w-100" onclick="refreshCheckoutTotals()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh with Latest Prices
                            </button>
                        </div>

                        <!-- Place Order Button -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="place-order-btn">
                                <i class="fas fa-lock me-2"></i>Place Order
                            </button>
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Cart
                            </a>
                        </div>

                        <!-- Security Features -->
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>Your payment information is secure and encrypted
                            </small>
                            <div class="mt-2">
                                <img src="{{ asset('images/security-badges.png') }}" alt="Security" class="img-fluid" style="max-height: 40px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Agreement to Terms</h6>
                <p>By placing an order with Jewelry Store, you agree to these terms and conditions.</p>

                <h6>2. Product Information</h6>
                <p>All jewelry is crafted using genuine gold at the specified karat levels. Weights may vary slightly due to the handcrafted nature of our products.</p>

                <h6>3. Pricing</h6>
                <p>Prices are based on current gold market rates and may fluctuate. The price at the time of order confirmation is final.</p>

                <h6>4. Payment</h6>
                <p>Payment must be received before shipping. We accept major credit cards, bank transfers, and PayPal.</p>

                <h6>5. Shipping</h6>
                <p>Orders are processed within 1-3 business days. Shipping times vary based on location and method selected.</p>

                <h6>6. Returns</h6>
                <p>We offer a 30-day return policy for unworn items in original condition. Custom orders are final sale.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Information We Collect</h6>
                <p>We collect information you provide directly, such as when you create an account or place an order.</p>

                <h6>How We Use Your Information</h6>
                <p>We use your information to process orders, communicate with you, and improve our services.</p>

                <h6>Information Sharing</h6>
                <p>We do not sell, trade, or otherwise transfer your personal information to third parties except as described in this policy.</p>

                <h6>Security</h6>
                <p>We implement appropriate security measures to protect your personal information.</p>

                <h6>Contact Us</h6>
                <p>If you have questions about this privacy policy, please contact us at privacy@jewelrystore.com.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.progress-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2rem;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
    max-width: 200px;
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 60%;
    width: 80%;
    height: 2px;
    background-color: #dee2e6;
    z-index: 1;
}

.step.completed::after,
.step.active::after {
    background-color: #198754;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 2;
}

.step.completed .step-number {
    background-color: #198754;
    color: white;
}

.step.active .step-number {
    background-color: #0d6efd;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
}

.step.completed .step-label,
.step.active .step-label {
    color: #495057;
}

.payment-option {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.payment-option:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.payment-option .form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.form-check-input:checked + .form-check-label .payment-option {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

@media (max-width: 768px) {
    .progress-steps {
        flex-direction: column;
        gap: 1rem;
    }

    .step::after {
        display: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Same as billing checkbox
    const sameAsBillingCheck = document.getElementById('same-as-billing');
    const shippingSection = document.getElementById('shipping-address-section');
    const billingAddress = document.getElementById('billing_address');
    const shippingAddress = document.getElementById('shipping_address');

    sameAsBillingCheck.addEventListener('change', function() {
        if (this.checked) {
            shippingSection.style.display = 'none';
            shippingAddress.value = billingAddress.value;
        } else {
            shippingSection.style.display = 'block';
        }
    });

    // Copy billing to shipping when billing changes
    billingAddress.addEventListener('input', function() {
        if (sameAsBillingCheck.checked) {
            shippingAddress.value = this.value;
        }
    });

    // Form validation
    const form = document.getElementById('checkout-form');
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('place-order-btn');
        const termsCheck = document.getElementById('terms_accepted');

        if (!termsCheck.checked) {
            e.preventDefault();
            showAlert('error', 'Please accept the terms and conditions to continue.');
            return;
        }

        // Disable submit button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Order...';
    });
});

function refreshCheckoutTotals() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    button.disabled = true;

    fetch('/checkout/refresh-totals', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update totals
            document.getElementById('checkout-subtotal').textContent = data.data.formatted.subtotal;
            document.getElementById('checkout-tax').textContent = data.data.formatted.tax_amount;
            document.getElementById('checkout-shipping').innerHTML = data.data.formatted.shipping_cost;
            document.getElementById('checkout-total').textContent = data.data.formatted.total_amount;

            // Update gold price
            const goldPriceElement = document.querySelector('.alert-info strong');
            goldPriceElement.nextSibling.textContent = ' ' + data.data.formatted.gold_price + '/oz';

            showAlert('success', data.message);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to refresh totals');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
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
    document.querySelectorAll('.alert-success, .alert-danger').forEach(alert => alert.remove());

    // Add new alert at the top of the form
    document.querySelector('.container').insertAdjacentHTML('afterbegin', alertHtml);

    // Scroll to top
    window.scrollTo({top: 0, behavior: 'smooth'});

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector(`.${alertClass}`);
        if (alert) alert.remove();
    }, 5000);
}
</script>
@endpush
@endsection
