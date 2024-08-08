<?php

use App\Http\Controllers\Api\V1\AccreditationController;
use App\Http\Controllers\Api\V1\ApprovalController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EndorsementController;
use App\Http\Controllers\Api\V1\ExperienceController;
use App\Http\Controllers\Api\V1\FileUploadController;
use App\Http\Controllers\Api\V1\HistoryController;
use App\Http\Controllers\Api\V1\IdentityController;
use App\Http\Controllers\Api\V1\InfringementController;
use App\Http\Controllers\Api\V1\IssuerController;
use App\Http\Controllers\Api\V1\IssuerUserController;
use App\Http\Controllers\Api\V1\ONSSubmissionController;
use App\Http\Controllers\Api\V1\OrganisationController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\QueryController;
use App\Http\Controllers\Api\V1\RegistryController;
use App\Http\Controllers\Api\V1\SystemConfigController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\TriggerEmailController;
use App\Http\Controllers\Api\V1\UserController;

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

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register/researcher', [AuthController::class, 'registerUser']);
Route::post('auth/register/issuer', [AuthController::class, 'registerIssuer']);
Route::post('auth/register/organisation', [AuthController::class, 'registerOrganisation']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::get('auth/me', [AuthController::class, 'me']);

Route::post('v1/query', [QueryController::class, 'query']);

Route::middleware('auth:api')->get('/secure-test', [UserController::class, 'index']);

Route::middleware('api')->get('v1/users', [UserController::class, 'index']);
Route::middleware('api')->get('v1/users/{id}', [UserController::class, 'show']);
Route::middleware('api')->get('v1/users/identifier/{id}', [UserController::class, 'showByUniqueIdentifier']);
Route::middleware('api')->post('v1/users', [UserController::class, 'store']);
Route::middleware('api')->put('v1/users/{id}', [UserController::class, 'update']);
Route::middleware('api')->patch('v1/users/{id}', [UserController::class, 'edit']);
Route::middleware('api')->delete('v1/users/{id}', [UserController::class, 'destroy']);
Route::middleware('api')->post('v1/users/permissions', [PermissionController::class, 'assignUserPermissionsToFrom']);

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

Route::middleware('api')->get('v1/issuer_users', [IssuerUserController::class, 'index']);
Route::middleware('api')->get('v1/issuer_users/{id}', [IssuerUserController::class, 'show']);
Route::middleware('api')->post('v1/issuer_users', [IssuerUserController::class, 'store']);
Route::middleware('api')->put('v1/issuer_users/{id}', [IssuerUserController::class, 'update']);
Route::middleware('api')->patch('v1/issuer_users/{id}', [IssuerUserController::class, 'edit']);
Route::middleware('api')->delete('v1/issuer_users/{id}', [IssuerUserController::class, 'destroy']);

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
Route::middleware('api')->post('v1/organisations/permissions', [PermissionController::class, 'assignOrganisationPermissionsToFrom']);

Route::middleware('api')->get('v1/accreditations/{id}', [AccreditationController::class, 'indexByRegistryId']);
Route::middleware('api')->post('v1/accreditations/{registryId}', [AccreditationController::class, 'storeByRegistryId']);
Route::middleware('api')->put('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'updateByRegistryId']);
Route::middleware('api')->patch('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'editByRegistryId']);
Route::middleware('api')->delete('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'deleteByRegistryId']);

Route::middleware('api')->get('v1/histories', [HistoryController::class, 'index']);
Route::middleware('api')->get('v1/histories/{id}', [HistoryController::class, 'show']);
Route::middleware('api')->post('v1/histories', [HistoryController::class, 'store']);

Route::middleware('api')->get('v1/infringements', [InfringementController::class, 'index']);
Route::middleware('api')->get('v1/infringements/{id}', [InfringementController::class, 'show']);
Route::middleware('api')->post('v1/infringements', [InfringementController::class, 'store']);

Route::middleware('api')->get('v1/permissions', [PermissionController::class, 'index']);

Route::middleware('api')->post('v1/trigger_email', [TriggerEmailController::class, 'spawnEmail']);

Route::middleware('api')->post('v1/files', [FileUploadController::class, 'store']);

Route::middleware('api')->post('v1/approvals/{entity_type}', [ApprovalController::class, 'store']);
Route::middleware('api')->delete('v1/approvals/{entity_type}/{id}/issuer/{issuer_id}', [ApprovalController::class, 'delete']);

Route::get('v1/system_config', [SystemConfigController::class, 'index']);
Route::post('v1/system_config', [SystemConfigController::class, 'store']);
Route::get('v1/system_config/{name}', [SystemConfigController::class, 'getByName']);

// ONS CSV RESEARCHER FEED
Route::post('v1/ons_researcher_feed', [ONSSubmissionController::class, 'receiveCSV']);

// stop all all other routes
Route::any('{path}', function () {
    $response = [
        'message' => 'Resource not found',
    ];

    return response()->json($response)
        ->setStatusCode(404);
});
