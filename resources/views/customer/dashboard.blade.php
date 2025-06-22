{{-- File: resources/views/customer/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Customer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Welcome back, {{ auth()->user()->first_name }}!</h1>
            <p class="mb-0 text-muted">Manage your jewelry orders and explore our latest collections.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('shop.index') }}" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Shop Now
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Spent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$4,250</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Wishlist Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Recent Orders -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
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
                                    <td>18K Gold Ring</td>
                                    <td>$1,250.00</td>
                                    <td><span class="badge bg-success">Delivered</span></td>
                                    <td>2024-01-15</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">Reorder</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-002</td>
                                    <td>Silver Necklace Set</td>
                                    <td>$480.00</td>
                                    <td><span class="badge bg-warning">Shipping</span></td>
                                    <td>2024-01-14</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">Track</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-003</td>
                                    <td>Platinum Wedding Band</td>
                                    <td>$2,100.00</td>
                                    <td><span class="badge bg-info">Processing</span></td>
                                    <td>2024-01-13</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Current Gold Prices -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Gold Prices</h6>
                    <small class="text-muted">Updated every 10 minutes</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body py-3 text-center">
                                    <h6 class="font-weight-bold">14K Gold</h6>
                                    <div class="h5 text-success">$49.30</div>
                                    <small class="text-muted">per gram</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body py-3 text-center">
                                    <h6 class="font-weight-bold">18K Gold</h6>
                                    <div class="h5 text-success">$63.40</div>
                                    <small class="text-muted">per gram</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body py-3 text-center">
                                    <h6 class="font-weight-bold">22K Gold</h6>
                                    <div class="h5 text-success">$77.50</div>
                                    <small class="text-muted">per gram</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body py-3 text-center">
                                    <h6 class="font-weight-bold">24K Gold</h6>
                                    <div class="h5 text-success">$85.50</div>
                                    <small class="text-muted">per gram</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">Prices are in AUD and subject to market fluctuations</small>
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
                        <a href="{{ route('shop.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Jewelry
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Order History
                        </a>
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>Edit Profile
                        </a>
                        <a href="#" class="btn btn-outline-info">
                            <i class="fas fa-headset me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('images/users/' . auth()->user()->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 class="rounded-circle" 
                                 width="80" 
                                 height="80">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        @endif
                    </div>
                    <div class="text-center">
                        <h6 class="font-weight-bold">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h6>
                        <p class="text-muted mb-2">{{ auth()->user()->email }}</p>
                        <small class="text-muted">Member since {{ auth()->user()->created_at->format('M Y') }}</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Account Status</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Loyalty Level</span>
                        <span class="badge bg-warning">Gold Member</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Rewards Points</span>
                        <span class="badge bg-info">1,250 pts</span>
                    </div>
                </div>
            </div>

            <!-- Wishlist Preview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Wishlist</h6>
                    <a href="#" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">Diamond Engagement Ring</h6>
                                <small class="text-muted">18K White Gold</small>
                            </div>
                            <div class="text-right">
                                <div class="text-primary font-weight-bold">$3,200</div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Add to Cart</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">Pearl Necklace</h6>
                                <small class="text-muted">Sterling Silver</small>
                            </div>
                            <div class="text-right">
                                <div class="text-primary font-weight-bold">$580</div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Add to Cart</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <h6 class="mb-1">Gold Bracelet</h6>
                                <small class="text-muted">22K Yellow Gold</small>
                            </div>
                            <div class="text-right">
                                <div class="text-primary font-weight-bold">$1,450</div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Products -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recommended for You</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-ring text-dark"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Wedding Ring Set</h6>
                                            <small class="text-muted">Based on your recent orders</small>
                                            <div class="text-success font-weight-bold">$2,800</div>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-gem text-white"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Sapphire Earrings</h6>
                                            <small class="text-muted">Trending in your area</small>
                                            <div class="text-success font-weight-bold">$1,200</div>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Offers -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4 border-left-success">
                <div class="card-header py-3 bg-gradient-success text-white">
                    <h6 class="m-0 font-weight-bold">Special Offers & Promotions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-percentage fa-2x text-dark"></i>
                                </div>
                                <h5 class="font-weight-bold">15% Off Gold Jewelry</h5>
                                <p class="text-muted">Valid until end of month</p>
                                <a href="#" class="btn btn-warning">Shop Now</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-shipping-fast fa-2x text-white"></i>
                                </div>
                                <h5 class="font-weight-bold">Free Shipping</h5>
                                <p class="text-muted">On orders over $500</p>
                                <a href="#" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-star fa-2x text-white"></i>
                                </div>
                                <h5 class="font-weight-bold">Loyalty Rewards</h5>
                                <p class="text-muted">Earn points with every purchase</p>
                                <a href="#" class="btn btn-info">View Rewards</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh gold prices every 5 minutes
setInterval(function() {
    fetch('/api/live-prices')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.prices.gold) {
                // Update gold prices if needed
                console.log('Gold prices updated');
            }
        })
        .catch(error => console.error('Error fetching prices:', error));
}, 300000); // 5 minutes
</script>
@endsection