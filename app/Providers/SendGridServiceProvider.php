<?php

namespace App\Providers;

use App\Services\SendGridService;
use Illuminate\Support\ServiceProvider;

class SendGridServiceProvider extends ServiceProvider
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
        $this->app->singleton('sendgrid', function ($app) {
            return new SendGridService();
        });
    }
}