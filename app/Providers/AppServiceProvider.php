<?php

namespace App\Providers;

use App\Models\File;
use App\Models\ONSFile;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\ProjectHasUser;
use App\Models\User;
use App\Models\Affiliation;
use App\Observers\FileObserver;
use App\Observers\ONSFileObserver;
use App\Observers\RegistryObserver;
use App\Observers\CustodianObserver;
use App\Observers\ProjectHasUserObserver;
use App\Observers\UserObserver;
use App\Observers\AffiliationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
        User::observe(UserObserver::class);
        Affiliation::observe(AffiliationObserver::class);
    }
}
