<?php

namespace App\Providers;

use App\Keycloak\Keycloak;

use Illuminate\Support\ServiceProvider;

class KeycloakServiceProvider extends ServiceProvider
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
        $this->app->bind('keycloak', function() {
            return new Keycloak();
        });
    }
}