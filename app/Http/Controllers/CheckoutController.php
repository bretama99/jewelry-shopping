<?php

// File: app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Services\KitcoApiService;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $kitcoService;

    public function __construct(KitcoApiService $kitcoService)
    {
        $this->kitcoService = $kitcoService;
        $this->middleware('auth');
    }

    /**
     * Display the checkout page.
     */
    public function index()
    {
        $cartItems = $this->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $goldPrices = $this->kitcoService->getCurrentPrices();
        $totals = $this->calculateOrderTotals($cartItems, $goldPrices);

        $user = Auth::user();

        return view('checkout.index', compact(
            'cartItems',
            'goldPrices',
            'totals',
            'user'
        ));
    }

    /**
     * Process the checkout and create order.
     */
    public function process(CheckoutRequest $request)
    {
        $cartItems = $this->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $goldPrices = $this->kitcoService->getCurrentPrices();
        $totals = $this->calculateOrderTotals($cartItems, $goldPrices);

        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => $this->generateOrderNumber(),
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'currency' => 'AUD',
                'status' => 'pending',
                'customer_name' => $request->first_name . ' ' . $request->last_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'customer_address' => $this->formatAddress($request),
                'billing_address' => $request->billing_same ? null : $this->formatBillingAddress($request),
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                $goldPricePerGram = $goldPrices[$item['product']->karat] ?? 0;
                $unitPrice = $item['product']->calculatePrice($item['weight'], $goldPricePerGram);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'product_karat' => $item['product']->karat,
                    'weight' => $item['weight'],
                    'gold_price_per_gram' => $goldPricePerGram,
                    'labor_cost' => $item['product']->labor_cost,
                    'profit_margin' => $item['product']->profit_margin,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'total_price' => $unitPrice * $item['quantity'],
                ]);
            }

            // Clear the cart
            $this->clearCart();

            // Process payment (placeholder)
            $paymentResult = $this->processPayment($order, $request);

            if ($paymentResult['success']) {
                $order->update([
                    'payment_status' => 'completed',
                    'status' => 'processing',
                    'payment_reference' => $paymentResult['reference']
                ]);

                DB::commit();

                // Send confirmation email (placeholder)
                // $this->sendOrderConfirmation($order);

                return redirect()->route('checkout.success', $order)
                    ->with('success', 'Your order has been placed successfully!');
            } else {
                throw new \Exception('Payment failed: ' . $paymentResult['message']);
            }

        } catch (\Exception $e) {
            DB::rollback();

            return back()->withInput()->with('error', 'Order processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Display order success page.
     */
    public function success(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product', 'user');

        return view('checkout.success', compact('order'));
    }

    /**
     * Calculate order totals.
     */
    protected function calculateOrderTotals($cartItems, $goldPrices)
    {
        $subtotal = 0;
        $itemCount = 0;

        foreach ($cartItems as $item) {
            $goldPricePerGram = $goldPrices[$item['product']->karat] ?? 0;
            $itemPrice = $item['product']->calculatePrice($item['weight'], $goldPricePerGram);
            $subtotal += $itemPrice * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        $tax = $subtotal * 0.10; // 10% GST
        $shipping = $subtotal >= 500 ? 0 : 25; // Free shipping over $500
        $insurance = $subtotal * 0.005; // 0.5% insurance
        $total = $subtotal + $tax + $shipping + $insurance;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => $shipping,
            'insurance' => round($insurance, 2),
            'total' => round($total, 2),
            'item_count' => $itemCount
        ];
    }

    /**
     * Get cart items for checkout.
     */
    protected function getCartItems()
    {
        $items = [];

        if (Auth::check()) {
            $cartItems = Cart::where('user_id', Auth::id())->with('product.category')->get();

            foreach ($cartItems as $item) {
                if ($item->product && $item->product->is_active) {
                    $items[] = [
                        'id' => $item->id,
                        'product' => $item->product,
                        'weight' => $item->weight,
                        'quantity' => $item->quantity
                    ];
                }
            }
        } else {
            $cart = Session::get('cart', []);

            foreach ($cart as $key => $item) {
                $product = Product::with('category')->find($item['product_id']);

                if ($product && $product->is_active) {
                    $items[] = [
                        'id' => null,
                        'product' => $product,
                        'weight' => $item['weight'],
                        'quantity' => $item['quantity']
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Generate unique order number.
     */
    protected function generateOrderNumber()
    {
        $prefix = 'JW';
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(Str::random(4));

        return $prefix . $timestamp . $random;
    }

    /**
     * Format shipping address.
     */
    protected function formatAddress($request)
    {
        return implode("\n", array_filter([
            $request->address_line_1,
            $request->address_line_2,
            $request->city . ', ' . $request->state . ' ' . $request->postcode,
            $request->country ?? 'Australia'
        ]));
    }

    /**
     * Format billing address.
     */
    protected function formatBillingAddress($request)
    {
        if ($request->billing_same) {
            return null;
        }

        return implode("\n", array_filter([
            $request->billing_address_line_1,
            $request->billing_address_line_2,
            $request->billing_city . ', ' . $request->billing_state . ' ' . $request->billing_postcode,
            $request->billing_country ?? 'Australia'
        ]));
    }

    /**
     * Process payment (placeholder implementation).
     */
    protected function processPayment($order, $request)
    {
        // This is a placeholder for payment processing
        // In a real implementation, you would integrate with:
        // - Stripe, PayPal, Square, etc.
        // - Bank payment gateways
        // - Buy now, pay later services

        $paymentMethod = $request->payment_method;

        switch ($paymentMethod) {
            case 'credit_card':
                return $this->processCreditCardPayment($order, $request);
            case 'paypal':
                return $this->processPayPalPayment($order, $request);
            case 'bank_transfer':
                return $this->processBankTransferPayment($order, $request);
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid payment method selected.'
                ];
        }
    }

    /**
     * Process credit card payment (placeholder).
     */
    protected function processCreditCardPayment($order, $request)
    {
        // Placeholder for credit card processing
        // Would integrate with Stripe, Square, etc.

        return [
            'success' => true,
            'reference' => 'CC_' . strtoupper(Str::random(10)),
            'message' => 'Credit card payment processed successfully.'
        ];
    }

    /**
     * Process PayPal payment (placeholder).
     */
    protected function processPayPalPayment($order, $request)
    {
        // Placeholder for PayPal processing

        return [
            'success' => true,
            'reference' => 'PP_' . strtoupper(Str::random(10)),
            'message' => 'PayPal payment processed successfully.'
        ];
    }

    /**
     * Process bank transfer payment (placeholder).
     */
    protected function processBankTransferPayment($order, $request)
    {
        // For bank transfers, order would be pending until payment received

        return [
            'success' => true,
            'reference' => 'BT_' . strtoupper(Str::random(10)),
            'message' => 'Bank transfer instructions sent. Order pending payment confirmation.'
        ];
    }

    /**
     * Clear user's cart after successful checkout.
     */
    protected function clearCart()
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        } else {
            Session::forget('cart');
        }
    }

    /**
     * Send order confirmation email (placeholder).
     */
    protected function sendOrderConfirmation($order)
    {
        // Placeholder for email functionality
        // Would use Laravel's Mail system with queues

        // Mail::to($order->customer_email)->queue(new OrderConfirmation($order));
    }

    /**
     * Calculate shipping cost based on location and items.
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'postcode' => 'required|string|max:10',
            'state' => 'required|string|max:50'
        ]);

        // Placeholder for dynamic shipping calculation
        // Would integrate with Australia Post API or courier services

        $cartItems = $this->getCartItems();
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $itemPrice = $item['product']->calculatePrice($item['weight']);
            $subtotal += $itemPrice * $item['quantity'];
        }

        // Simple shipping logic
        if ($subtotal >= 500) {
            $shipping = 0;
        } elseif ($request->state === 'NSW' || $request->state === 'VIC') {
            $shipping = 15;
        } else {
            $shipping = 25;
        }

        return response()->json([
            'shipping_cost' => $shipping,
            'formatted' => $shipping > 0 ? '$' . number_format($shipping, 2) : 'FREE',
            'free_shipping_threshold' => 500,
            'amount_for_free_shipping' => max(0, 500 - $subtotal)
        ]);
    }

    /**
     * Validate discount code.
     */
    public function validateDiscount(Request $request)
    {
        $request->validate([
            'discount_code' => 'required|string|max:20'
        ]);

        // Placeholder for discount validation
        // Would check against discount codes table

        return response()->json([
            'valid' => false,
            'message' => 'Discount functionality coming soon.',
            'discount_amount' => 0
        ]);
    }
}
