{{-- File: resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
            <p class="mb-0 text-muted">Welcome back! Here's what's happening with your jewelry store.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
                <i class="fas fa-plus me-2"></i>Quick Actions
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalProducts">{{ $stats['total_products'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gem fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeOrders">{{ $stats['total_orders'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Metal Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="metalCategories">{{ $stats['total_metal_categories'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Jewelry Types</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="jewelryTypes">{{ $stats['total_categories'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ring fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Live Metal Prices -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Live Metal Prices</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshPrices()">
                        <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="row" id="metalPricesContainer">
                        @if(isset($metalCategories))
                            @foreach($metalCategories as $metal)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-left-warning">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="font-weight-bold text-uppercase">{{ $metal->name }}</h6>
                                                    <small class="text-muted">{{ $metal->symbol }}</small>
                                                </div>
                                                <div class="text-right">
                                                    <div class="h6 mb-0 text-success">
                                                        ${{ number_format($metal->current_price_usd ?? 0, 2) }}/oz
                                                    </div>
                                                    <small class="text-muted">Last updated: {{ $metal->updated_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD-001</td>
                                    <td>John Smith</td>
                                    <td>18K Gold Ring</td>
                                    <td>$1,250.00</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2024-01-15</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-002</td>
                                    <td>Sarah Johnson</td>
                                    <td>Silver Necklace Set</td>
                                    <td>$480.00</td>
                                    <td><span class="badge bg-warning">Processing</span></td>
                                    <td>2024-01-14</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-003</td>
                                    <td>Mike Wilson</td>
                                    <td>Platinum Wedding Band</td>
                                    <td>$2,100.00</td>
                                    <td><span class="badge bg-info">Pending</span></td>
                                    <td>2024-01-13</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </a>
                        <a href="{{ route('admin.metal-categories.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-coins me-2"></i>Add Metal Category
                        </a>
                        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-layer-group me-2"></i>Add Jewelry Type
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-list me-2"></i>Manage Orders
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Metal Price API</span>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Database</span>
                        <span class="badge bg-success">Healthy</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Storage</span>
                        <span class="badge bg-warning">75% Used</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Cache</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>

            <!-- Popular Products -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Popular Products</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">18K Gold Wedding Ring</h6>
                                <small class="text-muted">15 orders this month</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">15</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">Silver Chain Necklace</h6>
                                <small class="text-muted">12 orders this month</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">12</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">Diamond Earrings</h6>
                                <small class="text-muted">8 orders this month</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">8</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus d-block mb-2"></i>
                            New Product
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-shopping-cart d-block mb-2"></i>
                            View Orders
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.metal-categories.index') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-coins d-block mb-2"></i>
                            Metal Prices
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-users d-block mb-2"></i>
                            Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    location.reload();
}

function refreshPrices() {
    const refreshIcon = document.getElementById('refreshIcon');
    refreshIcon.classList.add('fa-spin');
    
    fetch('{{ route("admin.metal-categories.refresh-all-prices") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        refreshIcon.classList.remove('fa-spin');
    });
}
</script>
@endsection