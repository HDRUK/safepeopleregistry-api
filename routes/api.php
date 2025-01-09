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
use App\Http\Controllers\Api\V1\CustodianController;
use App\Http\Controllers\Api\V1\CustodianUserController;
use App\Http\Controllers\Api\V1\ONSSubmissionController;
use App\Http\Controllers\Api\V1\OrganisationController;
use App\Http\Controllers\Api\V1\OrganisationDelegatesController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\QueryController;
use App\Http\Controllers\Api\V1\RegistryController;
use App\Http\Controllers\Api\V1\SystemConfigController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\TriggerEmailController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ResolutionController;
use App\Http\Controllers\Api\V1\EmploymentController;
use App\Http\Controllers\Api\V1\EducationController;
use App\Http\Controllers\Api\V1\EmailTemplateController;
use App\Http\Controllers\Api\V1\SectorController;
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

Route::post('v1/query', [QueryController::class, 'query']);

Route::middleware('auth:api')->get('/secure-test', [UserController::class, 'index']);

Route::middleware('auth:api')->get('auth/me', [AuthController::class, 'me']);
Route::middleware('auth:api')->post('auth/register', [AuthController::class, 'registerKeycloakUser']);

Route::middleware('api')->get('v1/users', [UserController::class, 'index']);
Route::middleware('api')->get('v1/users/test', [UserController::class, 'fakeEndpointForTesting']);
Route::middleware('api')->get('v1/users/{id}', [UserController::class, 'show']);
Route::middleware('api')->get('v1/users/identifier/{id}', [UserController::class, 'showByUniqueIdentifier']);
Route::middleware('api')->post('v1/users', [UserController::class, 'store']);
Route::middleware('api')->put('v1/users/{id}', [UserController::class, 'update']);
Route::middleware('api')->patch('v1/users/{id}', [UserController::class, 'edit']);
Route::middleware('api')->delete('v1/users/{id}', [UserController::class, 'destroy']);
Route::middleware('api')->post('v1/users/permissions', [PermissionController::class, 'assignUserPermissionsToFrom']);
Route::middleware('api')->post('v1/users/change-password/{userId}', [AuthController::class, 'changePassword']);

Route::middleware('api')->get('v1/training', [TrainingController::class, 'index']);
Route::middleware('api')->get('v1/training/registry/{registryId}', [TrainingController::class, 'indexByRegistryId']);
Route::middleware('api')->get('v1/training/{id}', [TrainingController::class, 'show']);
Route::middleware('api')->post('v1/training', [TrainingController::class, 'store']);
Route::middleware('api')->put('v1/training/{id}', [TrainingController::class, 'update']);
Route::middleware('api')->patch('v1/training/{id}', [TrainingController::class, 'edit']);
Route::middleware('api')->delete('v1/training/{id}', [TrainingController::class, 'destroy']);

Route::middleware('api')->get('v1/custodians', [CustodianController::class, 'index']);
Route::middleware('api')->get('v1/custodians/{id}', [CustodianController::class, 'show']);
Route::middleware('api')->get('v1/custodians/identifier/{id}', [CustodianController::class, 'showByUniqueIdentifier']);
Route::middleware('api')->get('v1/custodians/email/{email}', [CustodianController::class, 'showByEmail']);
Route::middleware('api')->get('v1/custodians/{id}/projects', [CustodianController::class, 'getProjects']);
Route::middleware('api')->post('v1/custodians', [CustodianController::class, 'store']);
Route::middleware('api')->put('v1/custodians/{id}', [CustodianController::class, 'update']);
Route::middleware('api')->patch('v1/custodians/{id}', [CustodianController::class, 'edit']);
Route::middleware('api')->delete('v1/custodians/{id}', [CustodianController::class, 'destroy']);
Route::middleware(['api', 'check.custodian.access'])->post('v1/custodians/push', [CustodianController::class, 'push']);

Route::middleware('api')->get('v1/custodian_users', [CustodianUserController::class, 'index']);
Route::middleware('api')->get('v1/custodian_users/{id}', [CustodianUserController::class, 'show']);
Route::middleware('api')->post('v1/custodian_users', [CustodianUserController::class, 'store']);
Route::middleware('api')->put('v1/custodian_users/{id}', [CustodianUserController::class, 'update']);
Route::middleware('api')->patch('v1/custodian_users/{id}', [CustodianUserController::class, 'edit']);
Route::middleware('api')->delete('v1/custodian_users/{id}', [CustodianUserController::class, 'destroy']);

Route::middleware('api')->get('v1/endorsements', [EndorsementController::class, 'index']);
Route::middleware('api')->get('v1/endorsements/{id}', [EndorsementController::class, 'show']);
Route::middleware('api')->post('v1/endorsements', [EndorsementController::class, 'store']);

Route::middleware('api')->get('v1/projects', [ProjectController::class, 'index']);
Route::middleware('api')->get('v1/projects/{id}', [ProjectController::class, 'show']);
Route::middleware('api')->get('v1/projects/user/{registryId}/approved', [ProjectController::class, 'getApprovedProjects']);
Route::middleware('api')->get('v1/projects/{id}/users', [ProjectController::class, 'getProjectUsers']);
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
Route::middleware('api')->get('v1/organisations/{id}/idvt', [OrganisationController::class, 'idvt']);
Route::middleware('api')->get('v1/organisations/{id}/counts/certifications', [OrganisationController::class, 'countCertifications']);
Route::middleware('api')->get('v1/organisations/{id}/counts/users', [OrganisationController::class, 'countUsers']);
Route::middleware('api')->get('v1/organisations/{id}/counts/projects/present', [OrganisationController::class, 'countPresentProjects']);
Route::middleware('api')->get('v1/organisations/{id}/counts/projects/past', [OrganisationController::class, 'countPastProjects']);
Route::middleware('api')->post('v1/organisations', [OrganisationController::class, 'store']);
Route::middleware('api')->put('v1/organisations/{id}', [OrganisationController::class, 'update']);
Route::middleware('api')->patch('v1/organisations/{id}', [OrganisationController::class, 'edit']);
Route::middleware('api')->delete('v1/organisations/{id}', [OrganisationController::class, 'destroy']);
Route::middleware('api')->post('v1/organisations/{id}/invite_user', [OrganisationController::class, 'inviteUser']);
Route::middleware('api')->post('v1/organisations/permissions', [PermissionController::class, 'assignOrganisationPermissionsToFrom']);
Route::middleware('api')->get('v1/organisations/{id}/projects', [OrganisationController::class, 'getProjects']);

Route::middleware('api')->get('v1/organisation_delegates', [OrganisationDelegatesController::class, 'index']);
Route::middleware('api')->get('v1/organisation_delegates/{id}', [OrganisationDelegatesController::class, 'show']);
Route::middleware('api')->post('v1/organisation_delegates', [OrganisationDelegatesController::class, 'store']);
Route::middleware('api')->put('v1/organisation_delegates/{id}', [OrganisationDelegatesController::class, 'update']);
Route::middleware('api')->patch('v1/organisation_delegates/{id}', [OrganisationDelegatesController::class, 'edit']);
Route::middleware('api')->delete('v1/organisation_delegates/{id}', [OrganisationDelegatesController::class, 'destroy']);


Route::middleware('api')->get('v1/organisations/{id}/projects/present', [OrganisationController::class, 'presentProjects']);
Route::middleware('api')->get('v1/organisations/{id}/projects/past', [OrganisationController::class, 'pastProjects']);
Route::middleware('api')->get('v1/organisations/{id}/projects/future', [OrganisationController::class, 'futureProjects']);

Route::middleware('api')->get('v1/accreditations/{registryId}', [AccreditationController::class, 'indexByRegistryId']);
Route::middleware('api')->post('v1/accreditations/{registryId}', [AccreditationController::class, 'storeByRegistryId']);
Route::middleware('api')->put('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'updateByRegistryId']);
Route::middleware('api')->patch('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'editByRegistryId']);
Route::middleware('api')->delete('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'destroyByRegistryId']);

Route::middleware('api')->get('v1/educations/{registryId}', [EducationController::class, 'indexByRegistryId']);
Route::middleware('api')->get('v1/educations/{id}/{registryId}', [EducationController::class, 'showByRegistryId']);
Route::middleware('api')->post('v1/educations/{registryId}', [EducationController::class, 'storeByRegistryId']);
Route::middleware('api')->put('v1/educations/{id}/{registryId}', [EducationController::class, 'updateByRegistryId']);
Route::middleware('api')->patch('v1/educations/{id}/{registryId}', [EducationController::class, 'editByRegistryId']);
Route::middleware('api')->delete('v1/educations/{id}/{registryId}', [EducationController::class, 'destroyByRegistryId']);

Route::middleware('api')->get('v1/employments/{registryId}', [EmploymentController::class, 'indexByRegistryId']);
Route::middleware('api')->get('v1/employments/{id}/{registryId}', [EmploymentController::class, 'showByRegistryId']);
Route::middleware('api')->post('v1/employments/{registryId}', [EmploymentController::class, 'storeByRegistryId']);
Route::middleware('api')->put('v1/employments/{id}/{registryId}', [EmploymentController::class, 'updateByRegistryId']);
Route::middleware('api')->patch('v1/employments/{id}/{registryId}', [EmploymentController::class, 'editByRegistryId']);
Route::middleware('api')->delete('v1/employments/{id}/{registryId}', [EmploymentController::class, 'destroyByRegistryId']);

Route::middleware('api')->get('v1/sectors', [SectorController::class, 'index']);
Route::middleware('api')->get('v1/sectors/{id}', [SectorController::class, 'show']);
Route::middleware('api')->post('v1/sectors', [SectorController::class, 'store']);
Route::middleware('api')->put('v1/sectors/{id}', [SectorController::class, 'update']);
Route::middleware('api')->patch('v1/sectors/{id}', [SectorController::class, 'edit']);
Route::middleware('api')->delete('v1/sectors/{id}', [SectorController::class, 'destroy']);

Route::middleware('api')->get('v1/resolutions/{registryId}', [ResolutionController::class, 'indexByRegistryId']);
Route::middleware('api')->post('v1/resolutions/{registryId}', [ResolutionController::class, 'storeByRegistryId']);

Route::middleware('api')->get('v1/histories', [HistoryController::class, 'index']);
Route::middleware('api')->get('v1/histories/{id}', [HistoryController::class, 'show']);
Route::middleware('api')->post('v1/histories', [HistoryController::class, 'store']);

Route::middleware('api')->get('v1/infringements', [InfringementController::class, 'index']);
Route::middleware('api')->get('v1/infringements/{id}', [InfringementController::class, 'show']);
Route::middleware('api')->post('v1/infringements', [InfringementController::class, 'store']);

Route::middleware('api')->get('v1/permissions', [PermissionController::class, 'index']);

Route::middleware('api')->get('v1/email_templates', [EmailTemplateController::class, 'index']);

Route::middleware('api')->post('v1/trigger_email', [TriggerEmailController::class, 'spawnEmail']);

Route::middleware('api')->post('v1/files', [FileUploadController::class, 'store']);

Route::middleware('api')->post('v1/approvals/{entity_type}', [ApprovalController::class, 'store']);
Route::middleware('api')->get('v1/approvals/{entity_type}/{id}/custodian/{custodian_id}', [ApprovalController::class, 'getEntityHasCustodianApproval']);
Route::middleware('api')->delete('v1/approvals/{entity_type}/{id}/custodian/{custodian_id}', [ApprovalController::class, 'delete']);

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
