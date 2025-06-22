<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetalPriceApiService;
use Illuminate\Http\Request;

class MetalPriceController extends Controller
{
    protected $metalPriceService;

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->metalPriceService = $metalPriceService;
    }

    /**
     * Get current metal prices
     */
    public function index()
    {
        try {
            $prices = $this->metalPriceService->getAllMetalPricesAUD();

            return response()->json([
                'success' => true,
                'data' => $prices,
                'cache_status' => $this->metalPriceService->getCacheStatus(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch metal prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prices for specific metal
     */
    public function show($metalSymbol)
    {
        try {
            $prices = $this->metalPriceService->getMetalPricesAUD(strtoupper($metalSymbol));

            if (empty($prices)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metal not found or inactive'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'metal' => strtoupper($metalSymbol),
                    'prices' => $prices,
                    'last_updated' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch metal prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force refresh prices
     */
    public function refresh()
    {
        try {
            $updateCount = $this->metalPriceService->forceRefreshPrices();

            return response()->json([
                'success' => true,
                'message' => "Updated prices for {$updateCount} metals",
                'data' => $this->metalPriceService->getAllMetalPricesAUD(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh metal prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate product price
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'metal_symbol' => 'nullable|string|in:XAU,XAG,XPD,XPT',
            'karat' => 'required|string',
            'weight' => 'required|numeric|min:0.01',
            'labor_cost' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $calculation = $this->metalPriceService->calculateProductPrice(
                $request->metal_symbol,
                $request->karat,
                $request->weight,
                $request->labor_cost ?? 15,
                $request->profit_margin ?? 25
            );

            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate price',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
