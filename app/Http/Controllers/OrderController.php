<?php
// File: app/Http/Controllers/OrderController.php - COMPLETE FINAL VERSION

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::where('user_id', $user->id)
            ->with(['orderItems.product'])
            ->withCount('orderItems')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(10)->withQueryString();

        // Get order statistics using your existing model methods
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)->pending()->count(),
            'completed_orders' => Order::where('user_id', $user->id)->completed()->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->sum('total_amount'),
            'total_items' => OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'total_weight' => OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->sum('weight')
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    public function show($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['orderItems.product', 'user'])
            ->firstOrFail();

        // Use your existing model properties
        $orderStats = [
            'total_items' => $order->items_count, // Your existing accessor
            'total_weight' => $order->total_weight, // Your existing accessor
            'average_karat' => $order->orderItems->avg('karat'),
            'highest_value_item' => $order->orderItems->max('subtotal'),
            'categories' => $order->orderItems->pluck('category_name')->unique()->values(),
            'timeline_events' => $order->getTimelineEvents(), // Your existing method
            'estimated_delivery' => $order->getEstimatedDeliveryDate(), // Your existing method
        ];

        return view('orders.show', compact('order', 'orderStats'));
    }

    public function track($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Use your existing timeline method
        $trackingSteps = $order->getTimelineEvents();

        return view('orders.track', compact('order', 'trackingSteps'));
    }

    public function cancel(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->is_cancellable) { // Your existing accessor
            return back()->with('error', 'This order cannot be cancelled.');
        }

        // Use your existing updateStatus method
        $order->updateStatus('cancelled', 'Cancelled by customer: ' . ($request->reason ?? 'No reason provided'));

        return back()->with('success', 'Order cancelled successfully.');
    }

    public function reorder($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with('orderItems.product')
            ->firstOrFail();

        if (!$order->canBeReordered()) { // Your existing method
            return back()->with('error', 'This order cannot be reordered.');
        }

        $sessionId = session()->getId();
        $userId = Auth::id();

        $addedItems = 0;
        $failedItems = [];

        foreach ($order->orderItems as $orderItem) {
            try {
                if ($orderItem->product && $orderItem->product->status === 'active') {
                    // Check if product still exists and is available
                    $product = Product::find($orderItem->product_id);

                    if ($product) {
                        // Assuming you have a Cart model or service
                        \App\Models\Cart::addItem(
                            $orderItem->product_id,
                            $orderItem->weight,
                            $sessionId,
                            $userId
                        );
                        $addedItems++;
                    } else {
                        $failedItems[] = $orderItem->product_name . ' (Product no longer available)';
                    }
                } else {
                    $failedItems[] = $orderItem->product_name . ' (Product unavailable or discontinued)';
                }
            } catch (\Exception $e) {
                $failedItems[] = $orderItem->product_name . ' (Error: ' . $e->getMessage() . ')';
            }
        }

        $message = "Added {$addedItems} items to your cart.";
        if (!empty($failedItems)) {
            $message .= " Could not add: " . implode(', ', $failedItems);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function downloadReceipt($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['orderItems.product'])
            ->firstOrFail();

        // For now, return a simple response. You can implement PDF generation later
        return response()->json([
            'success' => true,
            'message' => 'Receipt download would start here',
            'order' => $order->getOrderSummary() // Your existing method
        ]);
    }

    public function getOrderStatus($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->select(['order_number', 'status', 'tracking_number', 'shipped_at', 'delivered_at'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label, // Your existing accessor
                'status_color' => $order->status_color, // Your existing accessor
                'tracking_number' => $order->tracking_number,
                'shipped_at' => $order->shipped_at?->format('M j, Y'),
                'delivered_at' => $order->delivered_at?->format('M j, Y'),
                'is_cancellable' => $order->is_cancellable, // Your existing accessor
                'estimated_delivery' => $order->getEstimatedDeliveryDate()->format('M j, Y')
            ]
        ]);
    }

    // FIXED: Store method with proper order number generation
    public function store(Request $request)
    {
        try {
            Log::info('Order creation attempt', ['request_data' => $request->all()]);

            $request->validate([
                'customer_first_name' => 'required|string|max:255',
                'customer_last_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_passport_id' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required',
                'items.*.product_name' => 'required|string',
                'items.*.weight' => 'required|numeric|min:0.1',
                'items.*.price_per_gram' => 'required|numeric|min:0',
                'items.*.karat' => 'required|string',
                'items.*.category_name' => 'nullable|string',
                'gold_price_24k' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Find or create customer
            $customer = User::where('email', $request->customer_email)->first();

            if (!$customer) {
                $customer = User::create([
                    'first_name' => $request->customer_first_name,
                    'last_name' => $request->customer_last_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                    'passport_id_number' => $request->customer_passport_id,
                    'password' => bcrypt('temp123'),
                    'status' => 'active'
                ]);
            }

            // Calculate totals in controller
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += floatval($item['weight']) * floatval($item['price_per_gram']);
            }

            $taxAmount = $subtotal * 0.10; // 10% tax
            $shippingCost = $subtotal > 500 ? 0 : 25;
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            // Create order WITHOUT order_number first to get unique ID
            $order = new Order();
            $order->user_id = $customer->id;
            $order->customer_name = $request->customer_first_name . ' ' . $request->customer_last_name;
            $order->customer_email = $request->customer_email;
            $order->customer_phone = $request->customer_phone ?: '';
            $order->billing_address = 'Default Address';
            $order->shipping_address = 'Default Address';
            $order->subtotal = $subtotal;
            $order->tax_amount = $taxAmount;
            $order->shipping_cost = $shippingCost;
            $order->total_amount = $totalAmount;
            $order->currency = 'AUD';
            $order->status = 'pending';
            $order->payment_status = 'pending';
            $order->payment_method = 'pending';
            $order->notes = $request->notes;
            $order->gold_price_at_order = $request->gold_price_24k;
            $order->market_data = ['timestamp' => now()->toISOString()];
            // Explicitly NOT setting order_number here
            $order->save();

            // FIXED: Generate unique order number using the guaranteed unique order ID
            $year = date('Y');
            $month = date('m');
            $orderNumber = sprintf('ORD-%s%s-%04d', $year, $month, $order->id);

            // Update with the order number (this will always be unique because order ID is unique)
            $order->order_number = $orderNumber;
            $order->save();

            Log::info('Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount
            ]);

            // Create order items directly
            foreach ($request->items as $item) {
                $itemSubtotal = floatval($item['weight']) * floatval($item['price_per_gram']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => 'SKU-' . $item['product_id'] . '-' . time(),
                    'karat' => str_replace('K', '', $item['karat']), // Remove 'K' suffix
                    'category_name' => $item['category_name'] ?? 'Jewelry',
                    'product_description' => $item['product_name'],
                    'product_image' => $item['product_image'] ?? '/images/products/default-placeholder.jpg',
                    'weight' => floatval($item['weight']),
                    'price_per_gram' => floatval($item['price_per_gram']),
                    'gold_price' => $request->gold_price_24k,
                    'labor_cost' => 0,
                    'profit_margin' => 0,
                    'subtotal' => $itemSubtotal,
                    'product_features' => [],
                    'pricing_breakdown' => [
                        'weight' => floatval($item['weight']),
                        'price_per_gram' => floatval($item['price_per_gram']),
                        'subtotal' => $itemSubtotal,
                        'calculated_at' => now()->toISOString()
                    ]
                ]);

                Log::info('Order item created', [
                    'product_id' => $item['product_id'],
                    'weight' => $item['weight'],
                    'subtotal' => $itemSubtotal
                ]);
            }

            DB::commit();

            Log::info('Order creation successful', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'data' => [
                    'order' => $order->load('orderItems'),
                    'order_number' => $order->order_number
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Order validation failed', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error placing order: ' . $e->getMessage()
            ], 500);
        }
    }

    // FINAL: Search customers using your existing User model
    public function searchCustomers(Request $request)
    {
        try {
            $search = $request->get('query', $request->get('q', ''));

            Log::info('Customer search query: ' . $search);

            if (strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Query too short'
                ]);
            }

            // Use your existing User model's search scope and active scope
            $customers = User::search($search) // Your existing scope
                ->active() // Your existing scope
                ->limit(10)
                ->get()
                ->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'full_name' => $customer->full_name, // Your existing accessor
                        'name' => $customer->name, // Your existing accessor
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'passport_id_number' => $customer->passport_id_number,
                        'passport_id' => $customer->passport_id_number, // Alias for compatibility
                        'status' => $customer->status,
                        'role_name' => $customer->role_name, // Your existing accessor
                    ];
                });

            Log::info('Customer search results: ' . $customers->count() . ' found');

            return response()->json([
                'success' => true,
                'data' => $customers,
                'query' => $search,
                'count' => $customers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Customer search error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Search failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test endpoint to debug customer search and check your existing models
     */
    public function testCustomerSearch()
    {
        try {
            // Check if your models work as expected
            $userCount = User::count();
            $activeUsers = User::active()->count(); // Your scope
            $sampleUsers = User::limit(5)->get(['id', 'first_name', 'last_name', 'email', 'phone', 'passport_id_number', 'status']);

            // Test your existing model features
            $modelFeatures = [
                'has_search_scope' => method_exists(User::class, 'scopeSearch'),
                'has_active_scope' => method_exists(User::class, 'scopeActive'),
                'has_full_name_accessor' => method_exists(User::class, 'getFullNameAttribute'),
                'has_name_accessor' => method_exists(User::class, 'getNameAttribute'),
                'has_role_relationship' => method_exists(User::class, 'role'),
                'order_model_methods' => [
                    'has_create_from_cart' => method_exists(Order::class, 'createFromCart'),
                    'has_timeline_events' => method_exists(Order::class, 'getTimelineEvents'),
                    'has_order_summary' => method_exists(Order::class, 'getOrderSummary'),
                    'has_is_cancellable' => method_exists(Order::class, 'getIsCancellableAttribute'),
                ]
            ];

            return response()->json([
                'success' => true,
                'total_users' => $userCount,
                'active_users' => $activeUsers,
                'sample_users' => $sampleUsers,
                'model_features' => $modelFeatures,
                'recent_orders' => Order::recent(7)->count(), // Your scope
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Model compatibility check failed'
            ], 500);
        }
    }

    // Get order items for a specific order using your existing accessors
    public function getOrderItems($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with('orderItems.product')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'order_items' => $order->orderItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'karat' => $item->karat,
                    'karat_label' => $item->karat_label, // Your existing accessor
                    'category' => $item->category_name,
                    'weight' => $item->formatted_weight, // Your existing accessor
                    'price_per_gram' => $item->formatted_price_per_gram, // Your existing accessor
                    'subtotal' => $item->formatted_subtotal, // Your existing accessor
                    'product_image_url' => $item->product_image_url, // Your existing accessor
                ];
            })
        ]);
    }

    // Update order item using your existing methods
    public function updateOrderItem(Request $request, $orderNumber, OrderItem $orderItem)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Verify the order item belongs to this order
        if ($orderItem->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order item does not belong to this order'
            ], 400);
        }

        if (!$order->is_editable) { // Your existing accessor
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be edited'
            ], 400);
        }

        $request->validate([
            'weight' => 'sometimes|numeric|min:0.1',
            'price_per_gram' => 'sometimes|numeric|min:0',
            'special_instructions' => 'sometimes|nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            if ($request->has('weight')) {
                $orderItem->weight = $request->weight;
            }

            if ($request->has('price_per_gram')) {
                $orderItem->price_per_gram = $request->price_per_gram;
            }

            if ($request->has('special_instructions')) {
                $orderItem->special_instructions = $request->special_instructions;
            }

            $orderItem->save(); // Your model will auto-calculate subtotal

            // Use your existing method to recalculate order totals
            $order->calculateTotals()->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order item updated successfully',
                'order_item' => $orderItem,
                'new_order_total' => $order->formatted_total // Your existing accessor
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error updating order item: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get order statistics using your existing model methods
    public function getOrderStatistics()
    {
        $user = Auth::user();

        // Use your existing Order scopes and methods
        $recentStats = Order::getRecentStats(30); // Your existing static method
        $userOrders = Order::where('user_id', $user->id)->get();
        $userOrderItems = OrderItem::whereHas('order', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->get();

        return response()->json([
            'success' => true,
            'statistics' => [
                'recent_stats' => $recentStats, // Your existing method
                'user_orders' => [
                    'total' => $userOrders->count(),
                    'pending' => $userOrders->where('status', 'pending')->count(),
                    'completed' => $userOrders->whereIn('status', ['delivered'])->count(),
                    'total_value' => $userOrders->sum('total_amount')
                ],
                'user_items' => [
                    'total_items' => $userOrderItems->count(),
                    'total_weight' => $userOrderItems->sum('weight'),
                    'average_weight' => $userOrderItems->avg('weight'),
                    'popular_categories' => $userOrderItems->groupBy('category_name')
                        ->map(function($items) {
                            return $items->count();
                        })->sortDesc()->take(5),
                    'karat_distribution' => $userOrderItems->groupBy('karat')
                        ->map(function($items) {
                            return [
                                'count' => $items->count(),
                                'total_weight' => $items->sum('weight'),
                                'total_value' => $items->sum('subtotal')
                            ];
                        })
                ]
            ]
        ]);
    }
}
