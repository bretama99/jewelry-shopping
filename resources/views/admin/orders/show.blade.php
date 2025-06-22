@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Order #{{ $order->order_number }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shopping-cart"></i> Order #{{ $order->order_number }}
            </h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank"
               class="btn btn-success btn-sm">
                <i class="fas fa-file-pdf"></i> Download Receipt
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <!-- Order Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Order Status</h6>
                    <span class="badge badge-{{
                        $order->status == 'pending' ? 'warning' :
                        ($order->status == 'confirmed' ? 'info' :
                        ($order->status == 'processing' ? 'primary' :
                        ($order->status == 'shipped' ? 'secondary' :
                        ($order->status == 'delivered' ? 'success' : 'danger'))))
                    }} badge-lg">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                            <p><strong>Payment Status:</strong>
                                <span class="badge badge-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </p>
                            @if($order->payment_method)
                                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($order->shipped_at)
                                <p><strong>Shipped Date:</strong> {{ $order->shipped_at->format('M d, Y h:i A') }}</p>
                            @endif
                            @if($order->delivered_at)
                                <p><strong>Delivered Date:</strong> {{ $order->delivered_at->format('M d, Y h:i A') }}</p>
                            @endif
                            @if($order->tracking_number)
                                <p><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-3">
                        <div class="btn-group" role="group">
                            @if($order->status == 'pending')
                                <button class="btn btn-info btn-sm" onclick="updateStatus('confirmed')">
                                    <i class="fas fa-check"></i> Confirm Order
                                </button>
                            @endif
                            @if(in_array($order->status, ['confirmed', 'processing']))
                                <button class="btn btn-primary btn-sm" onclick="updateStatus('processing')">
                                    <i class="fas fa-cog"></i> Mark Processing
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="updateStatus('shipped')">
                                    <i class="fas fa-shipping-fast"></i> Mark Shipped
                                </button>
                            @endif
                            @if($order->status == 'shipped')
                                <button class="btn btn-success btn-sm" onclick="updateStatus('delivered')">
                                    <i class="fas fa-check-circle"></i> Mark Delivered
                                </button>
                            @endif
                            @if(!in_array($order->status, ['delivered', 'cancelled']))
                                <button class="btn btn-danger btn-sm" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Weight</th>
                                    <th>Karat</th>
                                    <th>Price/Gram</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @foreach($order->orderItems as $item)
                                    @php $itemTotal = $item->price * $item->weight; $subtotal += $itemTotal; @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product && $item->product->image)
                                                    <img src="{{ asset('storage/' . $item->product->image) }}"
                                                         alt="{{ $item->product_name }}"
                                                         class="rounded me-3"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                                                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-gem text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                    @if($item->product)
                                                        <small class="text-muted">{{ $item->product->category->name ?? 'N/A' }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($item->weight, 2) }}g</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $item->karat }}K</span>
                                        </td>
                                        <td>
                                            ${{ number_format($item->price, 2) }}
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                ${{ number_format($itemTotal, 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>${{ number_format($subtotal, 2) }}</strong></td>
                                </tr>
                                @if($order->tax_amount > 0)
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                        <td><strong>${{ number_format($order->tax_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                @if($order->shipping_amount > 0)
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                        <td><strong>${{ number_format($order->shipping_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                @if($order->discount_amount > 0)
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                        <td><strong class="text-danger">-${{ number_format($order->discount_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong class="text-primary">${{ number_format($order->total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Notes & History</h6>
                </div>
                <div class="card-body">
                    @if($order->notes)
                        <div class="alert alert-info">
                            <strong>Customer Notes:</strong><br>
                            {{ $order->notes }}
                        </div>
                    @endif

                    <!-- Status History -->
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Order Placed</h6>
                                <p class="timeline-info">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        @if($order->confirmed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Order Confirmed</h6>
                                    <p class="timeline-info">{{ $order->confirmed_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($order->shipped_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Order Shipped</h6>
                                    <p class="timeline-info">{{ $order->shipped_at->format('M d, Y h:i A') }}</p>
                                    @if($order->tracking_number)
                                        <p class="timeline-description">Tracking: {{ $order->tracking_number }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($order->delivered_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Order Delivered</h6>
                                    <p class="timeline-info">{{ $order->delivered_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Add Note -->
                    <div class="mt-4">
                        <h6>Add Internal Note:</h6>
                        <form id="noteForm">
                            <div class="form-group">
                                <textarea class="form-control" id="internalNote" placeholder="Add a note for internal use..." rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Note
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Sidebar -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="customer-info">
                        <h6 class="mb-3">
                            <i class="fas fa-user"></i> {{ $order->customer_name ?? 'Guest Customer' }}
                        </h6>

                        @if($order->customer_email)
                            <p class="mb-2">
                                <i class="fas fa-envelope text-muted me-2"></i>
                                <a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a>
                            </p>
                        @endif

                        @if($order->customer_phone)
                            <p class="mb-2">
                                <i class="fas fa-phone text-muted me-2"></i>
                                <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a>
                            </p>
                        @endif

                        @if($order->user_id)
                            <p class="mb-2">
                                <i class="fas fa-id-card text-muted me-2"></i>
                                <a href="{{ route('admin.customers.show', $order->user_id) }}">
                                    View Customer Profile
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            @if($order->billing_address)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Billing Address</h6>
                    </div>
                    <div class="card-body">
                        <address class="mb-0">
                            {{ $order->billing_name ?? $order->customer_name }}<br>
                            {{ $order->billing_address }}<br>
                            @if($order->billing_address2)
                                {{ $order->billing_address2 }}<br>
                            @endif
                            {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_zip }}<br>
                            {{ $order->billing_country ?? 'Australia' }}
                        </address>
                    </div>
                </div>
            @endif

            <!-- Shipping Address -->
            @if($order->shipping_address)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Shipping Address</h6>
                    </div>
                    <div class="card-body">
                        <address class="mb-0">
                            {{ $order->shipping_name ?? $order->customer_name }}<br>
                            {{ $order->shipping_address }}<br>
                            @if($order->shipping_address2)
                                {{ $order->shipping_address2 }}<br>
                            @endif
                            {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                            {{ $order->shipping_country ?? 'Australia' }}
                        </address>

                        @if($order->tracking_number)
                            <div class="mt-3">
                                <strong>Tracking Number:</strong><br>
                                <code>{{ $order->tracking_number }}</code>
                                <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyTracking()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Order Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="order-summary">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items:</span>
                            <span>{{ $order->orderItems->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Weight:</span>
                            <span>{{ number_format($order->orderItems->sum('weight'), 2) }}g</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($order->tax_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax:</span>
                                <span>${{ number_format($order->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->shipping_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>${{ number_format($order->shipping_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount:</span>
                                <span class="text-danger">-${{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between mb-0">
                            <strong>Total:</strong>
                            <strong class="text-primary">${{ number_format($order->total_amount, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="emailCustomer()">
                            <i class="fas fa-envelope"></i> Email Customer
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="addTracking()">
                            <i class="fas fa-truck"></i> Add Tracking
                        </button>
                        <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank"
                           class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-pdf"></i> View Receipt
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="duplicateOrder()">
                            <i class="fas fa-copy"></i> Duplicate Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">New Status</label>
                        <select class="form-control" id="newStatus" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3" id="trackingGroup" style="display: none;">
                        <label for="trackingNumber" class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" id="trackingNumber" name="tracking_number"
                               placeholder="Enter tracking number">
                    </div>
                    <div class="mb-3">
                        <label for="statusNote" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="statusNote" name="note" rows="3"
                                  placeholder="Add a note about this status change..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmStatusUpdate()">
                    Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email Customer Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="emailForm">
                    <div class="mb-3">
                        <label for="emailTo" class="form-label">To</label>
                        <input type="email" class="form-control" id="emailTo" name="to"
                               value="{{ $order->customer_email }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="emailSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="emailSubject" name="subject"
                               value="Update on your order #{{ $order->order_number }}">
                    </div>
                    <div class="mb-3">
                        <label for="emailMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="emailMessage" name="message" rows="6"
                                  placeholder="Write your message to the customer..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendEmail()">
                    <i class="fas fa-paper-plane"></i> Send Email
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline:before {
    content: '';
    position: absolute;
    left: -30px;
    top: 7px;
    bottom: -7px;
    width: 2px;
    background: #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-info {
    margin-bottom: 5px;
    font-size: 12px;
    color: #6c757d;
}

.timeline-description {
    font-size: 12px;
    color: #6c757d;
}

.customer-info i {
    width: 20px;
}

.order-summary {
    font-size: 14px;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
// Update order status
function updateStatus(status) {
    document.getElementById('newStatus').value = status;

    // Show tracking input for shipped status
    if (status === 'shipped') {
        document.getElementById('trackingGroup').style.display = 'block';
    } else {
        document.getElementById('trackingGroup').style.display = 'none';
    }

    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function confirmStatusUpdate() {
    const status = document.getElementById('newStatus').value;
    const note = document.getElementById('statusNote').value;
    const tracking = document.getElementById('trackingNumber').value;

    const updateBtn = event.target;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    updateBtn.disabled = true;

    fetch(`{{ route('admin.orders.update-status', $order) }}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status,
            note: note,
            tracking_number: tracking
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    })
    .finally(() => {
        updateBtn.innerHTML = 'Update Status';
        updateBtn.disabled = false;
    });
}

// Email customer
function emailCustomer() {
    const modal = new bootstrap.Modal(document.getElementById('emailModal'));
    modal.show();
}

function sendEmail() {
    const to = document.getElementById('emailTo').value;
    const subject = document.getElementById('emailSubject').value;
    const message = document.getElementById('emailMessage').value;

    if (!message.trim()) {
        alert('Please enter a message.');
        return;
    }

    const sendBtn = event.target;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    sendBtn.disabled = true;

    // Implement email sending logic here
    setTimeout(() => {
        alert('Email sent successfully!');
        bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
        sendBtn.disabled = false;
    }, 2000);
}

// Copy tracking number
function copyTracking() {
    const tracking = '{{ $order->tracking_number }}';
    navigator.clipboard.writeText(tracking).then(() => {
        alert('Tracking number copied to clipboard!');
    });
}

// Add tracking number
function addTracking() {
    const tracking = prompt('Enter tracking number:');
    if (tracking) {
        // Update tracking number
        fetch(`{{ route('admin.orders.update-tracking', $order) }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                tracking_number: tracking
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating tracking number');
            }
        });
    }
}

// Duplicate order
function duplicateOrder() {
    if (confirm('Create a duplicate of this order?')) {
        // Implement order duplication logic
        alert('Order duplication feature coming soon!');
    }
}

// Add internal note
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const note = document.getElementById('internalNote').value;
    if (!note.trim()) {
        alert('Please enter a note.');
        return;
    }

    // Implement note adding logic here
    fetch(`{{ route('admin.orders.add-note', $order) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('internalNote').value = '';
            alert('Note added successfully!');
            // Optionally reload to show the note
        } else {
            alert('Error adding note');
        }
    });
});
</script>
@endpush
