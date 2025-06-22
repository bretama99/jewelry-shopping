<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])
            ->withCount('orderItems')
            ->selectRaw('orders.*,
                (SELECT SUM(weight) FROM order_items WHERE order_items.order_id = orders.id) as total_weight');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        switch ($request->get('sort', 'newest')) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'total_high':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'total_low':
                $query->orderBy('total_amount', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(20)->withQueryString();

        // Calculate statistics including order items data
        $stats = $this->getOrderStatistics();

        return view('orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product.category']);

        // Calculate detailed order statistics
        $orderAnalytics = [
            'items_count' => $order->orderItems->count(),
            'total_weight' => $order->orderItems->sum('weight'),
            'average_item_value' => $order->orderItems->avg('subtotal'),
            'highest_value_item' => $order->orderItems->max('subtotal'),
            'lowest_value_item' => $order->orderItems->min('subtotal'),
            'karat_breakdown' => $order->orderItems->groupBy('karat')->map(function($items, $karat) {
                return [
                    'count' => $items->count(),
                    'total_weight' => $items->sum('weight'),
                    'total_value' => $items->sum('subtotal'),
                    'avg_price_per_gram' => $items->avg('price_per_gram')
                ];
            }),
            'category_breakdown' => $order->orderItems->groupBy('category_name')->map(function($items, $category) {
                return [
                    'count' => $items->count(),
                    'total_weight' => $items->sum('weight'),
                    'total_value' => $items->sum('subtotal')
                ];
            }),
            'profit_analysis' => [
                'estimated_gold_cost' => $order->orderItems->sum(function($item) {
                    return $item->weight * $item->gold_price * 0.9; // Assuming 90% gold content average
                }),
                'labor_costs' => $order->orderItems->sum('labor_cost'),
                'estimated_profit' => $order->subtotal - $order->orderItems->sum(function($item) {
                    return ($item->weight * $item->gold_price * 0.9) + $item->labor_cost;
                })
            ]
        ];

        return view('orders.show', compact('order', 'orderAnalytics'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'note' => 'nullable|string|max:500'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Check if status transition is allowed
        if (!$order->canBeStatusUpdatedTo($newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot change status from {$oldStatus} to {$newStatus}"
            ], 400);
        }

        try {
            $order->updateStatus($newStatus, $request->note);

            // Log the status change
            activity()
                ->performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'note' => $request->note
                ])
                ->log('Order status updated');

            return response()->json([
                'success' => true,
                'message' => "Order status updated to {$newStatus}",
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'status_color' => $order->status_color
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_processed,mark_shipped,mark_delivered,export_selected',
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();
        $successCount = 0;
        $errors = [];

        foreach ($orders as $order) {
            try {
                switch ($request->action) {
                    case 'mark_processed':
                        if ($order->canBeStatusUpdatedTo('processing')) {
                            $order->updateStatus('processing', 'Bulk update: Marked as processing');
                            $successCount++;
                        } else {
                            $errors[] = "Order #{$order->order_number} cannot be marked as processing";
                        }
                        break;

                    case 'mark_shipped':
                        if ($order->canBeStatusUpdatedTo('shipped')) {
                            $order->updateStatus('shipped', 'Bulk update: Marked as shipped');
                            $successCount++;
                        } else {
                            $errors[] = "Order #{$order->order_number} cannot be marked as shipped";
                        }
                        break;

                    case 'mark_delivered':
                        if ($order->canBeStatusUpdatedTo('delivered')) {
                            $order->updateStatus('delivered', 'Bulk update: Marked as delivered');
                            $successCount++;
                        } else {
                            $errors[] = "Order #{$order->order_number} cannot be marked as delivered";
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = "Error updating order #{$order->order_number}: " . $e->getMessage();
            }
        }

        $message = "Successfully updated {$successCount} orders.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => $message,
            'updated_count' => $successCount,
            'errors' => $errors
        ]);
    }

    public function export(Request $request)
    {
        $query = Order::with(['user', 'orderItems']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'orders_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Status',
                'Payment Status',
                'Payment Method',
                'Subtotal',
                'Tax Amount',
                'Shipping Cost',
                'Total Amount',
                'Items Count',
                'Total Weight',
                'Order Date',
                'Confirmed Date',
                'Shipped Date',
                'Delivered Date'
            ]);

            // CSV data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->customer_phone,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->subtotal,
                    $order->tax_amount,
                    $order->shipping_cost,
                    $order->total_amount,
                    $order->orderItems->count(),
                    $order->orderItems->sum('weight'),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->confirmed_at?->format('Y-m-d H:i:s'),
                    $order->shipped_at?->format('Y-m-d H:i:s'),
                    $order->delivered_at?->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function receipt(Order $order)
    {
        $order->load(['user', 'orderItems']);

        // Generate PDF receipt
        try {
            $receiptService = app(\App\Services\ReceiptService::class);
            $pdf = $receiptService->generateReceipt($order);

            return $pdf->download('receipt-' . $order->order_number . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating receipt: ' . $e->getMessage());
        }
    }

    private function getOrderStatistics()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'today_revenue' => Order::whereDate('created_at', $today)
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->sum('total_amount'),
            'month_revenue' => Order::where('created_at', '>=', $thisMonth)
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->sum('total_amount'),
            'total_revenue' => Order::whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->sum('total_amount'),
            'completed_orders' => Order::whereIn('status', ['delivered'])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
        ];
    }

  
}
