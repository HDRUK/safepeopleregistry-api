<?php

namespace App\Providers;

use App\Models\User;
use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Log;
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

        // Horizon::auth(function ($request) {
        //     return Gate::check('viewHorizon', $request->user());
        // });
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user = null) {
            // if (config('app.env') !== 'prod') {
            //     return true;
            // }

            if (!$user) {
                return false;
            }

            Log::info('HorizonServiceProvider - viewHorizon', [
                'user' => $user,
                'userGroup' => $user->user_group
            ]);
            if ($user->user_group === User::GROUP_ADMINS) {
                return true;
            }

            return false;
        });
    }
}
