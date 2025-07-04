<?php

namespace App\Providers;

use App\Models\File;
use App\Models\ONSFile;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\CustodianHasRule;
use App\Models\ProjectHasUser;
use App\Models\User;
use App\Models\UserHasDepartments;
use App\Models\Organisation;
use App\Models\OrganisationHasSubsidiary;
use App\Models\Affiliation;
use App\Models\ProjectHasOrganisation;
use App\Models\ProjectHasCustodian;
use App\Models\CustodianHasProjectOrganisation;
use App\Models\RegistryReadRequest;
use App\Models\CustodianHasValidationCheck;
use App\Models\RegistryHasTraining;
use App\Observers\FileObserver;
use App\Observers\ONSFileObserver;
use App\Observers\RegistryObserver;
use App\Observers\CustodianObserver;
use App\Observers\CustodianHasRuleObserver;
use App\Observers\ProjectHasUserObserver;
use App\Observers\ProjectHasOrganisationObserver;
use App\Observers\UserObserver;
use App\Observers\UserHasDepartmentsObserver;
use App\Observers\OrganisationObserver;
use App\Observers\OrganisationHasSubsidiaryObserver;
use App\Observers\CustodianUserObserver;
use App\Observers\AffiliationObserver;
use App\Observers\ProjectHasCustodianObserver;
use App\Observers\AuditModelObserver;
use App\Observers\RegistryReadRequestObserver;
use App\Observers\CustodianHasValidationCheckObserver;
use App\Observers\CustodianHasProjectOrganisationObserver;
use App\Observers\RegistryHasTrainingObserver;
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

        // Octane::tick('gc', function () {
        //     Log::info('[Worker Memory]', [
        //         'pid' => getmypid(),
        //         'memory_kb' => round(memory_get_usage(true) / 1024),
        //         'peak_memory_kb' => round(memory_get_peak_usage(true) / 1024),
        //     ]);
        // })->seconds(10);
    }
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        File::observe(FileObserver::class);
        ONSFile::observe(ONSFileObserver::class);
        Registry::observe(RegistryObserver::class);
        ProjectHasUser::observe(ProjectHasUserObserver::class);
        Custodian::observe(CustodianObserver::class);
        CustodianHasRule::observe(CustodianHasRuleObserver::class);
        CustodianUser::observe(CustodianUserObserver::class);
        User::observe(UserObserver::class);
        UserHasDepartments::observe(UserHasDepartmentsObserver::class);
        Organisation::observe(OrganisationObserver::class);
        OrganisationHasSubsidiary::observe(OrganisationHasSubsidiaryObserver::class);
        Affiliation::observe(AffiliationObserver::class);
        Affiliation::observe(AffiliationObserver::class);
        ProjectHasCustodian::observe(ProjectHasCustodianObserver::class);
        ProjectHasOrganisation::observe(ProjectHasOrganisationObserver::class);
        CustodianHasProjectOrganisation::observe(CustodianHasProjectOrganisationObserver::class);
        CustodianHasValidationCheck::observe(CustodianHasValidationCheckObserver::class);
        RegistryReadRequest::observe(RegistryReadRequestObserver::class);
        RegistryHasTraining::observe(RegistryHasTrainingObserver::class);
<<<<<<< HEAD
        RegistryHasTraining::observe(RegistryHasTrainingObserver::class);
=======
>>>>>>> b97e17d (add training check observer)
        // currently Training but is to be moved to RegistryHasTraining...
        // RegistryHasTraining::observe(RegistryHasTrainingObserver::class);
    }
}
