<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RegistryManagementControllerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->bind('registrymanagementcontroller', function () {
            return new RMC();
        });
    }
}
