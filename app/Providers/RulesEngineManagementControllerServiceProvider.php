<?php

namespace App\Providers;

use App\RulesEngineManagementController\RulesEngineManagementController;
use Illuminate\Support\ServiceProvider;

class RulesEngineManagementControllerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->bind('rulesenginemanagementcontroller', function () {
            return new RulesEngineManagementController();
        });
    }
}
