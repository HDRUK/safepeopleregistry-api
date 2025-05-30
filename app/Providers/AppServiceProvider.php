<?php

namespace App\Providers;

use App\Observers\AuditModelObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Event::listen('eloquent.*', function ($eventName, $payload) {
            $model = $payload[0] ?? null;

            if ($model instanceof Model) {
                App::make(AuditModelObserver::class)->handle($eventName, $model);
            }
        });
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
