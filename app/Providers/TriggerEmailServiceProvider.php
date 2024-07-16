<?php

namespace App\Providers;

use App\TriggerEmail\TriggerEmail;

use Illuminate\Support\ServiceProvider;

class TriggerEmailServiceProvider extends ServiceProvider
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
     * Bootstrap services
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->app->bind('triggeremail', function() {
            return new TriggerEmail();
        });
    }

}