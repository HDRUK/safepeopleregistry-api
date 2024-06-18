<?php

namespace App\Providers;

use App\OrcID\OrcID;

use Illuminate\Support\ServiceProvider;

class OrcIDServiceProvider extends ServiceProvider
{
    /**
     * Register services
     * 
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->app->bind('orcid', function() {
            return new OrcID();
        });
    }
}