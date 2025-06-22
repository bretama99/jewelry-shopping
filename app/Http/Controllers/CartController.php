<?php
// File: app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Services\KitcoApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    protected $kitcoService;

    public function __construct(KitcoApiService $kitcoService)
    {
        $this->kitcoService = $kitcoService;
    }

    public function index()
    {
        try {
            $sessionId = Session::getId();
            $userId = Auth::id();

            $cartItems = Cart::getCartItems($sessionId, $userId);
            $cartTotal = Cart::getCartTotal($sessionId, $userId);
            $cartCount = Cart::getCartCount($sessionId, $userId);

            // Get current gold price for display with fallback
            try {
                $goldPrice = $this->kitcoService->getCurrentGoldPrice();
                $marketData = $this->kitcoService->getMarketData();
            } catch (\Exception $e) {
                // Fallback values if service fails
                $goldPrice = 2000; // Fallback gold price
                $marketData = [
                    'status' => 'unavailable',
                    'last_updated' => now()->toISOString(),
                    'currency' => 'USD',
                    'unit' => 'per ounce',
                    'source' => 'Fallback Data'
                ];
            }

            return view('cart.index', compact(
                'cartItems',
                'cartTotal',
                'cartCount',
                'goldPrice',
                'marketData'
            ));

        } catch (\Exception $e) {
            // If there's any error, show empty cart
            return view('cart.index', [
                'cartItems' => collect([]),
                'cartTotal' => 0,
                'cartCount' => 0,
                'goldPrice' => 2000,
                'marketData' => [
                    'status' => 'unavailable',
                    'last_updated' => now()->toISOString(),
                    'currency' => 'USD',
                    'unit' => 'per ounce',
                    'source' => 'Fallback Data'
                ]
            ]);
        }
    }

    public function add(CartRequest $request)
    {
        try {
            $sessionId = Session::getId();
            $userId = Auth::id();

            $cartItem = Cart::addItem(
                $request->product_id,
                $request->weight,
                $sessionId,
                $userId
            );

            $cartCount = Cart::getCartCount($sessionId, $userId);
            $cartTotal = Cart::getCartTotal($sessionId, $userId);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully!',
                    'cart_count' => $cartCount,
                    'cart_total' => '$' . number_format($cartTotal, 2),
                    'item' => [
                        'id' => $cartItem->id,
                        'product_name' => $cartItem->product->name ?? 'Product',
                        'weight' => $cartItem->formatted_weight ?? $cartItem->weight . 'g',
                        'subtotal' => $cartItem->formatted_subtotal ?? '$' . number_format($cartItem->subtotal, 2)
                    ]
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Product added to cart successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add product to cart: ' . $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Failed to add product to cart.');
        }
    }

    public function update(Request $request, Cart $cartItem)
    {
        try {
            $request->validate([
                'weight' => 'required|numeric|min:0.1|max:1000'
            ]);

            // Verify ownership
            $sessionId = Session::getId();
            $userId = Auth::id();

            if ($cartItem->session_id !== $sessionId && $cartItem->user_id !== $userId) {
                abort(403);
            }

            $cartItem->updateWeight($request->weight);

            $cartTotal = Cart::getCartTotal($sessionId, $userId);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully!',
                    'item' => [
                        'id' => $cartItem->id,
                        'weight' => $cartItem->formatted_weight ?? $cartItem->weight . 'g',
                        'subtotal' => $cartItem->formatted_subtotal ?? '$' . number_format($cartItem->subtotal, 2)
                    ],
                    'cart_total' => '$' . number_format($cartTotal, 2)
                ]);
            }

            return back()->with('success', 'Cart updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update cart: ' . $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Failed to update cart.');
        }
    }

    public function remove(Cart $cartItem)
    {
        try {
            // Verify ownership
            $sessionId = Session::getId();
            $userId = Auth::id();

            if ($cartItem->session_id !== $sessionId && $cartItem->user_id !== $userId) {
                abort(403);
            }

            $productName = $cartItem->product->name ?? 'Product';
            $cartItem->delete();

            $cartCount = Cart::getCartCount($sessionId, $userId);
            $cartTotal = Cart::getCartTotal($sessionId, $userId);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $productName . ' removed from cart.',
                    'cart_count' => $cartCount,
                    'cart_total' => '$' . number_format($cartTotal, 2)
                ]);
            }

            return back()->with('success', $productName . ' removed from cart.');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove item from cart.'
                ], 422);
            }

            return back()->with('error', 'Failed to remove item from cart.');
        }
    }

    public function clear()
    {
        try {
            $sessionId = Session::getId();
            $userId = Auth::id();

            Cart::clearCart($sessionId, $userId);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart cleared successfully!',
                    'cart_count' => 0,
                    'cart_total' => '$0.00'
                ]);
            }

            return back()->with('success', 'Cart cleared successfully!');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear cart.'
                ], 422);
            }

            return back()->with('error', 'Failed to clear cart.');
        }
    }

    public function refreshPrices()
    {
        try {
            $sessionId = Session::getId();
            $userId = Auth::id();

            $cartItems = Cart::getCartItems($sessionId, $userId);

            foreach ($cartItems as $item) {
                try {
                    $item->refreshPricing();
                } catch (\Exception $e) {
                    // Skip this item if pricing fails
                    continue;
                }
            }

            $cartTotal = Cart::getCartTotal($sessionId, $userId);

            try {
                $goldPrice = $this->kitcoService->getCurrentGoldPrice();
            } catch (\Exception $e) {
                $goldPrice = 2000; // Fallback
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prices updated with latest gold rates!',
                    'cart_total' => '$' . number_format($cartTotal, 2),
                    'gold_price' => '$' . number_format($goldPrice, 2),
                    'items' => $cartItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'price_per_gram' => $item->formatted_price_per_gram ?? '$' . number_format($item->price_per_gram, 2),
                            'subtotal' => $item->formatted_subtotal ?? '$' . number_format($item->subtotal, 2)
                        ];
                    })
                ]);
            }

            return back()->with('success', 'Prices updated with latest gold rates!');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to refresh prices.'
                ], 422);
            }

            return back()->with('error', 'Failed to refresh prices.');
        }
    }

    public function calculatePrice(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'weight' => 'required|numeric|min:0.1|max:1000'
            ]);

            $product = Product::findOrFail($request->product_id);

            try {
                $goldPrice = $this->kitcoService->getCurrentGoldPrice();
                $pricePerGram = $product->calculatePricePerGram($goldPrice);
            } catch (\Exception $e) {
                // Fallback pricing
                $goldPrice = 2000;
                $pricePerGram = 85.50; // Fallback price per gram
            }

            $subtotal = $request->weight * $pricePerGram;

            return response()->json([
                'success' => true,
                'data' => [
                    'weight' => number_format($request->weight, 3),
                    'gold_price' => number_format($goldPrice, 2),
                    'price_per_gram' => number_format($pricePerGram, 2),
                    'subtotal' => number_format($subtotal, 2),
                    'formatted' => [
                        'weight' => number_format($request->weight, 3) . 'g',
                        'gold_price' => '$' . number_format($goldPrice, 2),
                        'price_per_gram' => '$' . number_format($pricePerGram, 2),
                        'subtotal' => '$' . number_format($subtotal, 2)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate price: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getCartSummary()
    {
        try {
            $sessionId = Session::getId();
            $userId = Auth::id();

            $cartItems = Cart::getCartItems($sessionId, $userId);
            $cartTotal = Cart::getCartTotal($sessionId, $userId);
            $cartCount = Cart::getCartCount($sessionId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $cartCount,
                    'total' => '$' . number_format($cartTotal, 2),
                    'items' => $cartItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product->name ?? 'Product',
                            'weight' => $item->formatted_weight ?? $item->weight . 'g',
                            'subtotal' => $item->formatted_subtotal ?? '$' . number_format($item->subtotal, 2),
                            'image' => $item->product->image_url ?? asset('images/product-placeholder.jpg')
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart summary.'
            ], 422);
        }
    }
}
