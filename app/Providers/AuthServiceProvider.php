<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Project;
use App\Models\Identity;
use App\Models\Registry;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\Department;
use App\Models\Experience;
use App\Models\Affiliation;
use App\Models\Endorsement;
use App\Models\Organisation;
use App\Policies\UserPolicy;
use App\Models\CustodianUser;
use App\Models\ValidationLog;
use App\Models\ValidationLogComment;
use App\Policies\ProjectPolicy;
use App\Policies\IdentityPolicy;
use App\Policies\RegistryPolicy;
use App\Policies\ActionLogPolicy;
use App\Policies\CustodianPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\ExperiencePolicy;
use App\Policies\AffiliationPolicy;
use App\Policies\EndorsementPolicy;
use App\Policies\OrganisationPolicy;
use Illuminate\Support\Facades\Gate;
use App\Policies\CustodianUserPolicy;
use App\Policies\ValidationLogPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ActionLog::class => ActionLogPolicy::class,
        Affiliation::class => AffiliationPolicy::class,
        Custodian::class => CustodianPolicy::class,
        CustodianUser::class => CustodianUserPolicy::class,
        Department::class => DepartmentPolicy::class,
        Endorsement::class => EndorsementPolicy::class,
        Experience::class => ExperiencePolicy::class,
        Identity::class => IdentityPolicy::class,
        Organisation::class => OrganisationPolicy::class,
        Project::class => ProjectPolicy::class,
        Registry::class => RegistryPolicy::class,
        User::class => UserPolicy::class,
        ValidationLogComment::class => ValidationLogPolicy::class,
        ValidationLog::class => ValidationLogPolicy::class,
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
