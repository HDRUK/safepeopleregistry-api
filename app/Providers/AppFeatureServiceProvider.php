<?php

namespace App\Providers;

use Laravel\Pennant\Feature;
use Illuminate\Support\ServiceProvider;

class AppFeatureServiceProvider extends ServiceProvider
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
        // feature with global scope (example)
        Feature::define('test-feature', fn () => true);

        Feature::define('test-feature-user-admin', function ($user = null) {
            if (!$user) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }

            return false;
        });

        // feature with user-specific scope (example)
        Feature::define('test-feature-user-1', function ($user = null) {
            if (!$user) {
                return false;
            }

            return $user->id === 1;
        });

        // access laravel horizon
        Feature::define('horizon-access', function ($user) {
            // return $user->isAdmin();
            return $user !== null;
        });
    }
}
