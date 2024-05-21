<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\QueryController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\IssuerController;
use App\Http\Controllers\Api\V1\EndorsementController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\RegistryController;
use App\Http\Controllers\Api\V1\ExperienceController;
use App\Http\Controllers\Api\V1\HistoryController;
use App\Http\Controllers\Api\V1\IdentityController;
use App\Http\Controllers\Api\V1\OrganisationController;
use App\Http\Controllers\Api\V1\InfringementController;
use App\Http\Controllers\Api\V1\TriggerEmailController;

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
Route::get('auth/redirect', [AuthController::class, 'loginKeycloak']);
Route::get('auth/callback', [AuthController::class, 'loginKeycloakCallback']);
Route::get('auth/me', [AuthController::class, 'me']);

Route::post('v1/query', [QueryController::class, 'query']);

Route::middleware('auth:api')->get('/secure-test',  [UserController::class, 'index']);

Route::middleware('api')->get('v1/users', [UserController::class, 'index']);
Route::middleware('api')->get('v1/users/{id}', [UserController::class, 'show']);
Route::middleware('api')->post('v1/users', [UserController::class, 'store']);
Route::middleware('api')->put('v1/users/{id}', [UserController::class, 'update']);
Route::middleware('api')->patch('v1/users/{id}', [UserController::class, 'edit']);
Route::middleware('api')->delete('v1/users/{id}', [UserController::class, 'destroy']);

Route::middleware('api')->get('v1/training', [TrainingController::class, 'index']);
Route::middleware('api')->get('v1/training/{id}', [TrainingController::class, 'show']);
Route::middleware('api')->post('v1/training', [TrainingController::class, 'store']);
Route::middleware('api')->put('v1/training/{id}', [TrainingController::class, 'update']);
Route::middleware('api')->patch('v1/training/{id}', [TrainingController::class, 'edit']);
Route::middleware('api')->delete('v1/training/{id}', [TrainingController::class, 'destroy']);

Route::middleware('api')->get('v1/issuers', [IssuerController::class, 'index']);
Route::middleware('api')->get('v1/issuers/{id}', [IssuerController::class, 'show']);
Route::middleware('api')->get('v1/issuers/identifier/{id}', [IssuerController::class, 'showByUniqueIdentifier']);
Route::middleware('api')->post('v1/issuers', [IssuerController::class, 'store']);
Route::middleware('api')->put('v1/issuers/{id}', [IssuerController::class, 'update']);
Route::middleware('api')->patch('v1/issuers/{id}', [IssuerController::class, 'edit']);
Route::middleware('api')->delete('v1/issuers/{id}', [IssuerController::class, 'destroy']);
Route::middleware(['api', 'check.issuer.access'])->post('v1/issuers/push', [IssuerController::class, 'push']);

Route::middleware('api')->get('v1/endorsements', [EndorsementController::class, 'index']);
Route::middleware('api')->get('v1/endorsements/{id}', [EndorsementController::class, 'show']);
Route::middleware('api')->post('v1/endorsements', [EndorsementController::class, 'store']);

Route::middleware('api')->get('v1/projects', [ProjectController::class, 'index']);
Route::middleware('api')->get('v1/projects/{id}', [ProjectController::class, 'show']);
Route::middleware('api')->post('v1/projects', [ProjectController::class, 'store']);
Route::middleware('api')->put('v1/projects/{id}', [ProjectController::class, 'update']);
Route::middleware('api')->patch('v1/projects/{id}', [ProjectController::class, 'edit']);
Route::middleware('api')->delete('v1/projects/{id}', [ProjectController::class, 'destroy']);

Route::middleware('api')->get('v1/registries', [RegistryController::class, 'index']);
Route::middleware('api')->get('v1/registries/{id}', [RegistryController::class, 'show']);
Route::middleware('api')->post('v1/registries', [RegistryController::class, 'store']);
Route::middleware('api')->put('v1/registries/{id}', [RegistryController::class, 'update']);
Route::middleware('api')->patch('v1/registries/{id}', [RegistryController::class, 'edit']);
Route::middleware('api')->delete('v1/registries/{id}', [RegistryController::class, 'destroy']);

Route::middleware('api')->get('v1/experiences', [ExperienceController::class, 'index']);
Route::middleware('api')->get('v1/experiences/{id}', [ExperienceController::class, 'show']);
Route::middleware('api')->post('v1/experiences', [ExperienceController::class, 'store']);
Route::middleware('api')->put('v1/experiences/{id}', [ExperienceController::class, 'update']);
Route::middleware('api')->patch('v1/experiences/{id}', [ExperienceController::class, 'edit']);
Route::middleware('api')->delete('v1/experiences/{id}', [ExperienceController::class, 'destroy']);

Route::middleware('api')->get('v1/identities', [IdentityController::class, 'index']);
Route::middleware('api')->get('v1/identities/{id}', [IdentityController::class, 'show']);
Route::middleware('api')->post('v1/identities', [IdentityController::class, 'store']);
Route::middleware('api')->put('v1/identities/{id}', [IdentityController::class, 'update']);
Route::middleware('api')->patch('v1/identities/{id}', [IdentityController::class, 'edit']);
Route::middleware('api')->delete('v1/identities/{id}', [IdentityController::class, 'destroy']);

Route::middleware('api')->get('v1/organisations', [OrganisationController::class, 'index']);
Route::middleware('api')->get('v1/organisations/{id}', [OrganisationController::class, 'show']);
Route::middleware('api')->post('v1/organisations', [OrganisationController::class, 'store']);
Route::middleware('api')->put('v1/organisations/{id}', [OrganisationController::class, 'update']);
Route::middleware('api')->patch('v1/organisations/{id}', [OrganisationController::class, 'edit']);
Route::middleware('api')->delete('v1/organisations/{id}', [OrganisationController::class, 'destroy']);

Route::middleware('api')->get('v1/histories', [HistoryController::class, 'index']);
Route::middleware('api')->get('v1/histories/{id}', [HistoryController::class, 'show']);
Route::middleware('api')->post('v1/histories', [HistoryController::class, 'store']);

Route::middleware('api')->get('v1/infringements', [InfringementController::class, 'index']);
Route::middleware('api')->get('v1/infringements/{id}', [InfringementController::class, 'show']);
Route::middleware('api')->post('v1/infringements', [InfringementController::class, 'store']);

Route::middleware('api')->post('v1/trigger_email', [TriggerEmailController::class, 'spawnEmail']);

// stop all all other routes
Route::any('{path}', function() {
    $response = [
        'message' => 'Resource not found',
    ];

    return response()->json($response)
        ->setStatusCode(404);
});