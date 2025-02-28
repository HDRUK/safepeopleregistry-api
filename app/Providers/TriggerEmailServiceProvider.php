<?php

namespace App\Providers;

use App\TriggerEmail\TriggerEmail;
use Illuminate\Support\ServiceProvider;

class TriggerEmailServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->app->bind('triggeremail', function () {
            return new TriggerEmail();
        });
    }
}
