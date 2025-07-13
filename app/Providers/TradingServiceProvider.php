<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MetalPriceApiService;

class TradingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(MetalPriceApiService::class, function ($app) {
            return new MetalPriceApiService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Boot logic if needed
    }
}
