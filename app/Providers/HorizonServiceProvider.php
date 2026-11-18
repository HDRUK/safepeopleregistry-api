<?php

namespace App\Providers;

use App\Models\User;
use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');

        // Dashboard users authenticate via a browser session on the web guard
        // (see HorizonAuthController), not the API's Keycloak Bearer guard.
        // Overrides Horizon's default, which also lets everyone in on APP_ENV=local.
        Horizon::auth(function ($request) {
            return Gate::forUser(Auth::guard('web')->user())->check('viewHorizon');
        });
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        // Super-admins only, in every environment: job payloads can contain
        // personal data. See docs/ARCHITECTURE/HORIZON_DASHBOARD_AUTH.md.
        Gate::define('viewHorizon', function (?User $user = null) {
            return $user?->isAdmin() ?? false;
        });
    }
}
