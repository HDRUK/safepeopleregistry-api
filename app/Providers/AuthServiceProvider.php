<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\Project;
use App\Policies\UserPolicy;
use App\Policies\RegistryPolicy;
use App\Policies\CustodianPolicy;
use App\Policies\OrganisationPolicy;
use App\Policies\ProjectPolicy;
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
        Registry::class => RegistryPolicy::class,
        Custodian::class => CustodianPolicy::class,
        Organisation::class => OrganisationPolicy::class,
        Project::class => ProjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //role based gate
        Gate::define('admin', function (User $user): bool {
            return $user->user_group === User::GROUP_ADMINS;
        });
    }
}
