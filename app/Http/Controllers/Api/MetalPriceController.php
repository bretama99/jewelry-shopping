<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetalPriceApiService;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MetalPriceController extends Controller
{
    protected $metalPriceService;

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        $this->metalPriceService = $metalPriceService;
    }

    /**
     * Get all live metal prices
     */
    public function index(): JsonResponse
    {
        try {
            $prices = $this->metalPriceService->getAllMetalPricesAUD();
            $cacheInfo = $this->metalPriceService->getCacheInfo();

            return response()->json([
                'success' => true,
                'data' => $prices,
                'cache_info' => $cacheInfo
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error fetching metal prices: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch metal prices',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get price for specific metal symbol
     */
    public function show(string $symbol): JsonResponse
    {
        try {
            $prices = $this->metalPriceService->getAllMetalPricesAUD();

            if (!isset($prices[$symbol])) {
                return response()->json([
                    'success' => false,
                    'message' => "Price not available for metal symbol: {$symbol}"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $prices[$symbol]
            ]);

        } catch (\Exception $e) {
            \Log::error("API: Error fetching price for {$symbol}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch metal price',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Force refresh all metal prices
     */
    public function refresh(): JsonResponse
    {
        try {
            $prices = $this->metalPriceService->forceRefreshPrices();

            return response()->json([
                'success' => true,
                'message' => 'Metal prices refreshed successfully',
                'data' => $prices
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error refreshing metal prices: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh metal prices',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate jewelry price
     */
    public function calculateJewelryPrice(Request $request): JsonResponse
    {
        $request->validate([
            'metal_symbol' => 'required|string|in:XAU,XAG,XPT,XPD',
            'purity' => 'required|string',
            'weight' => 'required|numeric|min:0.01',
            'labor_cost_per_gram' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0|max:1'
        ]);

        try {
            $metalPrice = $this->metalPriceService->getMetalPrice(
                $request->metal_symbol,
                $request->purity
            );

            $weight = $request->weight;
            $laborCostPerGram = $request->labor_cost_per_gram;
            $profitMargin = $request->profit_margin;

            // Calculate pricing breakdown
            $metalValue = $weight * $metalPrice;
            $totalLaborCost = $weight * $laborCostPerGram;
            $baseCost = $metalValue + $totalLaborCost;
            $profitAmount = $baseCost * $profitMargin;
            $finalPrice = $baseCost + $profitAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'metal_price_per_gram' => $metalPrice,
                    'metal_value' => round($metalValue, 2),
                    'labor_cost' => round($totalLaborCost, 2),
                    'base_cost' => round($baseCost, 2),
                    'profit_amount' => round($profitAmount, 2),
                    'final_price' => round($finalPrice, 2),
                    'price_per_gram' => round($finalPrice / $weight, 2),
                    'breakdown' => [
                        'weight' => $weight,
                        'metal_symbol' => $request->metal_symbol,
                        'purity' => $request->purity,
                        'labor_cost_per_gram' => $laborCostPerGram,
                        'profit_margin_percentage' => $profitMargin * 100
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error calculating jewelry price: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate jewelry price',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate scrap purchase price
     */
    public function calculateScrapPrice(Request $request): JsonResponse
    {
        $request->validate([
            'metal_symbol' => 'required|string|in:XAU,XAG,XPT,XPD',
            'purity' => 'required|string',
            'weight' => 'required|numeric|min:0.01',
            'processing_fee_rate' => 'required|numeric|min:0|max:1',
            'margin_rate' => 'required|numeric|min:0|max:1'
        ]);

        try {
            $metalPrice = $this->metalPriceService->getMetalPrice(
                $request->metal_symbol,
                $request->purity
            );

            $weight = $request->weight;
            $processingFeeRate = $request->processing_fee_rate;
            $marginRate = $request->margin_rate;

            // Calculate scrap pricing
            $grossValue = $weight * $metalPrice;
            $processingFee = $grossValue * $processingFeeRate;
            $marginDeduction = $grossValue * $marginRate;
            $totalDeductions = $processingFee + $marginDeduction;
            $offerValue = $grossValue - $totalDeductions;

            return response()->json([
                'success' => true,
                'data' => [
                    'metal_price_per_gram' => $metalPrice,
                    'gross_value' => round($grossValue, 2),
                    'processing_fee' => round($processingFee, 2),
                    'margin_deduction' => round($marginDeduction, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'offer_value' => round($offerValue, 2),
                    'offer_price_per_gram' => round($offerValue / $weight, 2),
                    'breakdown' => [
                        'weight' => $weight,
                        'metal_symbol' => $request->metal_symbol,
                        'purity' => $request->purity,
                        'processing_fee_rate' => $processingFeeRate * 100,
                        'margin_rate' => $marginRate * 100
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error calculating scrap price: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate scrap price',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate bullion sell price
     */
    public function calculateBullionSellPrice(Request $request): JsonResponse
    {
        $request->validate([
            'metal_symbol' => 'required|string|in:XAU,XAG,XPT,XPD',
            'weight' => 'required|numeric|min:0.01',
            'premium_rate' => 'required|numeric|min:0|max:1'
        ]);

        try {
            // Use highest purity for bullion
            $purity = $this->getHighestPurityForMetal($request->metal_symbol);
            $metalPrice = $this->metalPriceService->getMetalPrice($request->metal_symbol, $purity);

            $weight = $request->weight;
            $premiumRate = $request->premium_rate;

            // Calculate bullion sell pricing
            $baseValue = $weight * $metalPrice;
            $premiumAmount = $baseValue * $premiumRate;
            $sellPrice = $baseValue + $premiumAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'metal_price_per_gram' => $metalPrice,
                    'base_value' => round($baseValue, 2),
                    'premium_amount' => round($premiumAmount, 2),
                    'sell_price' => round($sellPrice, 2),
                    'price_per_gram' => round($sellPrice / $weight, 2),
                    'breakdown' => [
                        'weight' => $weight,
                        'metal_symbol' => $request->metal_symbol,
                        'purity' => $purity,
                        'premium_rate' => $premiumRate * 100
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error calculating bullion sell price: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate bullion sell price',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate bullion buy price
     */
    public function calculateBullionBuyPrice(Request $request): JsonResponse
    {
        $request->validate([
            'metal_symbol' => 'required|string|in:XAU,XAG,XPT,XPD',
            'weight' => 'required|numeric|min:0.01',
            'margin_rate' => 'required|numeric|min:0|max:1',
            'condition_factor' => 'nullable|numeric|min:0|max:1'
        ]);

        try {
            // Use highest purity for bullion
            $purity = $this->getHighestPurityForMetal($request->metal_symbol);
            $metalPrice = $this->metalPriceService->getMetalPrice($request->metal_symbol, $purity);

            $weight = $request->weight;
            $marginRate = $request->margin_rate;
            $conditionFactor = $request->condition_factor ?? 1.0;

            // Calculate bullion buy pricing
            $baseValue = $weight * $metalPrice;
            $marginDeduction = $baseValue * $marginRate;
            $afterMargin = $baseValue - $marginDeduction;
            $buyPrice = $afterMargin * $conditionFactor;

            return response()->json([
                'success' => true,
                'data' => [
                    'metal_price_per_gram' => $metalPrice,
                    'base_value' => round($baseValue, 2),
                    'margin_deduction' => round($marginDeduction, 2),
                    'condition_adjustment' => round($afterMargin - $buyPrice, 2),
                    'buy_price' => round($buyPrice, 2),
                    'price_per_gram' => round($buyPrice / $weight, 2),
                    'breakdown' => [
                        'weight' => $weight,
                        'metal_symbol' => $request->metal_symbol,
                        'purity' => $purity,
                        'margin_rate' => $marginRate * 100,
                        'condition_factor' => $conditionFactor * 100
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error calculating bullion buy price: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate bullion buy price',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get highest purity for metal symbol
     */
    private function getHighestPurityForMetal(string $symbol): string
    {
        $highestPurities = [
            'XAU' => '24',  // 24K Gold
            'XAG' => '999', // 999 Silver
            'XPT' => '999', // 999 Platinum
            'XPD' => '999'  // 999 Palladium
        ];

        return $highestPurities[$symbol] ?? '999';
    }
}
