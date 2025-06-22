<?php
// File: app/Http/Controllers/Api/MetalPriceController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetalCategory;
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
     * Get current prices for all metals
     */
    public function getAllPrices()
    {
        $metalCategories = MetalCategory::where('is_active', true)->get();
        $prices = [];

        foreach ($metalCategories as $category) {
            $priceData = $this->metalPriceService->getCurrentPrice($category->symbol);
            $prices[$category->symbol] = [
                'name' => $category->name,
                'symbol' => $category->symbol,
                'price_usd' => $priceData['success'] ? $priceData['price'] : null,
                'last_updated' => $priceData['success'] ? $priceData['last_updated'] : null,
                'success' => $priceData['success'],
                'error' => $priceData['success'] ? null : $priceData['error']
            ];
        }

        return response()->json([
            'success' => true,
            'prices' => $prices,
            'exchange_rate' => $this->metalPriceService->getExchangeRate()
        ]);
    }

    /**
     * Get current price for a specific metal
     */
    public function getPrice($symbol)
    {
        $priceData = $this->metalPriceService->getCurrentPrice($symbol);

        if (!$priceData['success']) {
            return response()->json([
                'success' => false,
                'error' => $priceData['error']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'symbol' => $symbol,
            'price_usd' => $priceData['price'],
            'exchange_rate' => $this->metalPriceService->getExchangeRate(),
            'price_aud_per_gram' => ($priceData['price'] * $this->metalPriceService->getExchangeRate()) / 31.1035,
            'last_updated' => $priceData['last_updated']
        ]);
    }

    /**
     * Calculate price for specific metal, karat, and weight
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'symbol' => 'nullable',
            'karat' => 'required|string',
            'weight' => 'required|numeric|min:0.001',
            'labor_cost' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            $symbol = $request->symbol;
            $karat = $request->karat;
            $weight = $request->weight;
            $laborCost = $request->labor_cost ?? 10; // Default labor cost per gram
            $profitMargin = $request->profit_margin ?? 20; // Default 20% profit margin

            // Get current metal price
            $priceData = $this->metalPriceService->getCurrentPrice($symbol);
            if (!$priceData['success']) {
                throw new \Exception('Unable to fetch current metal price');
            }

            // Convert to AUD per gram
            $exchangeRate = $this->metalPriceService->getExchangeRate();
            $pricePerGramAud = ($priceData['price'] * $exchangeRate) / 31.1035;

            // Get karat multiplier
            $karatMultiplier = $this->getKaratMultiplier($symbol, $karat);
            $karatAdjustedPrice = $pricePerGramAud * $karatMultiplier;

            // Calculate final price
            $metalCost = $karatAdjustedPrice * $weight;
            $totalLaborCost = $laborCost * $weight;
            $subtotal = $metalCost + $totalLaborCost;
            $profitAmount = $subtotal * ($profitMargin / 100);
            $finalPrice = $subtotal + $profitAmount;

            return response()->json([
                'success' => true,
                'calculation' => [
                    'metal_symbol' => $symbol,
                    'karat' => $karat,
                    'weight' => $weight,
                    'raw_metal_price_usd' => $priceData['price'],
                    'exchange_rate' => $exchangeRate,
                    'metal_price_per_gram_aud' => round($pricePerGramAud, 2),
                    'karat_multiplier' => $karatMultiplier,
                    'karat_adjusted_price' => round($karatAdjustedPrice, 2),
                    'metal_cost' => round($metalCost, 2),
                    'labor_cost_per_gram' => $laborCost,
                    'total_labor_cost' => round($totalLaborCost, 2),
                    'subtotal' => round($subtotal, 2),
                    'profit_margin_percent' => $profitMargin,
                    'profit_amount' => round($profitAmount, 2),
                    'final_price' => round($finalPrice, 2),
                    'currency' => 'AUD'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get karat multiplier for price calculation
     */
    protected function getKaratMultiplier($symbol, $karat)
    {
        switch ($symbol) {
            case 'XAU': // Gold
                return floatval($karat) / 24;
            case 'XAG': // Silver
                return floatval($karat) / 1000;
            case 'XPT': // Platinum
                return floatval($karat) / 1000;
            case 'XPD': // Palladium
                return floatval($karat) / 1000;
            default:
                return 1.0;
        }
    }
}

