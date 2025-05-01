<?php

namespace App\Providers;

use App\Models\Custodian;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //role based gate
        Gate::define('admin', function (User $user): bool {
            return (bool) $user->user_group === User::GROUP_ADMINS;
        });

        //permission
        Gate::define('custodian.view', function (User $user, int $custodianId): bool {
            if (Gate::forUser($user)->allows('admin')) {
                return true;
            }

            return optional($user->custodian_user)->custodian_id === $custodianId;
        });
    }
}
