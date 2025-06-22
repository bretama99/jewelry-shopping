@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Reports & Analytics
        </h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="refreshAllData()">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export Reports
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'comprehensive')">
                        <i class="fas fa-file-pdf text-danger"></i> Comprehensive PDF Report
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'financial')">
                        <i class="fas fa-file-excel text-success"></i> Financial Excel Report
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('csv', 'transactions')">
                        <i class="fas fa-file-csv text-info"></i> Transaction CSV Export
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="scheduleReport()">
                        <i class="fas fa-calendar-alt text-warning"></i> Schedule Automated Report
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Report Period & Filters
                    </h6>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-outline-secondary btn-sm" onclick="setDateRange('today')">Today</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setDateRange('week')">This Week</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setDateRange('month')">This Month</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setDateRange('quarter')">This Quarter</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="setDateRange('year')">This Year</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" id="reportFilters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <input type="text" class="form-control" id="dateRange" name="date_range" 
                               value="{{ request('date_range') }}" placeholder="Select date range">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Metal Type</label>
                        <select class="form-select" id="metalFilter" name="metal_filter">
                            <option value="">All Metals</option>
                            <option value="gold" {{ request('metal_filter') == 'gold' ? 'selected' : '' }}>Gold</option>
                            <option value="silver" {{ request('metal_filter') == 'silver' ? 'selected' : '' }}>Silver</option>
                            <option value="platinum" {{ request('metal_filter') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                            <option value="palladium" {{ request('metal_filter') == 'palladium' ? 'selected' : '' }}>Palladium</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Transaction Type</label>
                        <select class="form-select" id="transactionTypeFilter" name="transaction_type">
                            <option value="">All Types</option>
                            <option value="jewelry_sale" {{ request('transaction_type') == 'jewelry_sale' ? 'selected' : '' }}>Jewelry Sales</option>
                            <option value="scrap_purchase" {{ request('transaction_type') == 'scrap_purchase' ? 'selected' : '' }}>Scrap Purchase</option>
                            <option value="bullion_sale" {{ request('transaction_type') == 'bullion_sale' ? 'selected' : '' }}>Bullion Sales</option>
                            <option value="bullion_purchase" {{ request('transaction_type') == 'bullion_purchase' ? 'selected' : '' }}>Bullion Purchase</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="categoryFilter" name="category_filter">
                            <option value="">All Categories</option>
                            <option value="rings" {{ request('category_filter') == 'rings' ? 'selected' : '' }}>Rings</option>
                            <option value="necklaces" {{ request('category_filter') == 'necklaces' ? 'selected' : '' }}>Necklaces</option>
                            <option value="bracelets" {{ request('category_filter') == 'bracelets' ? 'selected' : '' }}>Bracelets</option>
                            <option value="earrings" {{ request('category_filter') == 'earrings' ? 'selected' : '' }}>Earrings</option>
                            <option value="chains" {{ request('category_filter') == 'chains' ? 'selected' : '' }}>Chains</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" id="customerFilter" name="customer_filter" 
                               value="{{ request('customer_filter') }}" placeholder="Search customer...">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                AUD$45,672.83
                            </div>
                            <div class="text-success">
                                <i class="fas fa-arrow-up"></i> 12.5% vs last period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Net Profit
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                AUD$12,843.21
                            </div>
                            <div class="text-success">
                                28.1% margin
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Total Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                287
                            </div>
                            <div class="text-info">
                                AUD$159.21 avg value
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-handshake fa-2x text-gray-300"></i>
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
                                Metal Processed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                1,247.8g
                            </div>
                            <div class="text-warning">
                                Gold: 67.2% | Silver: 32.8%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-weight fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue & Profit Trends</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            View Options
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="updateChart('revenue', 'daily')">Daily View</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChart('revenue', 'weekly')">Weekly View</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChart('revenue', 'monthly')">Monthly View</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Transaction Types Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transaction Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="transactionTypesChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary Tables -->
    <div class="row mb-4">
        <!-- Daily Sales Summary -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Sales Summary</h6>
                    <button class="btn btn-sm btn-outline-success" onclick="exportTable('dailySales', 'csv')">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dailySalesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Transactions</th>
                                    <th>Revenue</th>
                                    <th>Profit</th>
                                    <th>Margin %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Jun 03, 2025</td>
                                    <td>18</td>
                                    <td>AUD$2,847.50</td>
                                    <td>AUD$798.30</td>
                                    <td>28.0%</td>
                                </tr>
                                <tr>
                                    <td>Jun 02, 2025</td>
                                    <td>22</td>
                                    <td>AUD$3,245.80</td>
                                    <td>AUD$921.23</td>
                                    <td>28.4%</td>
                                </tr>
                                <tr>
                                    <td>Jun 01, 2025</td>
                                    <td>15</td>
                                    <td>AUD$2,156.90</td>
                                    <td>AUD$592.40</td>
                                    <td>27.5%</td>
                                </tr>
                                <tr>
                                    <td>May 31, 2025</td>
                                    <td>19</td>
                                    <td>AUD$2,934.60</td>
                                    <td>AUD$821.69</td>
                                    <td>28.0%</td>
                                </tr>
                                <tr>
                                    <td>May 30, 2025</td>
                                    <td>24</td>
                                    <td>AUD$3,687.40</td>
                                    <td>AUD$1,032.47</td>
                                    <td>28.0%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Financial Overview -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Financial Overview</h6>
                    <button class="btn btn-sm btn-outline-success" onclick="exportTable('monthlyFinancial', 'csv')">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="monthlyFinancialTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Sales</th>
                                    <th>Purchases</th>
                                    <th>Net Revenue</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>June 2025</td>
                                    <td>AUD$45,672.83</td>
                                    <td>AUD$28,945.62</td>
                                    <td>AUD$16,727.21</td>
                                    <td>AUD$12,843.21</td>
                                </tr>
                                <tr>
                                    <td>May 2025</td>
                                    <td>AUD$52,198.45</td>
                                    <td>AUD$33,421.87</td>
                                    <td>AUD$18,776.58</td>
                                    <td>AUD$14,892.35</td>
                                </tr>
                                <tr>
                                    <td>April 2025</td>
                                    <td>AUD$38,765.22</td>
                                    <td>AUD$24,556.78</td>
                                    <td>AUD$14,208.44</td>
                                    <td>AUD$11,034.67</td>
                                </tr>
                                <tr>
                                    <td>March 2025</td>
                                    <td>AUD$41,234.56</td>
                                    <td>AUD$26,123.89</td>
                                    <td>AUD$15,110.67</td>
                                    <td>AUD$11,545.68</td>
                                </tr>
                                <tr>
                                    <td>February 2025</td>
                                    <td>AUD$36,987.12</td>
                                    <td>AUD$23,432.45</td>
                                    <td>AUD$13,554.67</td>
                                    <td>AUD$10,356.39</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Transaction Records -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Comprehensive Transaction Records
            </h6>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control" id="transactionSearch" placeholder="Search transactions...">
                    <button class="btn btn-outline-secondary" onclick="searchTransactions()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <button class="btn btn-sm btn-outline-success" onclick="exportTable('transactions', 'csv')">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="transactionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>Order #</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Metal/Category</th>
                            <th>Weight (g)</th>
                            <th>Karat/Purity</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Profit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jun 03, 2025 14:25</td>
                            <td>ORD-2025-00287</td>
                            <td><span class="badge bg-success">Jewelry Sale</span></td>
                            <td>Sarah Johnson</td>
                            <td>Gold Ring</td>
                            <td>4.2</td>
                            <td>18K</td>
                            <td>AUD$63.40</td>
                            <td>AUD$266.28</td>
                            <td>AUD$74.56</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('ORD-2025-00287')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Jun 03, 2025 13:42</td>
                            <td>SCR-2025-00156</td>
                            <td><span class="badge bg-warning">Scrap Purchase</span></td>
                            <td>Michael Chen</td>
                            <td>Gold Scrap</td>
                            <td>12.8</td>
                            <td>14K</td>
                            <td>AUD$49.30</td>
                            <td>AUD$631.04</td>
                            <td>AUD$-631.04</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('SCR-2025-00156')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Jun 03, 2025 12:15</td>
                            <td>BUL-2025-00089</td>
                            <td><span class="badge bg-info">Bullion Sale</span></td>
                            <td>Jennifer Williams</td>
                            <td>Gold Bar</td>
                            <td>31.1</td>
                            <td>999</td>
                            <td>AUD$85.50</td>
                            <td>AUD$2,659.05</td>
                            <td>AUD$212.72</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('BUL-2025-00089')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Jun 03, 2025 11:30</td>
                            <td>ORD-2025-00286</td>
                            <td><span class="badge bg-success">Jewelry Sale</span></td>
                            <td>Robert Davis</td>
                            <td>Silver Necklace</td>
                            <td>8.5</td>
                            <td>925</td>
                            <td>AUD$1.68</td>
                            <td>AUD$14.28</td>
                            <td>AUD$4.00</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('ORD-2025-00286')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Jun 03, 2025 10:45</td>
                            <td>ORD-2025-00285</td>
                            <td><span class="badge bg-success">Jewelry Sale</span></td>
                            <td>Emily Rodriguez</td>
                            <td>Gold Earrings</td>
                            <td>2.8</td>
                            <td>22K</td>
                            <td>AUD$77.50</td>
                            <td>AUD$217.00</td>
                            <td>AUD$60.76</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('ORD-2025-00285')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Transaction pagination">
                <ul class="pagination justify-content-center mt-3">
                    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Profit/Loss Statement -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-pie"></i> Profit & Loss Statement
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success">Revenue Streams</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Jewelry Sales</td>
                            <td class="text-end">AUD$35,672.83</td>
                        </tr>
                        <tr>
                            <td>Bullion Sales</td>
                            <td class="text-end">AUD$8,450.00</td>
                        </tr>
                        <tr>
                            <td>Service Fees</td>
                            <td class="text-end">AUD$1,550.00</td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>Total Revenue</strong></td>
                            <td class="text-end"><strong>AUD$45,672.83</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-danger">Expenses & Costs</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Scrap Metal Purchases</td>
                            <td class="text-end">AUD$18,945.62</td>
                        </tr>
                        <tr>
                            <td>Bullion Purchases</td>
                            <td class="text-end">AUD$10,000.00</td>
                        </tr>
                        <tr>
                            <td>Operating Expenses</td>
                            <td class="text-end">AUD$3,884.00</td>
                        </tr>
                        <tr class="table-danger">
                            <td><strong>Total Expenses</strong></td>
                            <td class="text-end"><strong>AUD$32,829.62</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <h5>Net Profit</h5>
                        <h5 class="text-success">AUD$12,843.21</h5>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Profit Margin</span>
                        <span class="text-success">28.1%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionModalBody">
                <!-- Transaction details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printTransactionDetail()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Progress Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporting Report</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Generating your report...</p>
                <div class="progress">
                    <div class="progress-bar" id="exportProgress" role="progressbar" style="width: 0%"></div>
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
.text-xs {
    font-size: 0.7rem;
}
.font-weight-bold {
    font-weight: 700 !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.bg-success {
    background-color: #1cc88a !important;
}
.bg-warning {
    background-color: #f6c23e !important;
}
.bg-info {
    background-color: #36b9cc !important;
}
.bg-danger {
    background-color: #e74a3b !important;
}
.bg-primary {
    background-color: #4e73df !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// Initialize date range picker
$(document).ready(function() {
    // Date range picker initialization
    $('#dateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        }
    });

    $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Initialize charts
    initializeCharts();
});

// Set predefined date ranges
function setDateRange(period) {
    let startDate, endDate;
    
    switch(period) {
        case 'today':
            startDate = moment();
            endDate = moment();
            break;
        case 'week':
            startDate = moment().startOf('week');
            endDate = moment().endOf('week');
            break;
        case 'month':
            startDate = moment().startOf('month');
            endDate = moment().endOf('month');
            break;
        case 'quarter':
            startDate = moment().startOf('quarter');
            endDate = moment().endOf('quarter');
            break;
        case 'year':
            startDate = moment().startOf('year');
            endDate = moment().endOf('year');
            break;
    }
    
    $('#dateRange').val(startDate.format('MM/DD/YYYY') + ' - ' + endDate.format('MM/DD/YYYY'));
}

// Initialize charts
function initializeCharts() {
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [32000, 41000, 38000, 45000, 52000, 46000],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.3,
                fill: true
            }, {
                label: 'Profit',
                data: [8500, 11200, 10400, 12600, 14800, 12800],
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'AUD + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': AUD + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Transaction Types Pie Chart
    const transactionCtx = document.getElementById('transactionTypesChart').getContext('2d');
    new Chart(transactionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Jewelry Sales', 'Scrap Purchase', 'Bullion Sales', 'Bullion Purchase'],
            datasets: [{
                data: [65, 20, 10, 5],
                backgroundColor: ['#4e73df', '#f6c23e', '#1cc88a', '#e74a3b'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });
}

// Refresh all data
function refreshAllData() {
    const refreshBtn = event.target;
    const originalHtml = refreshBtn.innerHTML;
    
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;
    
    // Simulate data refresh
    setTimeout(() => {
        location.reload();
    }, 1500);
}

// Export functionality
function exportReport(format, type) {
    const modal = new bootstrap.Modal(document.getElementById('exportModal'));
    modal.show();
    
    // Simulate export progress
    let progress = 0;
    const progressBar = document.getElementById('exportProgress');
    
    const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                modal.hide();
                // Show success message
                alert(`${format.toUpperCase()} report generated successfully!`);
            }, 500);
        }
    }, 200);
}

// Export table data
function exportTable(tableType, format) {
    alert(`Exporting ${tableType} data as ${format.toUpperCase()} file...`);
}

// View transaction details
function viewTransaction(orderNumber) {
    // Sample transaction data
    const sampleData = {
        order_number: orderNumber,
        created_at: 'Jun 03, 2025 14:25',
        type: 'Jewelry Sale',
        status: 'Completed',
        status_color: 'success',
        customer_name: 'Sarah Johnson',
        customer_email: 'sarah.johnson@email.com',
        customer_phone: '+61 412 345 678',
        total_amount: '266.28',
        items: [{
            description: 'Gold Ring',
            weight: '4.2',
            karat: '18K',
            unit_price: '63.40',
            total_amount: '266.28'
        }]
    };

    document.getElementById('transactionModalBody').innerHTML = generateTransactionHTML(sampleData);
    const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
    modal.show();
}

function generateTransactionHTML(transaction) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6>Transaction Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Order Number:</strong></td><td>${transaction.order_number}</td></tr>
                    <tr><td><strong>Date:</strong></td><td>${transaction.created_at}</td></tr>
                    <tr><td><strong>Type:</strong></td><td>${transaction.type}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-${transaction.status_color}">${transaction.status}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Customer Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${transaction.customer_name || 'Guest'}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${transaction.customer_email || 'N/A'}</td></tr>
                    <tr><td><strong>Phone:</strong></td><td>${transaction.customer_phone || 'N/A'}</td></tr>
                </table>
            </div>
        </div>
        <hr>
        <h6>Items</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Weight</th>
                        <th>Karat/Purity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${transaction.items.map(item => `
                        <tr>
                            <td>${item.description}</td>
                            <td>${item.weight}g</td>
                            <td>${item.karat || item.purity}</td>
                            <td>AUD${item.unit_price}</td>
                            <td>AUD${item.total_amount}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4"><strong>Total Amount</strong></td>
                        <td><strong>AUD${transaction.total_amount}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

// Search transactions
function searchTransactions() {
    const searchTerm = document.getElementById('transactionSearch').value;
    if (searchTerm.length < 2) {
        alert('Please enter at least 2 characters to search');
        return;
    }
    
    alert(`Searching for: ${searchTerm}`);
}

// Update chart based on view selection
function updateChart(chartType, period) {
    alert(`Updating ${chartType} chart for ${period} view...`);
}

// Schedule automated reports
function scheduleReport() {
    alert('Automated report scheduling feature coming soon!');
}

// Print transaction detail
function printTransactionDetail() {
    const modalBody = document.getElementById('transactionModalBody').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Transaction Details</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    .btn { display: none; }
                }
            </style>
        </head>
        <body class="p-4">
            <h2>Transaction Details</h2>
            ${modalBody}
            <script>window.print();</script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
</script>
@endpush