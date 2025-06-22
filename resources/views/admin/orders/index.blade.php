@extends('layouts.admin')

@section('title', 'Orders Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shopping-cart"></i> Orders Management
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export Orders
            </a>
            <button class="btn btn-info btn-sm" onclick="showOrderAnalytics()">
                <i class="fas fa-chart-bar"></i> Analytics
            </button>
            <button class="btn btn-primary btn-sm" onclick="refreshOrders()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Enhanced Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_orders'] ?? 0 }}
                            </div>
                            @if(isset($stats['total_items_sold']))
                                <small class="text-muted">{{ number_format($stats['total_items_sold']) }} items sold</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                AUD${{ number_format($stats['today_revenue'] ?? 0, 2) }}
                            </div>
                            @if(isset($stats['average_order_value']))
                                <small class="text-muted">Avg: AUD${{ number_format($stats['average_order_value'], 2) }}</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pending Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['pending_orders'] ?? 0 }}
                            </div>
                            @if(isset($stats['total_weight_sold']))
                                <small class="text-muted">{{ number_format($stats['total_weight_sold'], 1) }}g total weight</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                AUD${{ number_format($stats['month_revenue'] ?? 0, 2) }}
                            </div>
                            @if(isset($stats['average_items_per_order']))
                                <small class="text-muted">{{ number_format($stats['average_items_per_order'], 1) }} items/order</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Analytics Row -->
    @if(isset($stats['top_categories']) && $stats['top_categories']->count() > 0)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Categories</h6>
                </div>
                <div class="card-body">
                    @foreach($stats['top_categories'] as $category)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span>{{ $category->category_name }}</span>
                                <span>{{ $category->count }} orders - AUD${{ number_format($category->total_value, 0) }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ ($category->total_value / $stats['top_categories']->first()->total_value) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Karat Distribution</h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['karat_distribution']))
                        @foreach($stats['karat_distribution'] as $karat)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $karat->karat }} Gold</span>
                                    <span>{{ $karat->count }} items - {{ number_format($karat->total_weight, 1) }}g</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ ($karat->total_weight / $stats['karat_distribution']->first()->total_weight) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search Orders</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Order number, customer name...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sort">Sort By</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="total_high" {{ request('sort') == 'total_high' ? 'selected' : '' }}>Highest Total</option>
                                <option value="total_low" {{ request('sort') == 'total_low' ? 'selected' : '' }}>Lowest Total</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Orders List ({{ $orders->total() ?? 0 }} total)
            </h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                    Bulk Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_processed')">
                        <i class="fas fa-check"></i> Mark as Processed
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_shipped')">
                        <i class="fas fa-shipping-fast"></i> Mark as Shipped
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('export_selected')">
                        <i class="fas fa-download"></i> Export Selected
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($orders && $orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="ordersTable">
                        <thead>
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th width="12%">Order #</th>
                                <th width="15%">Customer</th>
                                <th width="10%">Date</th>
                                <th width="12%">Items & Weight</th>
                                <th width="12%">Total</th>
                                <th width="10%">Status</th>
                                <th width="10%">Payment</th>
                                <th width="16%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr id="order-{{ $order->id }}">
                                    <td>
                                        <input type="checkbox" name="selected_orders[]" value="{{ $order->id }}">
                                    </td>
                                    <td>
                                        <strong>#{{ $order->order_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->customer_name ?? 'Guest' }}</strong>
                                            @if($order->customer_email)
                                                <br>
                                                <small class="text-muted">{{ $order->customer_email }}</small>
                                            @endif
                                            @if($order->customer_phone)
                                                <br>
                                                <small class="text-muted">{{ $order->customer_phone }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $order->created_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $order->order_items_count ?? $order->orderItems->count() }} items
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ number_format($order->total_weight ?? 0, 2) }}g total
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            AUD${{ number_format($order->total_amount, 2) }}
                                        </strong>
                                        @if($order->tax_amount > 0)
                                            <br>
                                            <small class="text-muted">
                                                +AUD${{ number_format($order->tax_amount, 2) }} tax
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{
                                            $order->status == 'pending' ? 'warning' :
                                            ($order->status == 'confirmed' ? 'info' :
                                            ($order->status == 'processing' ? 'primary' :
                                            ($order->status == 'shipped' ? 'secondary' :
                                            ($order->status == 'delivered' ? 'success' : 'danger'))))
                                        }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status ?? 'pending') }}
                                        </span>
                                        @if($order->payment_method)
                                            <br>
                                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="{{ route('admin.orders.show', $order) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($order->canBeStatusUpdatedTo('confirmed'))
                                                        <li><a class="dropdown-item" href="#"
                                                               onclick="updateStatus({{ $order->id }}, 'confirmed')">
                                                            <i class="fas fa-check"></i> Confirm Order
                                                        </a></li>
                                                    @endif
                                                    @if($order->canBeStatusUpdatedTo('processing'))
                                                        <li><a class="dropdown-item" href="#"
                                                               onclick="updateStatus({{ $order->id }}, 'processing')">
                                                            <i class="fas fa-cog"></i> Mark Processing
                                                        </a></li>
                                                    @endif
                                                    @if($order->canBeStatusUpdatedTo('shipped'))
                                                        <li><a class="dropdown-item" href="#"
                                                               onclick="updateStatus({{ $order->id }}, 'shipped')">
                                                            <i class="fas fa-shipping-fast"></i> Mark Shipped
                                                        </a></li>
                                                    @endif
                                                    @if($order->canBeStatusUpdatedTo('delivered'))
                                                        <li><a class="dropdown-item" href="#"
                                                               onclick="updateStatus({{ $order->id }}, 'delivered')">
                                                            <i class="fas fa-check-circle"></i> Mark Delivered
                                                        </a></li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item"
                                                           href="{{ route('admin.orders.receipt', $order) }}" target="_blank">
                                                        <i class="fas fa-file-pdf"></i> Download Receipt
                                                    </a></li>
                                                    @if($order->canBeStatusUpdatedTo('cancelled'))
                                                        <li><a class="dropdown-item text-danger" href="#"
                                                               onclick="cancelOrder({{ $order->id }})">
                                                            <i class="fas fa-times"></i> Cancel Order
                                                        </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No orders found</h5>
                    <p class="text-gray-500">Orders will appear here when customers place them.</p>
                </div>
            @endif
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
                    <input type="hidden" id="orderId" name="order_id">
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

<!-- Order Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="analyticsContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Loading analytics...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fc;
}
.badge {
    font-size: 0.75rem;
}
.btn-group-vertical .btn {
    margin-bottom: 2px;
}
.progress {
    background-color: #e9ecef;
}
.progress-bar {
    background-color: #4e73df;
}
.progress-bar.bg-warning {
    background-color: #f6c23e !important;
}
</style>
@endpush

@push('scripts')
<script>
// Auto-refresh orders every 30 seconds
setInterval(function() {
    if (document.visibilityState === 'visible') {
        // Only refresh if no modals are open
        if (!document.querySelector('.modal.show')) {
            refreshOrders();
        }
    }
}, 30000);

function refreshOrders() {
    window.location.reload();
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="selected_orders[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update order status
function updateStatus(orderId, status) {
    document.getElementById('orderId').value = orderId;
    document.getElementById('newStatus').value = status;

    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function confirmStatusUpdate() {
    const orderId = document.getElementById('orderId').value;
    const status = document.getElementById('newStatus').value;
    const note = document.getElementById('statusNote').value;

    // Show loading
    const updateBtn = event.target;
    const originalText = updateBtn.innerHTML;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    updateBtn.disabled = true;

    // Make AJAX request
    fetch(`/admin/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: status,
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the UI
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
        updateBtn.innerHTML = originalText;
        updateBtn.disabled = false;
    });
}

// Cancel order
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        updateStatus(orderId, 'cancelled');
    }
}

// Bulk actions
function bulkAction(action) {
    const selectedOrders = Array.from(document.querySelectorAll('input[name="selected_orders[]"]:checked'))
        .map(checkbox => checkbox.value);

    if (selectedOrders.length === 0) {
        alert('Please select at least one order.');
        return;
    }

    if (confirm(`Are you sure you want to ${action.replace('_', ' ')} ${selectedOrders.length} order(s)?`)) {
        // Make AJAX request for bulk action
        fetch('/admin/orders/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                order_ids: selectedOrders
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error performing bulk action');
        });
    }
}

// Show order analytics
function showOrderAnalytics() {
    const modal = new bootstrap.Modal(document.getElementById('analyticsModal'));
    modal.show();

    // Load analytics data
    fetch('/admin/api/orders/items/analysis', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAnalytics(data.analysis);
        } else {
            document.getElementById('analyticsContent').innerHTML = '<div class="alert alert-danger">Error loading analytics</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('analyticsContent').innerHTML = '<div class="alert alert-danger">Error loading analytics</div>';
    });
}

function displayAnalytics(analysis) {
    const content = document.getElementById('analyticsContent');

    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Summary</h6>
                <table class="table table-sm">
                    <tr><td>Total Items:</td><td>${analysis.summary.total_items}</td></tr>
                    <tr><td>Total Weight:</td><td>${analysis.summary.total_weight.toFixed(1)}g</td></tr>
                    <tr><td>Total Value:</td><td>AUD${analysis.summary.total_value.toFixed(2)}</td></tr>
                    <tr><td>Avg Weight:</td><td>${analysis.summary.average_weight.toFixed(1)}g</td></tr>
                    <tr><td>Avg Value:</td><td>AUD${analysis.summary.average_value.toFixed(2)}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Weight Distribution</h6>
                <table class="table table-sm">
                    <tr><td>Under 5g:</td><td>${analysis.weight_distribution.under_5g}</td></tr>
                    <tr><td>5g - 10g:</td><td>${analysis.weight_distribution['5g_to_10g']}</td></tr>
                    <tr><td>10g - 25g:</td><td>${analysis.weight_distribution['10g_to_25g']}</td></tr>
                    <tr><td>25g - 50g:</td><td>${analysis.weight_distribution['25g_to_50g']}</td></tr>
                    <tr><td>Over 50g:</td><td>${analysis.weight_distribution.over_50g}</td></tr>
                </table>
            </div>
        </div>
    `;

    content.innerHTML = html;
}

// Auto-submit filter form on change
document.querySelectorAll('#filterForm select, #filterForm input[type="date"]').forEach(element => {
    element.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Live search with debounce
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});
</script>
@endpush
