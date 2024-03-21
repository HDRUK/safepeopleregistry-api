<?php

use App\Http\Controllers\Api\V1\{
    AuthController,
    QueryController,
    TrainingController,
    IssuerController,
    EndorsementController,
    ProjectController,
    RegistryController,
    ExperienceController,
    HistoryController,
    IdentityController,
    AffiliationController,
    InfringementController
};

use Laravel\Fortify\Http\Controllers\{
    AuthenticatedSessionController,
    RegisteredUserController,
    PasswordResetLinkController
};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['middleware' => 'auth:sanctum'], function() {
    // Auth routes
    Route::prefix('auth')->withoutMiddleware('auth:sanctum')->group(function() {
        $limiter = config('fortify.limiters.login');

        // Route for user login
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:' . config('fortify.guard'), // Only guests (non-auth'd users) are allowed
                $limiter ? 'throttle:' . $limiter : null // Throttle login attempts if limiter is configured
            ]));

        // Route for user registration
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware('guest:' . config('fortify.guard')); // Only guests (non-auth'd users) are allowed

        // Route for initiating password reset
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware('guest:' . config('fortify.guard')) // Only guests (non-auth'd users) are allowed
            ->name('password.reset'); // Name for the route
    });
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('v1/query', [QueryController::class, 'query']);

// Route::middleware('api')->get('v1/training', [TrainingController::class, 'index']);
// Route::middleware('api')->get('v1/training/{id}', [TrainingController::class, 'show']);
// Route::middleware('api')->post('v1/training', [TrainingController::class, 'store']);
// Route::middleware('api')->put('v1/training/{id}', [TrainingController::class, 'update']);
// Route::middleware('api')->patch('v1/training/{id}', [TrainingController::class, 'edit']);
// Route::middleware('api')->delete('v1/training/{id}', [TrainingController::class, 'destroy']);

// Route::middleware('api')->get('v1/issuers', [IssuerController::class, 'index']);
// Route::middleware('api')->get('v1/issuers/{id}', [IssuerController::class, 'show']);
// Route::middleware('api')->post('v1/issuers', [IssuerController::class, 'store']);
// Route::middleware('api')->put('v1/issuers/{id}', [IssuerController::class, 'update']);
// Route::middleware('api')->patch('v1/issuers/{id}', [IssuerController::class, 'edit']);
// Route::middleware('api')->delete('v1/issuers/{id}', [IssuerController::class, 'destroy']);

// Route::middleware('api')->get('v1/endorsements', [EndorsementController::class, 'index']);
// Route::middleware('api')->get('v1/endorsements/{id}', [EndorsementController::class, 'show']);
// Route::middleware('api')->post('v1/endorsements', [EndorsementController::class, 'store']);

// Route::middleware('api')->get('v1/projects', [ProjectController::class, 'index']);
// Route::middleware('api')->get('v1/projects/{id}', [ProjectController::class, 'show']);
// Route::middleware('api')->post('v1/projects', [ProjectController::class, 'store']);
// Route::middleware('api')->put('v1/projects/{id}', [ProjectController::class, 'update']);
// Route::middleware('api')->patch('v1/projects/{id}', [ProjectController::class, 'edit']);
// Route::middleware('api')->delete('v1/projects/{id}', [ProjectController::class, 'destroy']);

// Route::middleware('api')->get('v1/registries', [RegistryController::class, 'index']);
// Route::middleware('api')->get('v1/registries/{id}', [RegistryController::class, 'show']);
// Route::middleware('api')->post('v1/registries', [RegistryController::class, 'store']);
// Route::middleware('api')->put('v1/registries/{id}', [RegistryController::class, 'update']);
// Route::middleware('api')->patch('v1/registries/{id}', [RegistryController::class, 'edit']);
// Route::middleware('api')->delete('v1/registries/{id}', [RegistryController::class, 'destroy']);

// Route::middleware('api')->get('v1/experiences', [ExperienceController::class, 'index']);
// Route::middleware('api')->get('v1/experiences/{id}', [ExperienceController::class, 'show']);
// Route::middleware('api')->post('v1/experiences', [ExperienceController::class, 'store']);
// Route::middleware('api')->put('v1/experiences/{id}', [ExperienceController::class, 'update']);
// Route::middleware('api')->patch('v1/experiences/{id}', [ExperienceController::class, 'edit']);
// Route::middleware('api')->delete('v1/experiences/{id}', [ExperienceController::class, 'destroy']);

// Route::middleware('api')->get('v1/identities', [IdentityController::class, 'index']);
// Route::middleware('api')->get('v1/identities/{id}', [IdentityController::class, 'show']);
// Route::middleware('api')->post('v1/identities', [IdentityController::class, 'store']);
// Route::middleware('api')->put('v1/identities/{id}', [IdentityController::class, 'update']);
// Route::middleware('api')->patch('v1/identities/{id}', [IdentityController::class, 'edit']);
// Route::middleware('api')->delete('v1/identities/{id}', [IdentityController::class, 'destroy']);

// Route::middleware('api')->get('v1/affiliations', [AffiliationController::class, 'index']);
// Route::middleware('api')->get('v1/affiliations/{id}', [AffiliationController::class, 'show']);
// Route::middleware('api')->post('v1/affiliations', [AffiliationController::class, 'store']);
// Route::middleware('api')->put('v1/affiliations/{id}', [AffiliationController::class, 'update']);
// Route::middleware('api')->patch('v1/affiliations/{id}', [AffiliationController::class, 'edit']);
// Route::middleware('api')->delete('v1/affiliations/{id}', [AffiliationController::class, 'destroy']);

// Route::middleware('api')->get('v1/histories', [HistoryController::class, 'index']);
// Route::middleware('api')->get('v1/histories/{id}', [HistoryController::class, 'show']);
// Route::middleware('api')->post('v1/histories', [HistoryController::class, 'store']);

// Route::middleware('api')->get('v1/infringements', [InfringementController::class, 'index']);
// Route::middleware('api')->get('v1/infringements/{id}', [InfringementController::class, 'show']);
// Route::middleware('api')->post('v1/infringements', [InfringementController::class, 'store']);

// stop all all other routes
Route::any('{path}', function() {
    $response = [
        'message' => 'Resource not found',
    ];

    return response()->json($response)
        ->setStatusCode(404);
});