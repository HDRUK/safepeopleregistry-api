<?php

namespace App\Providers;

use App\OrcID\OrcID;
use Illuminate\Support\ServiceProvider;

class OrcIDServiceProvider extends ServiceProvider
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
        $this->app->bind('orcid', function () {
            return new OrcID();
        });
    }
}
