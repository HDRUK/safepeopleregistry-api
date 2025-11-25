<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FrankenPhpServiceProvider extends ServiceProvider
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
        if (isset($_SERVER['FRANKENPHP_WORKER'])) {
            $this->app->terminating(function () {
                gc_collect_cycles();
            });
        }
    }
}
