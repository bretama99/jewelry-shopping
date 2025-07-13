<?php
// File: app/Services/KitcoApiService.php (Updated to use MetalPriceAPI)

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
    protected $fallbackPrices = [
        'XAU' => 2000, // Gold fallback price in USD per ounce
        'XAG' => 25,   // Silver fallback price in USD per ounce
        'XPD' => 1000, // Palladium fallback price in USD per ounce
        'XPT' => 1050  // Platinum fallback price in USD per ounce
    ];

    public function __construct()
    {
        $this->apiKey = 'd68f51781cca05150ab380fbea59224c';
        $this->apiUrl = 'https://api.metalpriceapi.com/v1';
    }

    /**
     * Get current metal prices from MetalPriceAPI
     */
    public function getCurrentMetalPrices()
    {
        return Cache::remember($this->cacheKey . '_current', $this->cacheDuration, function () {
            try {
                $response = Http::timeout(10)->get($this->apiUrl . '/latest', [
                    'api_key' => $this->apiKey,
                    'base' => 'USD',
                    'currencies' => 'XAU,XAG,XPD,XPT'
                ]);

                if (!$response->successful()) {
                    throw new \Exception("API request failed with status: " . $response->status());
                }

                $data = $response->json();

                if (!$data['success'] || !isset($data['rates'])) {
                    throw new \Exception("Invalid API response format");
                }

                // Convert API rates to prices per troy ounce in USD
                $prices = [
                    'XAU' => isset($data['rates']['USDXAU']) ? (1 / $data['rates']['USDXAU']) : $this->fallbackPrices['XAU'],
                    'XAG' => isset($data['rates']['USDXAG']) ? (1 / $data['rates']['USDXAG']) : $this->fallbackPrices['XAG'],
                    'XPD' => isset($data['rates']['USDXPD']) ? (1 / $data['rates']['USDXPD']) : $this->fallbackPrices['XPD'],
                    'XPT' => isset($data['rates']['USDXPT']) ? (1 / $data['rates']['USDXPT']) : $this->fallbackPrices['XPT'],
                    'timestamp' => $data['timestamp'],
                    'last_updated' => now()->toISOString()
                ];

                return $prices;

            } catch (\Exception $e) {
                Log::warning('Metal price API failed: ' . $e->getMessage());
                return $this->getFallbackPrices();
            }
        });
    }

    /**
     * Get current gold price in USD per ounce
     */
    public function getCurrentGoldPrice()
    {
        $prices = $this->getCurrentMetalPrices();
        return $prices['XAU'];
    }

    /**
     * Get fallback prices when API is unavailable
     */
    protected function getFallbackPrices()
    {
        // Return fallback prices with small time-based variation
        $variation = sin(time() / 3600) * 50; // Small hourly variation

        return [
            'XAU' => round($this->fallbackPrices['XAU'] + $variation, 2),
            'XAG' => round($this->fallbackPrices['XAG'] + ($variation * 0.01), 2),
            'XPD' => round($this->fallbackPrices['XPD'] + ($variation * 0.5), 2),
            'XPT' => round($this->fallbackPrices['XPT'] + ($variation * 0.5), 2),
            'timestamp' => time(),
            'last_updated' => now()->toISOString(),
            'fallback' => true
        ];
    }

    /**
     * Calculate gold prices for all karats in AUD per gram
     */
    public function getCurrentPrices()
    {
        $metalPrices = $this->getCurrentMetalPrices();
        $goldPricePerOz = $metalPrices['XAU'];
        $audRate = $this->getAudRate();
        $gramsPerTroyOz = 31.1035;

        // Convert to AUD per gram for 24K
        $goldPricePerGramAud = ($goldPricePerOz * $audRate) / $gramsPerTroyOz;

        // Calculate prices for all karats using: Price(karat) = Price(24K) × (karat/24)
        return [
            '9' => round($goldPricePerGramAud * (9/24), 2),
            '10' => round($goldPricePerGramAud * (10/24), 2),
            '14' => round($goldPricePerGramAud * (14/24), 2),
            '18' => round($goldPricePerGramAud * (18/24), 2),
            '19' => round($goldPricePerGramAud * (19/24), 2),
            '20' => round($goldPricePerGramAud * (20/24), 2),
            '21' => round($goldPricePerGramAud * (21/24), 2),
            '22' => round($goldPricePerGramAud * (22/24), 2),
            '23' => round($goldPricePerGramAud * (23/24), 2),
            '24' => round($goldPricePerGramAud * (24/24), 2),
        ];
    }

    /**
     * Get all metal prices in AUD per gram
     */
    public function getAllMetalPricesAUD()
    {
        $metalPrices = $this->getCurrentMetalPrices();
        $audRate = $this->getAudRate();
        $gramsPerTroyOz = 31.1035;

        $result = [
            'gold' => [],
            'silver' => [],
            'palladium' => [],
            'platinum' => [],
            'last_updated' => $metalPrices['last_updated']
        ];

        // Gold karats
        $goldPricePerGramAud = ($metalPrices['XAU'] * $audRate) / $gramsPerTroyOz;
        for ($karat = 9; $karat <= 24; $karat++) {
            $result['gold'][(string)$karat] = round($goldPricePerGramAud * ($karat/24), 2);
        }

        // Silver purities
        $silverPricePerGramAud = ($metalPrices['XAG'] * $audRate) / $gramsPerTroyOz;
        $result['silver']['925'] = round($silverPricePerGramAud * 0.925, 2);
        $result['silver']['950'] = round($silverPricePerGramAud * 0.950, 2);
        $result['silver']['999'] = round($silverPricePerGramAud * 0.999, 2);

        // Palladium and Platinum
        $palladiumPricePerGramAud = ($metalPrices['XPD'] * $audRate) / $gramsPerTroyOz;
        $result['palladium']['950'] = round($palladiumPricePerGramAud * 0.950, 2);
        $result['palladium']['999'] = round($palladiumPricePerGramAud * 0.999, 2);

        $platinumPricePerGramAud = ($metalPrices['XPT'] * $audRate) / $gramsPerTroyOz;
        $result['platinum']['900'] = round($platinumPricePerGramAud * 0.900, 2);
        $result['platinum']['950'] = round($platinumPricePerGramAud * 0.950, 2);
        $result['platinum']['999'] = round($platinumPricePerGramAud * 0.999, 2);

        return $result;
    }

    /**
     * Get AUD/USD exchange rate
     */
    protected function getAudRate()
    {
        return Cache::remember('usd_aud_rate', 3600, function () {
            try {
                $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates']['AUD'] ?? 1.45; // Fallback rate
                }
                return 1.45; // Fallback USD to AUD rate
            } catch (\Exception $e) {
                Log::warning('Currency conversion failed: ' . $e->getMessage());
                return 1.45; // Fallback rate
            }
        });
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

                // Simplified market hours (NYSE: 9:30 AM - 4:00 PM EST)
                $isMarketHours = !$isWeekend && $hour >= 9 && $hour <= 16;

                return [
                    'status' => $isMarketHours ? 'open' : 'closed',
                    'last_updated' => $currentTime->toISOString(),
                    'currency' => 'USD',
                    'unit' => 'per ounce',
                    'source' => 'MetalPriceAPI'
                ];

            } catch (\Exception $e) {
                Log::warning('Market data fetch failed: ' . $e->getMessage());
                return [
                    'status' => 'unknown',
                    'last_updated' => now()->toISOString(),
                    'currency' => 'USD',
                    'unit' => 'per ounce',
                    'source' => 'Fallback Data'
                ];
            }
        });
    }

    /**
     * Calculate price per gram for specific karat
     */
    public function calculatePricePerGram($karat)
    {
        $goldPricePerOz = $this->getCurrentGoldPrice();
        $audRate = $this->getAudRate();
        $gramsPerTroyOz = 31.1035;

        // Convert to AUD per gram for 24K
        $goldPricePerGramAud = ($goldPricePerOz * $audRate) / $gramsPerTroyOz;

        // Extract numeric karat value
        $karatValue = is_string($karat) ? (int)str_replace('K', '', $karat) : (int)$karat;

        // Calculate price for given karat: Price(karat) = Price(24K) × (karat/24)
        return round($goldPricePerGramAud * ($karatValue/24), 2);
    }

    /**
     * Get price in AUD
     */
    public function getCurrentGoldPriceAUD()
    {
        $usdPrice = $this->getCurrentGoldPrice();
        $audRate = $this->getAudRate();
        return round($usdPrice * $audRate, 2);
    }

    /**
     * Convert USD to AUD
     */
    public function convertToAUD($usdAmount)
    {
        $audRate = $this->getAudRate();
        return round($usdAmount * $audRate, 2);
    }

    /**
     * Refresh prices (force update)
     */
    public function refreshPrice()
    {
        Cache::forget($this->cacheKey . '_current');
        Cache::forget($this->cacheKey . '_market_data');
        Cache::forget('usd_aud_rate');

        return $this->getCurrentGoldPrice();
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

            if (abs($difference) < 5) {
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
        Cache::forget('usd_aud_rate');

        return true;
    }

    /**
     * Get formatted price display
     */
    public function getFormattedPrice($currency = 'USD')
    {
        if ($currency === 'AUD') {
            $price = $this->getCurrentGoldPriceAUD();
            return 'AUD'  . number_format($price, 2);
        } else {
            $price = $this->getCurrentGoldPrice();
            return 'USD'  . number_format($price, 2);
        }
    }

    /**
     * Get cache status
     */
    public function getCacheStatus()
    {
        return [
            'current_price_cached' => Cache::has($this->cacheKey . '_current'),
            'market_data_cached' => Cache::has($this->cacheKey . '_market_data'),
            'exchange_rate_cached' => Cache::has('usd_aud_rate'),
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
     * Get historical price data (simplified mock for now)
     */
    public function getHistoricalPrices($days = 30)
    {
        // Generate mock historical data for demonstration
        $prices = [];
        $basePrice = $this->getCurrentGoldPrice();

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $variation = (sin($i / 7) * 100) + (rand(-50, 50)); // Mock price variation
            $price = round($basePrice + $variation, 2);

            $prices[] = [
                'date' => $date->format('Y-m-d'),
                'price' => $price,
                'change' => $i > 0 ? round($price - ($basePrice + (sin(($i-1) / 7) * 100)), 2) : 0
            ];
        }

        return $prices;
    }

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
        
        // Return fallback price
        $fallbackPrice = $this->fallbackPrices[$symbol] ?? 0;
        
        return [
            'success' => true,
            'price' => $fallbackPrice,
            'symbol' => $symbol,
            'timestamp' => time(),
            'last_updated' => now()->toISOString(),
            'fallback' => true
        ];
    }
}

/**
 * Get exchange rate (alias for getAudRate for compatibility)
 * @return float
 */
public function getExchangeRate()
{
    return $this->getAudRate();
}

/**
 * Get current price for specific metal symbol (for compatibility with KitcoApiService)
 * @param string $symbol Metal symbol (XAU, XAG, XPD, XPT)
 * @return array
 */

}
