<?php

namespace App\Providers;

use App\Gateway\Gateway;
use Illuminate\Support\ServiceProvider;

class GatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services
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
        $this->app->bind('gateway', function () {
            return new Gateway();
        });
    }
}
