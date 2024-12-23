<?php

namespace App\Providers;

use App\Models\File;
use App\Models\ONSFile;
use App\Models\ProjectHasUser;
use App\Observers\FileObserver;
use App\Observers\ONSFileObserver;
use App\Observers\ProjectHasUserObserver;
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
        ProjectHasUser::observe(ProjectHasUserObserver::class);
    }
}
