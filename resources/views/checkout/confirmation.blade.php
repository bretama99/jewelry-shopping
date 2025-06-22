
@extends('layouts.admin')

@section('title', 'Order Confirmation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                <h1 class="h2 mb-3">Order Placed Successfully!</h1>
                <p class="lead text-muted">
                    Thank you for your order. We'll send you a confirmation email shortly.
                </p>
            </div>

            <!-- Order Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Order Details
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Order Date:</strong></td>
                                    <td>{{ $order->created_at->format('M j, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_color }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $order->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $order->customer_email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $order->customer_phone }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Karat</th>
                                    <th>Weight</th>
                                    <th>Price/gram</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->product_image_url }}"
                                                     alt="{{ $item->product_name }}"
                                                     class="me-3 rounded"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                    <small class="text-muted">{{ $item->category_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ $item->karat_label }}</span>
                                        </td>
                                        <td>{{ $item->formatted_weight }}</td>
                                        <td>{{ $item->formatted_price_per_gram }}</td>
                                        <td class="text-end">{{ $item->formatted_subtotal }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Totals -->
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-borderless">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end">{{ $order->formatted_subtotal }}</td>
                                </tr>
                                <tr>
                                    <td>Tax (10% GST):</td>
                                    <td class="text-end">${{ number_format($order->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Shipping:</td>
                                    <td class="text-end">
                                        @if($order->shipping_cost == 0)
                                            <span class="text-success">FREE</span>
                                        @else
                                            ${{ number_format($order->shipping_cost, 2) }}
                                        @endif
                                    </td>
                                </tr>
                                <tr class="table-dark">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong>{{ $order->formatted_total }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>Billing Address
                            </h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-0">
                                {{ $order->billing_address }}
                            </address>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-truck me-2"></i>Shipping Address
                            </h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-0">
                                {{ $order->shipping_address }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <div class="d-grid gap-2 d-md-block">
                    <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Order Details
                    </a>
                    <a href="{{ route('orders.track', $order->order_number) }}" class="btn btn-info">
                        <i class="fas fa-shipping-fast me-2"></i>Track Order
                    </a>
                    <a href="{{ route('orders.download-receipt', $order->order_number) }}" class="btn btn-success">
                        <i class="fas fa-download me-2"></i>Download Receipt
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>

            <!-- What's Next -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>What Happens Next?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                            <h6>Confirmation Email</h6>
                            <small class="text-muted">You'll receive an order confirmation email within 5 minutes.</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                            <h6>Processing</h6>
                            <small class="text-muted">Your jewelry will be carefully crafted and prepared (1-3 business days).</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-shipping-fast fa-2x text-success mb-2"></i>
                            <h6>Shipping</h6>
                            <small class="text-muted">Your order will be shipped with tracking information provided.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gold Market Info -->
            <div class="alert alert-info mt-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="alert-heading mb-1">
                            <i class="fas fa-coins me-2"></i>Gold Market Information
                        </h6>
                        <small>
                            Your order was placed at a gold price of
                            <strong>${{ number_format($order->gold_price_at_order, 2) }}/oz</strong>.
                            This price is locked in for your order.
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-info fs-6">Price Locked</span>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="text-center mt-4">
                <h6>Need Help?</h6>
                <p class="text-muted">
                    Contact our customer support team at
                    <a href="mailto:support@jewelrystore.com">support@jewelrystore.com</a>
                    or call <a href="tel:+61212345678">+61 2 1234 5678</a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.success-icon {
    animation: checkmark 0.6s ease-in-out;
}

@keyframes checkmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

@media print {
    .btn, .alert, .card-header {
        display: none !important;
    }
}
</style>
@endpush
@endsection
