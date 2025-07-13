<?php

use App\Http\Controllers\Admin\MetalCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExternalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\MetalCategoryController as AdminMetalCategoryController;
use App\Http\Controllers\Admin\SubcategoryController as AdminSubcategoryController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| External Website Routes (Public)
|--------------------------------------------------------------------------
*/

Route::name('external.')->group(function () {
    Route::get('/', [ExternalController::class, 'home'])->name('home');
    Route::get('/about', [ExternalController::class, 'about'])->name('about');
    Route::get('/collections', [ExternalController::class, 'collections'])->name('collections');
    Route::get('/services', [ExternalController::class, 'services'])->name('services');
    Route::get('/contact', [ExternalController::class, 'contact'])->name('contact');
    Route::post('/contact', [ExternalController::class, 'submitContact'])->name('contact.submit');
    Route::get('/help', [ExternalController::class, 'help'])->name('help');
    Route::get('/size-guide', [ExternalController::class, 'sizeGuide'])->name('size-guide');
    Route::get('/care-instructions', [ExternalController::class, 'careInstructions'])->name('care-instructions');
    Route::get('/warranty', [ExternalController::class, 'warranty'])->name('warranty');
    Route::get('/privacy-policy', [ExternalController::class, 'privacy'])->name('privacy');
    Route::get('/terms-of-service', [ExternalController::class, 'terms'])->name('terms');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [UserController::class, 'create'])->name('register');
    Route::post('/register', [UserController::class, 'store']);

    // Password reset routes
    Route::get('/password/reset', 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('/password/email', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('/password/reset/{token}', 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('/password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Public Product Routes
|--------------------------------------------------------------------------
*/

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    Route::get('/{product}/live-price', [ProductController::class, 'getLivePrice'])->name('live-price');
    Route::post('/update-prices', [ProductController::class, 'updatePrices'])->name('update-prices');
    Route::post('/{product}/calculate-price', [ProductController::class, 'calculatePrice'])->name('calculate-price');
});

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->name('api.')->group(function () {
    // Product API routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/metals/{metalSlug}/karats', [ProductController::class, 'getAvailableKarats'])->name('metals.karats');
        Route::get('/metals/{metalSlug}/subcategories', [ProductController::class, 'getSubcategoriesForMetal'])->name('metals.subcategories');
        Route::post('/calculate-scrap-price', [ProductController::class, 'calculateScrapPrice'])->name('calculate-scrap-price');
        Route::get('/customers/search', [ProductController::class, 'searchCustomers'])->name('customers.search');
        Route::post('/orders', [ProductController::class, 'createOrder'])->name('orders.create');
    });

    // Live pricing endpoints
    Route::get('/live-prices', [HomeController::class, 'getLivePrices'])->name('live-prices');
    Route::get('/dashboard-stats', [HomeController::class, 'getDashboardStats'])->name('dashboard-stats');
    Route::post('/calculate-price', [HomeController::class, 'calculateProductPrice'])->name('calculate-price');
    Route::post('/refresh-prices', [HomeController::class, 'refreshMetalPrices'])->name('refresh-prices');

    // Metal price API endpoints
    Route::prefix('metal-prices')->name('metal-prices.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\MetalPriceController::class, 'index'])->name('index');
        Route::get('/{metalSymbol}', [App\Http\Controllers\Api\MetalPriceController::class, 'show'])->name('show');
        Route::post('/refresh', [App\Http\Controllers\Api\MetalPriceController::class, 'refresh'])->name('refresh');
        Route::post('/calculate', [App\Http\Controllers\Api\MetalPriceController::class, 'calculatePrice'])->name('calculate');
    });

    // Search and categories
    Route::get('/search', function (Illuminate\Http\Request $request) {
        $query = $request->get('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $products = \App\Models\Product::where('name', 'like', "%{$query}%")
                ->where('is_active', true)
                ->with('subcategory')
                ->limit(8)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category' => $product->subcategory->name ?? 'Jewelry',
                        'karat' => $product->karat ?? '18',
                        'url' => route('products.show', $product->slug ?? $product->id)
                    ];
                });

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    })->name('search');

    Route::get('/categories', function () {
        try {
            $categories = \App\Models\Subcategory::where('is_active', true)
                ->withCount('products')
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'slug']);
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    })->name('categories');

    // User search for admin
    Route::middleware('auth')->get('/users/search', function (Illuminate\Http\Request $request) {
        $query = $request->get('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $users = \App\Models\User::where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->with('role')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? 'No Role',
                    'status' => $user->status,
                    'avatar' => $user->profile_picture_url,
                    'url' => route('admin.users.show', $user->id)
                ];
            });

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    })->name('users.search');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Main dashboard redirect
    Route::get('/dashboard', function() {
        $user = auth()->user();

        if ($user && $user->role) {
            if ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('customer') || $user->isCustomer()) {
                return redirect()->route('customer.dashboard');
            }
        }

        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    // Internal application home
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Shop route
    Route::get('/shop', function () {
        $products = \App\Models\Product::with('subcategory')->where('is_active', true)->get();
        $categories = \App\Models\Subcategory::where('is_active', true)->get();
        return view('shop.index', compact('products', 'categories'));
    })->name('shop.index');

    // Profile Management Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-picture', [ProfileController::class, 'uploadPicture'])->name('upload-picture');
        Route::delete('/remove-picture', [ProfileController::class, 'removePicture'])->name('remove-picture');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::put('/preferences', [ProfileController::class, 'updatePreferences'])->name('update-preferences');
        Route::get('/activity', [ProfileController::class, 'getActivity'])->name('activity');
        Route::delete('/delete-account', [ProfileController::class, 'deleteAccount'])->name('delete-account');
    });

    // Cart routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/update/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::post('/refresh-prices', [CartController::class, 'refreshPrices'])->name('refresh-prices');
        Route::get('/count', [CartController::class, 'getCartCount'])->name('count');
    });

    // Checkout routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('calculate-shipping');
    });

    // Order API endpoints
    Route::prefix('api/orders')->name('orders.')->group(function () {
        Route::get('/{orderNumber}/status', [OrderController::class, 'getOrderStatus'])->name('status');
        Route::get('/{orderNumber}/items', [OrderController::class, 'getOrderItems'])->name('items');
        Route::patch('/{orderNumber}/items/{orderItem}', [OrderController::class, 'updateOrderItem'])->name('items.update');
        Route::get('/statistics', [OrderController::class, 'getOrderStatistics'])->name('statistics');
    });

    // Customer search endpoints
    Route::prefix('api/customers')->name('api.customers.')->group(function () {
        Route::get('/search', [OrderController::class, 'searchCustomers'])->name('search');
        Route::get('/test', [OrderController::class, 'testCustomerSearch'])->name('test');
    });

    Route::get('/customers/search', [OrderController::class, 'searchCustomers'])->name('customers.search');
});

/*
|--------------------------------------------------------------------------
| Customer Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', function() {
        return view('customer.dashboard');
    })->name('dashboard');

    Route::get('/orders', [OrderController::class, 'customerOrders'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'customerOrderDetail'])->name('orders.show');
    Route::get('/wishlist', function() {
        return view('customer.wishlist');
    })->name('wishlist');
    Route::get('/account', [ProfileController::class, 'customerAccount'])->name('account');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [AdminDashboardController::class, 'getStats'])->name('stats');

    // Reports
    Route::get('/reports', function() {
        return view('admin.reports.index');
    })->name('reports.index');

    // Trading routes
    Route::get('/trading', function () {
        return view('admin.trading.index');
    })->name('trading.index');

    // Metal Categories Management
    Route::prefix('metal-categories')->name('metal-categories.')->group(function () {
        Route::get('/', [AdminMetalCategoryController::class, 'index'])->name('index');
        Route::get('/create', [AdminMetalCategoryController::class, 'create'])->name('create');
        Route::post('/', [AdminMetalCategoryController::class, 'store'])->name('store');
        Route::get('/{metalCategory}', [AdminMetalCategoryController::class, 'show'])->name('show');
        Route::get('/{metalCategory}/edit', [AdminMetalCategoryController::class, 'edit'])->name('edit');
        Route::put('/{metalCategory}', [AdminMetalCategoryController::class, 'update'])->name('update');
        Route::delete('/{metalCategory}', [AdminMetalCategoryController::class, 'destroy'])->name('destroy');

        // AJAX routes
        Route::patch('/{metalCategory}/toggle-status', [AdminMetalCategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{metalCategory}/duplicate', [AdminMetalCategoryController::class, 'duplicate'])->name('duplicate');
        Route::post('/bulk-action', [AdminMetalCategoryController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [AdminMetalCategoryController::class, 'export'])->name('export');

        // Price management routes
        Route::get('/live-prices', [AdminMetalCategoryController::class, 'getLivePrices'])->name('live-prices');
        Route::post('/refresh-price/{symbol}', [AdminMetalCategoryController::class, 'refreshPrice'])->name('refresh-price');
        Route::post('/refresh-all-prices', [AdminMetalCategoryController::class, 'refreshAllPrices'])->name('refresh-all-prices');
        Route::get('/live-price/{symbol}', [AdminMetalCategoryController::class, 'getLivePrice'])->name('live-price');
        Route::post('/{metalCategory}/update-price', [AdminMetalCategoryController::class, 'updatePrice'])->name('update-price');
        Route::post('/update-all-prices', [AdminMetalCategoryController::class, 'updateAllPrices'])->name('update-all-prices');
    });

    // Subcategories Management
    Route::prefix('subcategories')->name('subcategories.')->group(function () {
        Route::get('/', [AdminSubcategoryController::class, 'index'])->name('index');
        Route::get('/create', [AdminSubcategoryController::class, 'create'])->name('create');
        Route::post('/', [AdminSubcategoryController::class, 'store'])->name('store');
        Route::get('/{subcategory}', [AdminSubcategoryController::class, 'show'])->name('show');
        Route::get('/{subcategory}/edit', [AdminSubcategoryController::class, 'edit'])->name('edit');
        Route::put('/{subcategory}', [AdminSubcategoryController::class, 'update'])->name('update');
        Route::delete('/{subcategory}', [AdminSubcategoryController::class, 'destroy'])->name('destroy');

        // AJAX routes
        Route::patch('/{subcategory}/toggle-status', [AdminSubcategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{subcategory}/toggle-feature', [AdminSubcategoryController::class, 'toggleFeature'])->name('toggle-feature');
        Route::post('/{subcategory}/duplicate', [AdminSubcategoryController::class, 'duplicate'])->name('duplicate');
        Route::post('/bulk-action', [AdminSubcategoryController::class, 'bulkAction'])->name('bulk-action');

        // Metal category relationship routes
        Route::get('/for-metal/{metalCategory}', [AdminSubcategoryController::class, 'getSubcategoriesForMetal'])->name('for-metal');
        Route::put('/{subcategory}/metal/{metalCategory}/settings', [AdminSubcategoryController::class, 'updateMetalCategorySettings'])->name('metal-settings');
        Route::get('/{subcategoryId}/details', function($subcategoryId) {
            $subcategory = \App\Models\Subcategory::findOrFail($subcategoryId);
            return response()->json([
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'default_labor_cost' => $subcategory->default_labor_cost,
                'default_profit_margin' => $subcategory->default_profit_margin,
            ]);
        })->name('details');
    });

    // Products Management
    Route::resource('products', AdminProductController::class);
    Route::prefix('products')->name('products.')->group(function () {
        // Pricing endpoints
        Route::get('/{product}/pricing', [AdminProductController::class, 'getPricing'])->name('get-pricing');
        Route::patch('/{product}/update-pricing', [AdminProductController::class, 'updatePricing'])->name('update-pricing');
        Route::post('/calculate-price', [AdminProductController::class, 'calculatePrice'])->name('calculate-price');

        // Metal and category helper routes
        Route::get('/metal/{metalCategoryId}/karats', [AdminProductController::class, 'getAvailableKarats'])->name('metal-karats');
        Route::get('/metal/{metalCategoryId}/subcategories', [AdminProductController::class, 'getSubcategoriesForMetal'])->name('metal-subcategories');

        // Bulk operations
        Route::post('/bulk-action', [AdminProductController::class, 'bulkAction'])->name('bulk-action');

        // Import/Export
        Route::get('/export', [AdminProductController::class, 'export'])->name('export');
        Route::post('/import', [AdminProductController::class, 'import'])->name('import');

        // Metal price management
        Route::get('/metal-prices', [AdminProductController::class, 'getMetalPrices'])->name('metal-prices');
        Route::post('/update-metal-prices', [AdminProductController::class, 'updateMetalPrices'])->name('update-metal-prices');

        // Product management actions
        Route::patch('/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{product}/toggle-feature', [AdminProductController::class, 'toggleFeature'])->name('toggle-feature');
        Route::post('/{product}/duplicate', [AdminProductController::class, 'duplicate'])->name('duplicate');
        Route::post('/refresh-prices', [AdminProductController::class, 'refreshPrices'])->name('refresh-prices');

        // Gallery management
        Route::get('/{product}/gallery', [AdminProductController::class, 'gallery'])->name('gallery');
        Route::post('/{product}/gallery', [AdminProductController::class, 'uploadGalleryImage'])->name('gallery.upload');
        Route::delete('/{product}/gallery/{image}', [AdminProductController::class, 'deleteGalleryImage'])->name('gallery.delete');
    });

    // Orders Management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::delete('/{order}', [AdminOrderController::class, 'destroy'])->name('destroy');
        Route::patch('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('update-tracking');
        Route::post('/{order}/note', [AdminOrderController::class, 'addNote'])->name('add-note');
        Route::get('/{order}/receipt', [AdminOrderController::class, 'receipt'])->name('receipt');
        Route::post('/bulk-action', [AdminOrderController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/bulk-update', [AdminOrderController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
        Route::patch('/{order}/items/{orderItem}', [AdminOrderController::class, 'updateOrderItem'])->name('items.update');
        Route::delete('/{order}/items/{orderItem}', [AdminOrderController::class, 'deleteOrderItem'])->name('items.delete');
        Route::get('/api/items/analysis', [AdminOrderController::class, 'getOrderItemsAnalysis'])->name('items.analysis');
    });

    // Users Management
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::prefix('users')->name('users.')->group(function () {
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('toggle-admin');
        Route::post('/{user}/assign-role', [UserController::class, 'assignRole'])->name('assign-role');
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [UserController::class, 'export'])->name('export');
        Route::delete('/{user}/remove-avatar', function(\App\Models\User $user) {
            if ($user->profile_picture && \Illuminate\Support\Facades\File::exists(public_path('images/users/' . $user->profile_picture))) {
                \Illuminate\Support\Facades\File::delete(public_path('images/users/' . $user->profile_picture));
                $user->update(['profile_picture' => null]);
                return response()->json(['success' => true, 'message' => 'Avatar removed successfully']);
            }
            return response()->json(['success' => false, 'message' => 'No avatar to remove']);
        })->name('remove-avatar');
        Route::get('/stats', function () {
            return response()->json([
                'success' => true,
                'stats' => [
                    'total_users' => \App\Models\User::count(),
                    'active_users' => \App\Models\User::where('status', 'active')->count(),
                    'inactive_users' => \App\Models\User::where('status', 'inactive')->count(),
                    'suspended_users' => \App\Models\User::where('status', 'suspended')->count(),
                    'admin_users' => \App\Models\User::where('is_admin', true)->count(),
                    'customer_users' => \App\Models\User::where('is_admin', false)->count(),
                    'users_with_avatars' => \App\Models\User::whereNotNull('profile_picture')->count(),
                    'recent_users' => \App\Models\User::where('created_at', '>=', now()->subDays(7))->count(),
                ]
            ]);
        })->name('stats');
    });

    // Roles Management
    Route::resource('roles', RoleController::class);
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::patch('/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('duplicate');
        Route::post('/bulk-action', [RoleController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [RoleController::class, 'export'])->name('export');
    });

    // Dashboard API endpoints
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/system-status', [AdminDashboardController::class, 'getSystemStatus'])->name('system-status');
        Route::get('/analytics', [AdminDashboardController::class, 'getAnalytics'])->name('analytics');
        Route::get('/reports/sales', [AdminDashboardController::class, 'getSalesReport'])->name('reports.sales');
        Route::get('/reports/inventory', [AdminDashboardController::class, 'getInventoryReport'])->name('reports.inventory');
        Route::get('/export', [AdminDashboardController::class, 'exportData'])->name('export');

        Route::get('/gold-prices', function() {
            try {
                if (class_exists('\App\Services\KitcoApiService')) {
                    $kitcoService = app(\App\Services\KitcoApiService::class);
                    return response()->json([
                        'success' => true,
                        'goldPrices' => $kitcoService->getCurrentPrices()
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'goldPrices' => [
                        '10' => 35.20,
                        '14' => 49.30,
                        '18' => 63.40,
                        '22' => 77.50,
                        '24' => 85.50
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'goldPrices' => [
                        '10' => 35.20,
                        '14' => 49.30,
                        '18' => 63.40,
                        '22' => 77.50,
                        '24' => 85.50
                    ]
                ]);
            }
        })->name('gold-prices');

        Route::get('/api-status', function() {
            try {
                if (class_exists('\App\Services\KitcoApiService')) {
                    $kitcoService = app(\App\Services\KitcoApiService::class);
                    $kitcoService->getCurrentGoldPrice();
                    return response()->json(['success' => true, 'status' => 'connected']);
                }
                return response()->json(['success' => true, 'status' => 'connected']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'status' => 'disconnected']);
            }
        })->name('api-status');

        Route::get('/quick-stats', function() {
            try {
                $stats = [
                    'total_products' => \App\Models\Product::count(),
                    'active_products' => \App\Models\Product::where('is_active', true)->count(),
                    'featured_products' => \App\Models\Product::where('is_featured', true)->count(),
                    'total_categories' => \App\Models\Subcategory::count(),
                    'total_users' => \App\Models\User::count(),
                    'active_users' => \App\Models\User::where('status', 'active')->count(),
                    'users_with_avatars' => \App\Models\User::whereNotNull('profile_picture')->count(),
                ];

                return response()->json(['success' => true, 'stats' => $stats]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        })->name('quick-stats');
    });
});

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware(['auth', 'admin'])->name('api.')->group(function () {
    // Metal Categories API
    Route::prefix('metal-categories')->name('metal-categories.')->group(function () {
        Route::get('/', [AdminMetalCategoryController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [AdminMetalCategoryController::class, 'apiShow'])->name('show');
    });

    // Metal-specific configuration APIs
    Route::prefix('metals')->name('metals.')->group(function () {
        Route::get('/{metalSlug}/scrap-margins', [AdminMetalCategoryController::class, 'getScrapMargins'])->name('scrap-margins');
        Route::get('/{metalSlug}/bullion-premium', [AdminMetalCategoryController::class, 'getBullionPremium'])->name('bullion-premium');
        Route::get('/{metalSlug}/bullion-margin', [AdminMetalCategoryController::class, 'getBullionMargin'])->name('bullion-margin');
    });

    // Subcategories API
    Route::prefix('subcategories')->name('subcategories.')->group(function () {
        Route::get('/', [AdminSubcategoryController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [AdminSubcategoryController::class, 'apiShow'])->name('show');
    });

    // Products API
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [AdminProductController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [AdminProductController::class, 'apiShow'])->name('show');
        Route::post('/calculate-price', [AdminProductController::class, 'calculatePrice'])->name('calculate-price');
        Route::get('/metal/{metalCategoryId}/karats', [AdminProductController::class, 'getAvailableKarats'])->name('metal-karats');
    });

    // Company Information API
    Route::get('/company-info', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'name' => config('app.company_name', 'Premium Gold Trading Co.'),
                'address' => config('app.company_address', '123 Gold Street, Brisbane QLD 4000'),
                'phone' => config('app.company_phone', '+61 7 3123 4567'),
                'email' => config('app.company_email', 'info@goldtrading.com.au'),
                'abn' => config('app.company_abn', '12 345 678 901'),
            ]
        ]);
    })->name('company-info');

    // Bullion Sizes API
    Route::get('/bullion-sizes', function () {
        return response()->json([
            'success' => true,
            'data' => [
                '1g' => ['weight' => 1, 'type' => 'gram', 'display' => '1 Gram'],
                '2.5g' => ['weight' => 2.5, 'type' => 'gram', 'display' => '2.5 Grams'],
                '5g' => ['weight' => 5, 'type' => 'gram', 'display' => '5 Grams'],
                '10g' => ['weight' => 10, 'type' => 'gram', 'display' => '10 Grams'],
                '20g' => ['weight' => 20, 'type' => 'gram', 'display' => '20 Grams'],
                '1oz' => ['weight' => 31.1035, 'type' => 'ounce', 'display' => '1 Troy Ounce'],
                '50g' => ['weight' => 50, 'type' => 'gram', 'display' => '50 Grams'],
                '100g' => ['weight' => 100, 'type' => 'gram', 'display' => '100 Grams'],
                '250g' => ['weight' => 250, 'type' => 'gram', 'display' => '250 Grams'],
                '500g' => ['weight' => 500, 'type' => 'gram', 'display' => '500 Grams'],
                '1kg' => ['weight' => 1000, 'type' => 'gram', 'display' => '1 Kilogram'],
            ]
        ]);
    })->name('bullion-sizes');
});

/*
|--------------------------------------------------------------------------
| Trading System Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/trading', function () {
        return view('admin.trading.index');
    })->name('trading.index');
});

/*
|--------------------------------------------------------------------------
| Image Serving Routes
|--------------------------------------------------------------------------
*/

// Default avatar route
Route::get('/images/default-avatar', function () {
    $path = public_path('images/default-avatar.png');

    if (!file_exists($path)) {
        if (extension_loaded('gd')) {
            $image = imagecreatetruecolor(200, 200);
            $bgColor = imagecolorallocate($image, 108, 117, 125);
            $textColor = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $bgColor);
            imagefilledellipse($image, 100, 70, 60, 60, $textColor);
            imagefilledarc($image, 100, 140, 120, 80, 0, 180, $textColor, IMG_ARC_PIE);

            ob_start();
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();
            imagedestroy($image);

            return response($imageData, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
        abort(404);
    }

    return response()->file($path, [
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->name('default-avatar');

// Default product image placeholder
Route::get('/images/products/default-placeholder.jpg', function () {
    $customDefault = public_path('images/products/placeholder.jpg');
    if (file_exists($customDefault)) {
        return response()->file($customDefault, [
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    if (extension_loaded('gd')) {
        $width = 300;
        $height = 300;
        $image = imagecreate($width, $height);
        $bgColor = imagecolorallocate($image, 248, 249, 250);
        $borderColor = imagecolorallocate($image, 206, 212, 218);
        $textColor = imagecolorallocate($image, 108, 117, 125);
        $iconColor = imagecolorallocate($image, 173, 181, 189);

        imagefill($image, 0, 0, $bgColor);
        imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);

        $centerX = $width / 2;
        $centerY = $height / 2;
        $cameraWidth = 80;
        $cameraHeight = 60;
        $cameraX = $centerX - $cameraWidth/2;
        $cameraY = $centerY - $cameraHeight/2 + 10;
        imagefilledrectangle($image, $cameraX, $cameraY, $cameraX + $cameraWidth, $cameraY + $cameraHeight, $iconColor);

        $lensRadius = 20;
        imagefilledellipse($image, $centerX, $centerY + 10, $lensRadius * 2, $lensRadius * 2, $bgColor);
        imageellipse($image, $centerX, $centerY + 10, $lensRadius * 2, $lensRadius * 2, $iconColor);

        imagefilledrectangle($image, $centerX - 10, $cameraY - 8, $centerX + 10, $cameraY, $iconColor);

        if (function_exists('imagestring')) {
            $text1 = 'No Image';
            $text2 = 'Available';
            $textX1 = $centerX - (strlen($text1) * 10) / 2;
            $textX2 = $centerX - (strlen($text2) * 10) / 2;
            $textY = $centerY + 50;
            imagestring($image, 4, $textX1, $textY, $text1, $textColor);
            imagestring($image, 4, $textX2, $textY + 20, $text2, $textColor);
        }

        ob_start();
        imagejpeg($image, null, 85);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        return response($imageData, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    return response($pixel, 200, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->name('default-product-image');

/*
|--------------------------------------------------------------------------
| Utility Routes
|--------------------------------------------------------------------------
*/

Route::get('/coming-soon', function() {
    return view('coming-soon');
})->name('coming-soon');

// Fallback route (must be last)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
