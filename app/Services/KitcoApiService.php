<?php
// File: app/Services/KitcoApiService.php (Updated to use metals-api.com)

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KitcoApiService
{
    protected $apiKey;
    protected $apiUrl;
    protected $cacheKey = 'metal_prices';
    protected $cacheDuration = 300; // 5 minutes

    public function __construct()
    {
        $this->apiKey = '1c70eyqb8hpkqcg8bmtfwalg6u84j20qv9gq8fq7k2h8f6fi9m7p61sxkmng';
        $this->apiUrl = 'https://metals-api.com/api';
    }

    /**
     * Get current metal prices from metals-api.com
     */
    public function getCurrentMetalPrices()
    {
        return Cache::remember($this->cacheKey . '_current', $this->cacheDuration, function () {
            try {
                $response = Http::timeout(10)->get($this->apiUrl . '/latest', [
                    'access_key' => $this->apiKey,
                    'base' => 'AUD',
                    'symbols' => 'XAU,XAG,XPD,XPT'
                ]);

                if (!$response->successful()) {
                    throw new \Exception("API request failed with status: " . $response->status());
                }

                $data = $response->json();

                if (!$data['success'] || !isset($data['rates'])) {
                    throw new \Exception("Invalid API response format");
                }

                // Store pure metal prices per troy ounce in AUD
                $prices = [
                    'XAU' => $data['rates']['XAU'], // Pure 24K Gold per troy ounce AUD
                    'XAG' => $data['rates']['XAG'], // Pure 999 Silver per troy ounce AUD
                    'XPD' => $data['rates']['XPD'], // Pure 999 Palladium per troy ounce AUD
                    'XPT' => $data['rates']['XPT'], // Pure 999 Platinum per troy ounce AUD
                    'timestamp' => $data['timestamp'],
                    'date' => $data['date'],
                    'last_updated' => now()->toISOString()
                ];

                return $prices;

            } catch (\Exception $e) {
                Log::warning('Metal price API failed: ' . $e->getMessage());
                throw $e; // Don't use fallback - force real API data
            }
        });
    }

    /**
     * Get current gold price in AUD per ounce (pure 24K)
     */
    public function getCurrentGoldPrice()
    {
        $prices = $this->getCurrentMetalPrices();
        return $prices['XAU'];
    }

    /**
     * Calculate gold prices for all karats in AUD per gram
     */
    public function getCurrentPrices()
    {
        $metalPrices = $this->getCurrentMetalPrices();
        $goldPricePerOz = $metalPrices['XAU']; // Pure 24K gold per troy ounce AUD
        $gramsPerTroyOz = 31.1035;

        // Convert to AUD per gram for 24K
        $goldPricePerGramAud = $goldPricePerOz / $gramsPerTroyOz;

        // Calculate prices for all karats: Price(karat) = Price(24K) × (karat/24)
        $availableKarats = ['9', '10', '14', '18', '21', '22', '24'];
        $karatPrices = [];

        foreach ($availableKarats as $karat) {
            $purityRatio = (int)$karat / 24;
            $karatPrices[$karat] = round($goldPricePerGramAud * $purityRatio, 2);
        }

        return $karatPrices;
    }

    /**
     * Get all metal prices in AUD per gram
     */
    public function getAllMetalPricesAUD()
    {
        $metalPrices = $this->getCurrentMetalPrices();
        $gramsPerTroyOz = 31.1035;

        $result = [
            'gold' => [],
            'silver' => [],
            'palladium' => [],
            'platinum' => [],
            'last_updated' => $metalPrices['last_updated'],
            'api_timestamp' => $metalPrices['timestamp'],
            'api_date' => $metalPrices['date']
        ];

        // Gold karats (9K, 10K, 14K, 18K, 21K, 22K, 24K)
        $goldPricePerGramAud = $metalPrices['XAU'] / $gramsPerTroyOz;
        $goldKarats = [9, 10, 14, 18, 21, 22, 24];
        foreach ($goldKarats as $karat) {
            $purityRatio = $karat / 24;
            $result['gold'][(string)$karat] = round($goldPricePerGramAud * $purityRatio, 2);
        }

        // Silver purities
        $silverPricePerGramAud = $metalPrices['XAG'] / $gramsPerTroyOz;
        $result['silver']['925'] = round($silverPricePerGramAud * 0.925, 2);
        $result['silver']['950'] = round($silverPricePerGramAud * 0.950, 2);
        $result['silver']['999'] = round($silverPricePerGramAud * 0.999, 2);

        // Palladium purities
        $palladiumPricePerGramAud = $metalPrices['XPD'] / $gramsPerTroyOz;
        $result['palladium']['500'] = round($palladiumPricePerGramAud * 0.500, 2);
        $result['palladium']['950'] = round($palladiumPricePerGramAud * 0.950, 2);
        $result['palladium']['999'] = round($palladiumPricePerGramAud * 0.999, 2);

        // Platinum purities
        $platinumPricePerGramAud = $metalPrices['XPT'] / $gramsPerTroyOz;
        $result['platinum']['900'] = round($platinumPricePerGramAud * 0.900, 2);
        $result['platinum']['950'] = round($platinumPricePerGramAud * 0.950, 2);
        $result['platinum']['999'] = round($platinumPricePerGramAud * 0.999, 2);

        return $result;
    }

    /**
     * Calculate price per gram for specific karat
     */
    public function calculatePricePerGram($karat)
    {
        $goldPricePerOz = $this->getCurrentGoldPrice();
        $gramsPerTroyOz = 31.1035;

        // Convert to AUD per gram for 24K
        $goldPricePerGramAud = $goldPricePerOz / $gramsPerTroyOz;

        // Extract numeric karat value
        $karatValue = is_string($karat) ? (int)str_replace('K', '', $karat) : (int)$karat;

        // Calculate price for given karat: Price(karat) = Price(24K) × (karat/24)
        return round($goldPricePerGramAud * ($karatValue/24), 2);
    }

    /**
     * Get price in AUD (already in AUD from API)
     */
    public function getCurrentGoldPriceAUD()
    {
        return $this->getCurrentGoldPrice();
    }

    /**
     * No conversion needed - already in AUD
     */
    public function convertToAUD($audAmount)
    {
        return $audAmount;
    }

    /**
     * Refresh prices (force update)
     */
    public function refreshPrice()
    {
        Cache::forget($this->cacheKey . '_current');
        Cache::forget($this->cacheKey . '_market_data');

        return $this->getCurrentGoldPrice();
    }

    /**
     * Get market data and status
     */
    public function getMarketData()
    {
        return Cache::remember($this->cacheKey . '_market_data', $this->cacheDuration, function () {
            try {
                $currentTime = now();
                $isWeekend = $currentTime->isWeekend();
                $hour = $currentTime->hour;

                // Simplified market hours (metals market: generally 24/5)
                $isMarketHours = !$isWeekend && $hour >= 0 && $hour <= 23;

                return [
                    'status' => $isMarketHours ? 'open' : 'closed',
                    'last_updated' => $currentTime->toISOString(),
                    'currency' => 'AUD',
                    'unit' => 'per ounce',
                    'source' => 'metals-api.com'
                ];

            } catch (\Exception $e) {
                Log::warning('Market data fetch failed: ' . $e->getMessage());
                return [
                    'status' => 'unknown',
                    'last_updated' => now()->toISOString(),
                    'currency' => 'AUD',
                    'unit' => 'per ounce',
                    'source' => 'metals-api.com'
                ];
            }
        });
    }

    /**
     * Get market status
     */
    public function getMarketStatus()
    {
        $marketData = $this->getMarketData();
        return $marketData['status'] ?? 'unknown';
    }

    /**
     * Get last updated timestamp
     */
    public function getLastUpdated()
    {
        $marketData = $this->getMarketData();
        return $marketData['last_updated'] ?? now()->toISOString();
    }

    /**
     * Health check for the service
     */
    public function healthCheck()
    {
        try {
            $price = $this->getCurrentGoldPrice();
            return $price > 0 ? 'healthy' : 'degraded';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Get price trend (up/down/stable)
     */
    public function getPriceTrend()
    {
        try {
            $currentPrice = $this->getCurrentGoldPrice();
            $previousPrice = Cache::get($this->cacheKey . '_previous', $currentPrice);

            // Store current price as previous for next check
            Cache::put($this->cacheKey . '_previous', $currentPrice, $this->cacheDuration * 2);

            $difference = $currentPrice - $previousPrice;

            if (abs($difference) < 50) { // Adjusted for AUD prices
                return 'stable';
            } elseif ($difference > 0) {
                return 'up';
            } else {
                return 'down';
            }

        } catch (\Exception $e) {
            return 'stable';
        }
    }

    /**
     * Clear all cached prices
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey . '_current');
        Cache::forget($this->cacheKey . '_market_data');
        Cache::forget($this->cacheKey . '_previous');

        return true;
    }

    /**
     * Get formatted price display
     */
    public function getFormattedPrice($currency = 'AUD')
    {
        $price = $this->getCurrentGoldPrice();
        return 'AUD$' . number_format($price, 2);
    }

    /**
     * Get cache status
     */
    public function getCacheStatus()
    {
        return [
            'current_price_cached' => Cache::has($this->cacheKey . '_current'),
            'market_data_cached' => Cache::has($this->cacheKey . '_market_data'),
            'cache_ttl' => $this->cacheDuration,
            'last_updated' => $this->getLastUpdated()
        ];
    }

    // Legacy methods for compatibility with existing Product model
    public function getGoldPriceByKarat($karat)
    {
        return $this->calculatePricePerGram($karat);
    }

    public function getGoldPricePerGram($karat = '14K')
    {
        return $this->calculatePricePerGram($karat);
    }

    /**
     * Get current price for specific metal symbol
     */
    public function getCurrentPrice($symbol)
    {
        try {
            $metalPrices = $this->getCurrentMetalPrices();

            if (!isset($metalPrices[$symbol])) {
                return [
                    'success' => false,
                    'error' => "Price not available for symbol: {$symbol}"
                ];
            }

            return [
                'success' => true,
                'price' => $metalPrices[$symbol],
                'symbol' => $symbol,
                'timestamp' => $metalPrices['timestamp'] ?? time(),
                'last_updated' => $metalPrices['last_updated'] ?? now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::warning("Failed to get current price for {$symbol}: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * No exchange rate needed - already in AUD
     */
    public function getExchangeRate()
    {
        return 1.0;
    }

    /**
     * Get AUD rate (always 1.0 since API returns AUD)
     */
    public function getAudRate()
    {
        return 1.0;
    }
}
