<?php
// File: app/Providers/MiddlewareServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);

        // Register middleware aliases
        $router->aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
        $router->aliasMiddleware('permission', \App\Http\Middleware\CheckPermission::class);
    }
}
