<?php
// File: app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'billing_address',
        'shipping_address',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'tracking_number',
        'gold_price_at_order',
        'market_data',
        'notes',
        'admin_notes',
        'confirmed_at',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gold_price_at_order' => 'decimal:2',
        'market_data' => 'array',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    protected $dates = [
        'confirmed_at',
        'shipped_at',
        'delivered_at'
    ];

    // Boot method to auto-generate order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->generateOrderNumber();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered']);
    }

    public function scopeByCustomer($query, $email)
    {
        return $query->where('customer_email', $email);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedTotalAttribute()
    {
        return 'AUD$' . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'AUD$' . number_format($this->subtotal, 2);
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'success',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'secondary',
            default => 'secondary'
        };
    }

    public function getIsEditableAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getIsCancellableAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getTotalWeightAttribute()
    {
        return $this->orderItems->sum('weight');
    }

    public function getItemsCountAttribute()
    {
        return $this->orderItems->count();
    }

    // Methods
    public function generateOrderNumber()
    {
        $year = date('Y');
        $month = date('m');

        // Get the last order number for this month
        $lastOrder = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotNull('order_number')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder && $lastOrder->order_number) {
            // Extract sequence number from last order
            $parts = explode('-', $lastOrder->order_number);
            $sequence = isset($parts[2]) ? (int) $parts[2] + 1 : 1;
        } else {
            $sequence = 1;
        }

        $this->order_number = sprintf('ORD-%s%s-%04d', $year, $month, $sequence);
        return $this->order_number;
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->orderItems->sum('subtotal');
        $this->tax_amount = $this->subtotal * 0.10; // 10% tax

        // Free shipping over $500
        $this->shipping_cost = $this->subtotal > 500 ? 0 : 25;

        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost;

        return $this;
    }

    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;

        if ($notes) {
            $this->admin_notes = $notes;
        }

        switch ($status) {
            case 'confirmed':
                if (!$this->confirmed_at) {
                    $this->confirmed_at = now();
                }
                $this->payment_status = 'paid';
                break;
            case 'processing':
                if (!$this->confirmed_at) {
                    $this->confirmed_at = now();
                }
                break;
            case 'shipped':
                if (!$this->shipped_at) {
                    $this->shipped_at = now();
                }
                // Generate tracking number if not exists
                if (!$this->tracking_number) {
                    $this->tracking_number = 'TRK-' . strtoupper(uniqid());
                }
                break;
            case 'delivered':
                if (!$this->delivered_at) {
                    $this->delivered_at = now();
                }
                break;
            case 'cancelled':
                $this->payment_status = 'cancelled';
                break;
        }

        $this->save();

        // Log status change
        if (class_exists('\Spatie\Activitylog\Traits\LogsActivity')) {
            activity()
                ->performedOn($this)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'notes' => $notes
                ])
                ->log('Order status updated');
        }

        return $this;
    }

    public function canBeStatusUpdatedTo($newStatus)
    {
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => ['refunded'],
            'cancelled' => [],
            'refunded' => []
        ];

        return in_array($newStatus, $allowedTransitions[$this->status] ?? []);
    }

    // Static Methods
    public static function createFromCart($cartItems, $customerData, $userId = null)
    {
        try {
            \DB::beginTransaction();

            $order = new static();
            $order->fill($customerData);
            $order->user_id = $userId;
            $order->currency = 'AUD';
            $order->status = 'pending';
            $order->payment_status = 'pending';

            // Set default gold price if service not available
            $order->gold_price_at_order = 77.50;
            $order->market_data = ['timestamp' => now()->toISOString()];

            $order->save(); // This will trigger generateOrderNumber()

            // Calculate totals first to get subtotal
            $subtotal = 0;
            foreach ($cartItems as $cartItem) {
                $itemTotal = $cartItem['weight'] * $cartItem['pricePerGram'];
                $subtotal += $itemTotal;
            }

            // Set calculated amounts
            $order->subtotal = $subtotal;
            $order->tax_amount = $subtotal * 0.10;
            $order->shipping_cost = $subtotal > 500 ? 0 : 25;
            $order->total_amount = $order->subtotal + $order->tax_amount + $order->shipping_cost;
            $order->save();

            // Create order items
            foreach ($cartItems as $cartItem) {
                $order->orderItems()->create([
                    'product_id' => $cartItem['productId'],
                    'product_name' => $cartItem['productName'],
                    'product_sku' => 'SKU-' . $cartItem['productId'] . '-' . time(),
                    'karat' => $cartItem['productKarat'],
                    'category_name' => $cartItem['productCategory'],
                    'product_description' => $cartItem['productName'],
                    'product_image' => $cartItem['productImage'],
                    'weight' => $cartItem['weight'],
                    'price_per_gram' => $cartItem['pricePerGram'],
                    'gold_price' => $cartItem['pricePerGram'],
                    'labor_cost' => 0,
                    'profit_margin' => 0,
                    'subtotal' => $cartItem['weight'] * $cartItem['pricePerGram'],
                    'product_features' => $cartItem['features'] ?? [],
                    'pricing_breakdown' => [
                        'weight' => $cartItem['weight'],
                        'gold_price' => $cartItem['pricePerGram'],
                        'price_per_gram' => $cartItem['pricePerGram'],
                        'subtotal' => $cartItem['weight'] * $cartItem['pricePerGram'],
                        'calculated_at' => now()->toISOString()
                    ]
                ]);
            }

            \DB::commit();
            return $order;

        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public static function getRecentStats($days = 30)
    {
        $orders = static::recent($days)->get();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->avg('total_amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->whereIn('status', ['delivered'])->count(),
        ];
    }

    // Additional helper methods for order management
    public function getTimelineEvents()
    {
        $events = [];

        $events[] = [
            'title' => 'Order Placed',
            'date' => $this->created_at,
            'completed' => true,
            'description' => 'Order was successfully placed'
        ];

        if ($this->confirmed_at) {
            $events[] = [
                'title' => 'Order Confirmed',
                'date' => $this->confirmed_at,
                'completed' => true,
                'description' => 'Order confirmed and payment processed'
            ];
        }

        if ($this->status === 'processing') {
            $events[] = [
                'title' => 'Processing',
                'date' => $this->updated_at,
                'completed' => true,
                'description' => 'Order is being processed'
            ];
        }

        if ($this->shipped_at) {
            $events[] = [
                'title' => 'Shipped',
                'date' => $this->shipped_at,
                'completed' => true,
                'description' => 'Order has been shipped',
                'tracking' => $this->tracking_number
            ];
        }

        if ($this->delivered_at) {
            $events[] = [
                'title' => 'Delivered',
                'date' => $this->delivered_at,
                'completed' => true,
                'description' => 'Order has been delivered'
            ];
        }

        return $events;
    }

    public function getEstimatedDeliveryDate()
    {
        if ($this->delivered_at) {
            return $this->delivered_at;
        }

        if ($this->shipped_at) {
            return $this->shipped_at->addDays(3); // 3 days for delivery
        }

        if ($this->status === 'processing') {
            return now()->addDays(7); // 7 days processing + delivery
        }

        if ($this->status === 'confirmed') {
            return now()->addDays(10); // 10 days total
        }

        return now()->addDays(14); // Default estimate
    }

    public function canBeReordered()
    {
        return $this->orderItems()->count() > 0;
    }

    public function getOrderSummary()
    {
        return [
            'order_number' => $this->order_number,
            'status' => $this->status,
            'items_count' => $this->orderItems()->count(),
            'total_weight' => $this->orderItems()->sum('weight'),
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at->format('M j, Y'),
            'estimated_delivery' => $this->getEstimatedDeliveryDate()->format('M j, Y')
        ];
    }
}
