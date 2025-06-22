<?php
// app/Services/MetalPriceApiService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\MetalCategory;

class MetalPriceApiService
{
    protected $apiKey;
    protected $baseUrl;
    protected $cacheMinutes;

// Change this:
// $exchangeRate = $this->metalPriceService->getExchangeRate();

// To this:
// $exchangeRate = $this->metalPriceService->getAudRate();

    public function __construct()
    {
        $this->apiKey = config('services.metal_price_api.key', 'd68f51781cca05150ab380fbea59224c');
        $this->baseUrl = 'https://api.metalpriceapi.com/v1';
        $this->cacheMinutes = 5; // Cache for 5 minutes
    }

    /**
     * Fetch live metal prices from API
     */
    public function fetchLivePrices()
    {
        return Cache::remember('live_metal_prices', $this->cacheMinutes * 60, function () {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/latest', [
                    'api_key' => $this->apiKey,
                    'base' => 'USD',
                    'currencies' => 'XAU,XAG,XPD,XPT'
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if ($data['success'] && isset($data['rates'])) {
                        $rates = $data['rates'];

                        // Extract USD prices per troy ounce
                        $usdPrices = [
                            'XAU' => $rates['USDXAU'], // Gold
                            'XAG' => $rates['USDXAG'], // Silver
                            'XPD' => $rates['USDXPD'], // Palladium
                            'XPT' => $rates['USDXPT'], // Platinum
                        ];

                        Log::info('Metal prices fetched successfully', $usdPrices);
                        return $usdPrices;
                    }
                }

                throw new \Exception('Invalid API response format');
            } catch (\Exception $e) {
                Log::error('Failed to fetch metal prices: ' . $e->getMessage());
                return $this->getFallbackPrices();
            }
        });
    }

    /**
     * Get AUD exchange rate
     */
    public function getAudRate()
    {
        return Cache::remember('usd_aud_rate', 60 * 60, function () { // Cache for 1 hour
            try {
                $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates']['AUD'] ?? 1.45;
                }
                return 1.45; // Fallback USD to AUD rate
            } catch (\Exception $e) {
                Log::warning('Failed to fetch AUD rate: ' . $e->getMessage());
                return 1.45; // Fallback rate
            }
        });
    }

    /**
     * Update all metal categories with live prices
     */
    public function updateAllMetalPrices()
    {
        $livePrices = $this->fetchLivePrices();
        $audRate = $this->getAudRate();

        $metalCategories = MetalCategory::active()->get();
        $updateCount = 0;

        foreach ($metalCategories as $metalCategory) {
            if (isset($livePrices[$metalCategory->symbol])) {
                $usdPrice = $livePrices[$metalCategory->symbol];
                $metalCategory->updatePriceFromApi($usdPrice, $audRate);
                $updateCount++;
            }
        }

        Log::info("Updated prices for {$updateCount} metal categories");
        return $updateCount;
    }

    /**
     * Get prices for all metals in AUD per gram by karat/purity
     */
    public function getAllMetalPricesAUD()
    {
        $metalCategories = MetalCategory::active()->get();
        $allPrices = [
            'gold' => [],
            'silver' => [],
            'palladium' => [],
            'platinum' => [],
            'last_updated' => now()->toISOString(),
        ];

        foreach ($metalCategories as $metalCategory) {
            $metalKey = strtolower($metalCategory->name);
            if (isset($allPrices[$metalKey])) {
                $allPrices[$metalKey] = $metalCategory->getAllPrices();
            }
        }

        return $allPrices;
    }

    /**
     * Get prices for specific metal
     */
    public function getMetalPricesAUD($metalSymbol)
    {
        $metalCategory = MetalCategory::where('symbol', $metalSymbol)->active()->first();

        if (!$metalCategory) {
            return [];
        }

        return $metalCategory->getAllPrices();
    }

    /**
     * Get fallback prices when API fails
     */
    protected function getFallbackPrices()
    {
        // Based on realistic market values - these will be used if API fails
        return [
            'XAU' => 3300.00, // Gold ~$3300/oz
            'XAG' => 33.00,   // Silver ~$33/oz
            'XPD' => 980.00,  // Palladium ~$980/oz
            'XPT' => 1075.00, // Platinum ~$1075/oz
        ];
    }

    /**
     * Force refresh prices (bypass cache)
     */
    public function forceRefreshPrices()
    {
        Cache::forget('live_metal_prices');
        Cache::forget('usd_aud_rate');
        return $this->updateAllMetalPrices();
    }

    /**
     * Get cache status and last update time
     */
    public function getCacheStatus()
    {
        $hasCache = Cache::has('live_metal_prices');
        $lastUpdate = $hasCache ? Cache::get('live_metal_prices_timestamp', 'Unknown') : 'Never';

        return [
            'has_cache' => $hasCache,
            'last_update' => $lastUpdate,
            'cache_minutes' => $this->cacheMinutes,
        ];
    }

    /**
     * Calculate price for specific product configuration
     */
    public function calculateProductPrice($metalSymbol, $karat, $weight, $laborCost = 15, $profitMargin = 25)
    {
        $metalCategory = MetalCategory::where('symbol', $metalSymbol)->active()->first();

        if (!$metalCategory) {
            return 0;
        }

        $pricePerGram = $metalCategory->calculatePricePerGram($karat);
        $metalValue = $weight * $pricePerGram;
        $totalLaborCost = $weight * $laborCost;
        $baseCost = $metalValue + $totalLaborCost;
        $profitAmount = $baseCost * ($profitMargin / 100);
        $finalPrice = $baseCost + $profitAmount;

        return [
            'metal_value' => round($metalValue, 2),
            'labor_cost' => round($totalLaborCost, 2),
            'base_cost' => round($baseCost, 2),
            'profit_amount' => round($profitAmount, 2),
            'final_price' => round($finalPrice, 2),
            'price_per_gram' => round($pricePerGram, 2),
        ];
    }

public function getExchangeRate()
{
    return $this->getAudRate();
}

}
