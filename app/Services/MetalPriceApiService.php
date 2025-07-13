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

    public function __construct()
    {
        $this->apiKey = '1c70eyqb8hpkqcg8bmtfwalg6u84j20qv9gq8fq7k2h8f6fi9m7p61sxkmng';
        $this->baseUrl = 'https://metals-api.com/api';
        $this->cacheMinutes = 5; // Cache for 5 minutes
    }

    /**
     * Fetch live metal prices from metals-api.com
     */
    public function fetchLivePrices()
    {
        return Cache::remember('live_metal_prices', $this->cacheMinutes * 60, function () {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/latest', [
                    'access_key' => $this->apiKey,
                    'base' => 'AUD',
                    'symbols' => 'XAU,XAG,XPD,XPT'
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if ($data['success'] && isset($data['rates'])) {
                        $rates = $data['rates'];

                        // Store pure metal prices per troy ounce in AUD
                        $purePrices = [
                            'XAU' => $rates['XAU'], // Pure 24K Gold per troy ounce AUD
                            'XAG' => $rates['XAG'], // Pure 999 Silver per troy ounce AUD
                            'XPD' => $rates['XPD'], // Pure 999 Palladium per troy ounce AUD
                            'XPT' => $rates['XPT'], // Pure 999 Platinum per troy ounce AUD
                            'timestamp' => $data['timestamp'],
                            'date' => $data['date']
                        ];

                        Log::info('Live metal prices fetched successfully from metals-api.com', $purePrices);
                        return $purePrices;
                    }
                }

                throw new \Exception('Invalid API response format');
            } catch (\Exception $e) {
                Log::error('Failed to fetch metal prices from metals-api.com: ' . $e->getMessage());
                throw $e; // Don't use fallback - force real API data
            }
        });
    }

    /**
     * Update all metal categories with live prices
     */
    public function updateAllMetalPrices()
    {
        $livePrices = $this->fetchLivePrices();
        $metalCategories = MetalCategory::active()->get();
        $updateCount = 0;

        foreach ($metalCategories as $metalCategory) {
            if (isset($livePrices[$metalCategory->symbol])) {
                $purePricePerOunce = $livePrices[$metalCategory->symbol];
                $metalCategory->updatePriceFromApi($purePricePerOunce, 1.0); // No currency conversion needed
                $updateCount++;
            }
        }

        Log::info("Updated prices for {$updateCount} metal categories from metals-api.com");
        return $updateCount;
    }

    /**
     * Get prices for all metals in AUD per gram by karat/purity
     */
    public function getAllMetalPricesAUD()
    {
        $livePrices = $this->fetchLivePrices();
        $gramsPerTroyOz = 31.1035;

        $allPrices = [
            'gold' => [],
            'silver' => [],
            'palladium' => [],
            'platinum' => [],
            'last_updated' => $livePrices['timestamp'] ?? time(),
            'date' => $livePrices['date'] ?? date('Y-m-d')
        ];

        // Calculate Gold prices for all karats (9K, 10K, 14K, 18K, 21K, 22K, 24K)
        if (isset($livePrices['XAU'])) {
            $goldPure24KPerGram = $livePrices['XAU'] / $gramsPerTroyOz; // 24K gold price per gram

            $goldKarats = [9, 10, 14, 18, 21, 22, 24];
            foreach ($goldKarats as $karat) {
                // Calculate price based on karat purity: price = pure24K * (karat/24)
                $purityRatio = $karat / 24;
                $allPrices['gold'][(string)$karat] = round($goldPure24KPerGram * $purityRatio, 2);
            }
        }

        // Calculate Silver prices for different purities
        if (isset($livePrices['XAG'])) {
            $silverPure999PerGram = $livePrices['XAG'] / $gramsPerTroyOz; // Pure 999 silver price per gram

            $silverPurities = [
                '925' => 0.925, // Sterling silver
                '950' => 0.950, // Higher grade silver
                '999' => 0.999  // Pure silver
            ];

            foreach ($silverPurities as $purity => $ratio) {
                $allPrices['silver'][$purity] = round($silverPure999PerGram * $ratio, 2);
            }
        }

        // Calculate Platinum prices
        if (isset($livePrices['XPT'])) {
            $platinumPure999PerGram = $livePrices['XPT'] / $gramsPerTroyOz;

            $platinumPurities = [
                '900' => 0.900,
                '950' => 0.950,
                '999' => 0.999
            ];

            foreach ($platinumPurities as $purity => $ratio) {
                $allPrices['platinum'][$purity] = round($platinumPure999PerGram * $ratio, 2);
            }
        }

        // Calculate Palladium prices
        if (isset($livePrices['XPD'])) {
            $palladiumPure999PerGram = $livePrices['XPD'] / $gramsPerTroyOz;

            $palladiumPurities = [
                '500' => 0.500,
                '950' => 0.950,
                '999' => 0.999
            ];

            foreach ($palladiumPurities as $purity => $ratio) {
                $allPrices['palladium'][$purity] = round($palladiumPure999PerGram * $ratio, 2);
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
     * Force refresh prices (bypass cache)
     */
    public function forceRefreshPrices()
    {
        Cache::forget('live_metal_prices');
        return $this->updateAllMetalPrices();
    }

    /**
     * Get cache status and last update time
     */
    public function getCacheStatus()
    {
        $hasCache = Cache::has('live_metal_prices');
        $lastUpdate = $hasCache ? 'Cached' : 'Not cached';

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

    /**
     * Get current gold price for specific karat using live API data
     */
    public function getCurrentGoldPrice($karat = '24')
    {
        $livePrices = $this->fetchLivePrices();

        if (!isset($livePrices['XAU'])) {
            throw new \Exception('Gold price not available from API');
        }

        $gramsPerTroyOz = 31.1035;
        $goldPure24KPerGram = $livePrices['XAU'] / $gramsPerTroyOz;

        // Calculate price for specified karat
        $karatValue = (int) str_replace('K', '', $karat);
        $purityRatio = $karatValue / 24;

        return round($goldPure24KPerGram * $purityRatio, 2);
    }

    /**
     * Get all available karats for gold
     */
    public function getAvailableGoldKarats()
    {
        return ['9', '10', '14', '18', '21', '22', '24'];
    }

    /**
     * Get current prices for all gold karats using live API data
     */
    public function getAllGoldKaratPrices()
    {
        $livePrices = $this->fetchLivePrices();

        if (!isset($livePrices['XAU'])) {
            throw new \Exception('Gold price not available from API');
        }

        $gramsPerTroyOz = 31.1035;
        $goldPure24KPerGram = $livePrices['XAU'] / $gramsPerTroyOz;

        $karatPrices = [];
        $availableKarats = $this->getAvailableGoldKarats();

        foreach ($availableKarats as $karat) {
            $purityRatio = (int)$karat / 24;
            $karatPrices[$karat] = round($goldPure24KPerGram * $purityRatio, 2);
        }

        return $karatPrices;
    }

    /**
     * Compatibility method
     */
    public function getAudRate()
    {
        return 1.0; // Already in AUD from API
    }

    /**
     * Compatibility method
     */
    public function getExchangeRate()
    {
        return $this->getAudRate();
    }
}
