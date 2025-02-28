<?php

namespace App\Providers;

use App\RegistryManagementController\RegistryManagementController;
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
            return new RegistryManagementController();
        });
    }
}
