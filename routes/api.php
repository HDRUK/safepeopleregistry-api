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
    InfringementController,
    UserController
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

    Route::get('/me', [UserController::class, 'me']);

    Route::get('/training', [TrainingController::class, 'index']);
    Route::get('/training/{id}', [TrainingController::class, 'show']);
    Route::post('/training', [TrainingController::class, 'store']);
    Route::put('/training/{id}', [TrainingController::class, 'update']);
    Route::patch('/training/{id}', [TrainingController::class, 'edit']);
    Route::delete('/training/{id}', [TrainingController::class, 'destroy']);

    Route::get('/issuers', [IssuerController::class, 'index']);
    Route::get('/issuers/{id}', [IssuerController::class, 'show']);
    Route::post('/issuers', [IssuerController::class, 'store']);
    Route::put('/issuers/{id}', [IssuerController::class, 'update']);
    Route::patch('/issuers/{id}', [IssuerController::class, 'edit']);
    Route::delete('/issuers/{id}', [IssuerController::class, 'destroy']);
    
    Route::get('/endorsements', [EndorsementController::class, 'index']);
    Route::get('/endorsements/{id}', [EndorsementController::class, 'show']);
    Route::post('/endorsements', [EndorsementController::class, 'store']);
    
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::patch('/projects/{id}', [ProjectController::class, 'edit']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
    Route::get('/registries', [RegistryController::class, 'index']);
    Route::get('/registries/{id}', [RegistryController::class, 'show']);
    Route::post('/registries', [RegistryController::class, 'store']);
    Route::put('/registries/{id}', [RegistryController::class, 'update']);
    Route::patch('/registries/{id}', [RegistryController::class, 'edit']);
    Route::delete('/registries/{id}', [RegistryController::class, 'destroy']);
    
    Route::get('/experiences', [ExperienceController::class, 'index']);
    Route::get('/experiences/{id}', [ExperienceController::class, 'show']);
    Route::post('/experiences', [ExperienceController::class, 'store']);
    Route::put('/experiences/{id}', [ExperienceController::class, 'update']);
    Route::patch('/experiences/{id}', [ExperienceController::class, 'edit']);
    Route::delete('/experiences/{id}', [ExperienceController::class, 'destroy']);
    
    Route::get('/identities', [IdentityController::class, 'index']);
    Route::get('/identities/{id}', [IdentityController::class, 'show']);
    Route::post('/identities', [IdentityController::class, 'store']);
    Route::put('/identities/{id}', [IdentityController::class, 'update']);
    Route::patch('/identities/{id}', [IdentityController::class, 'edit']);
    Route::delete('/identities/{id}', [IdentityController::class, 'destroy']);
    
    Route::get('/affiliations', [AffiliationController::class, 'index']);
    Route::get('/affiliations/{id}', [AffiliationController::class, 'show']);
    Route::post('/affiliations', [AffiliationController::class, 'store']);
    Route::put('/affiliations/{id}', [AffiliationController::class, 'update']);
    Route::patch('/affiliations/{id}', [AffiliationController::class, 'edit']);
    Route::delete('/affiliations/{id}', [AffiliationController::class, 'destroy']);
    
    Route::get('/histories', [HistoryController::class, 'index']);
    Route::get('/histories/{id}', [HistoryController::class, 'show']);
    Route::post('/histories', [HistoryController::class, 'store']);
    
    Route::get('/infringements', [InfringementController::class, 'index']);
    Route::get('/infringements/{id}', [InfringementController::class, 'show']);
    Route::post('/infringements', [InfringementController::class, 'store']);    
});

// Public Query route
Route::post('/query', [QueryController::class, 'query']);

// stop all all other routes
Route::any('{path}', function() {
    $response = [
        'message' => 'Resource not found',
    ];

    return response()->json($response)
        ->setStatusCode(404);
});