<?php

namespace App\Providers;

use App\Models\File;
use App\Models\Project;
use App\Models\Registry;
use App\Models\Training;
use App\Models\Custodian;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\Organisation;
use App\Models\ProjectHasOrganisation;
use App\Models\ProjectHasUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->checkParams();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('app.rate_limit'))->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function checkParams(): void
    {
        $modelMap = [
            'custodianId'    => [Custodian::class, 'id', 'numeric'],
            'projectId'      => [Project::class, 'id', 'numeric'],
            // 'notificationId' => [Notification::class, 'id', 'numeric'],
            'registryId'     => [Registry::class, 'id', 'numeric'],
            'organisationId' => [Organisation::class, 'id', 'numeric'],
            'trainingId'     => [Training::class, 'id', 'numeric'],
            'fileId'        => [File::class, 'id', 'numeric'],
            'projectUserId'  => [ProjectHasUser::class, 'id', 'numeric'],
            'projectOrganisationId' => [ProjectHasOrganisation::class, 'id', 'numeric']
        ];

        $this->registerModelMap($modelMap);
    }

    protected function registerModelMap(array $map): void
    {
        foreach ($map as $param => [$modelClass, $column, $type]) {
            Route::bind($param, function ($value) use ($param, $modelClass, $column, $type) {
                $val = (string) $value;

                $resolver = function ($column, $val) use ($modelClass) {
                    return $modelClass::where($column, $val)->firstOrFail();
                };

                switch ($type) {
                    case 'numeric':
                        if (!ctype_digit($val)) {
                            abort(404, "Invalid {$param} (must be numeric)");
                        }
                        return $resolver($column, (int) $val);

                    case 'uuid':
                        if (!Str::isUuid($val)) {
                            abort(404, "Invalid {$param} (must be a valid UUID)");
                        }
                        return $resolver($column, $val);

                    default:
                        abort(404, "Invalid {$param} (unsupported type)");
                }
            });
        }
    }
}
