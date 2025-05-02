<?php

use App\Http\Controllers\Api\V1\AccreditationController;
use App\Http\Controllers\Api\V1\AffiliationController;
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
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\QueryController;
use App\Http\Controllers\Api\V1\RegistryController;
use App\Http\Controllers\Api\V1\RegistryReadRequestController;
use App\Http\Controllers\Api\V1\SystemConfigController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\TriggerEmailController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ResolutionController;
use App\Http\Controllers\Api\V1\EducationController;
use App\Http\Controllers\Api\V1\EmailTemplateController;
use App\Http\Controllers\Api\V1\SectorController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ActionLogController;
use App\Http\Controllers\Api\V1\ValidationLogController;
use App\Http\Controllers\Api\V1\ValidationLogCommentController;
use App\Http\Controllers\Api\V1\ProfessionalRegistrationController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\CustodianModelConfigController;
use App\Http\Controllers\Api\V1\ProjectDetailController;
use App\Http\Controllers\Api\V1\ProjectRoleController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

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

Route::middleware(['check.custodian.access', 'verify.signed.payload'])->post('v1/query', [QueryController::class, 'query']);

Route::middleware('api')->get('auth/me', [AuthController::class, 'me']);
Route::middleware('api')->post('auth/register', [AuthController::class, 'registerKeycloakUser']);


Route::middleware(['auth:api'])
    ->prefix('v1/users')
    ->group(function () {

        Route::get('/', [UserController::class, 'index']);
        Route::get('/test', [UserController::class, 'fakeEndpointForTesting']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::get('/{id}/history', [UserController::class, 'getHistory']);
        Route::get('/identifier/{id}', [UserController::class, 'showByUniqueIdentifier']);
        Route::get('/{id}/projects', [UserController::class, 'userProjects']);

        // create
        Route::post('/', [UserController::class, 'store']);
        Route::post('/change-password/{id}', [AuthController::class, 'changePassword']);
        Route::post('/invite', [UserController::class, 'invite']);
        Route::post('/permissions', [PermissionController::class, 'assignUserPermissionsToFrom']);
        Route::post('/search_affiliations', [UserController::class, 'searchUsersByNameAndProfessionalEmail']);


        //update
        Route::put('/{id}', [UserController::class, 'update']);
        Route::patch('/{id}', [UserController::class, 'edit']);
        Route::delete('/{id}', [UserController::class, 'destroy']);


        // Notifications
        Route::get('/{id}/notifications', [NotificationController::class, 'getUserNotifications']);
        Route::get('/{id}/notifications/count', [NotificationController::class, 'getNotificationCounts']);
        Route::patch('/{id}/notifications/read', [NotificationController::class, 'markUserNotificationsAsRead']);
        Route::patch('/{id}/notifications/{notificationId}/read', [NotificationController::class, 'markUserNotificationAsRead']);
        Route::patch('/{id}/notifications/{notificationId}/unread', [NotificationController::class, 'markUserNotificationAsUnread']);
    });

// probably redundant...
Route::middleware(['check.custodian.access', 'verify.signed.payload'])
    ->post('v1/users/validate', [UserController::class, 'validateUserRequest']);




Route::middleware('auth:api')->get('v1/{entity}/{id}/action_log', [ActionLogController::class, 'getEntityActionLog']);
Route::middleware('auth:api')->put('v1/action_log/{id}', [ActionLogController::class, 'update']);

Route::middleware('auth:api')->get(
    'v1/custodians/{custodianId}/projects/{projectId}/registries/{registryId}/validation_logs',
    [ValidationLogController::class, 'getCustodianProjectUserValidationLogs']
);
Route::middleware('auth:api')->get(
    'v1/custodians/{custodianId}/organisations/{organisationId}/validation_logs',
    [ValidationLogController::class, 'getCustodianOrganisationValidationLogs']
);
Route::middleware('auth:api')->put(
    'v1/custodians/{custodianId}/validation_logs',
    [ValidationLogController::class, 'updateCustodianValidationLogs']
);


Route::middleware('auth:api')->get('v1/validation_logs/{id}', [ValidationLogController::class, 'index']);
Route::middleware('auth:api')->get('v1/validation_logs/{id}/comments', [ValidationLogController::class, 'comments']);
Route::middleware('auth:api')->put('v1/validation_logs/{id}', [ValidationLogController::class, 'update']);


Route::middleware('auth:api')->get('v1/validation_log_comments/{id}', [ValidationLogCommentController::class, 'show']);
Route::middleware('auth:api')->post('v1/validation_log_comments', [ValidationLogCommentController::class, 'store']);
Route::middleware('auth:api')->put('v1/validation_log_comments/{id}', [ValidationLogCommentController::class, 'update']);
Route::middleware('auth:api')->delete('v1/validation_log_comments/{id}', [ValidationLogCommentController::class, 'destroy']);


Route::middleware('auth:api')
    ->prefix('v1/training')
    ->controller(TrainingController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/registry/{registryId}', 'indexByRegistryId');

        Route::post('/', 'store');
        Route::post('/{trainingId}/link_file/{fileId}', 'linkTrainingFile');

        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

Route::middleware(['auth:api'])
    ->prefix('v1/custodians')
    ->controller(CustodianController::class)
    ->group(function () {
        // Read
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/identifier/{id}', 'showByUniqueIdentifier');
        Route::get('/{id}/projects', 'getProjects');
        Route::get('/{id}/users/{userId}/projects', 'getUserProjects');
        Route::get('/{id}/organisations', 'getOrganisations');
        Route::get('/{id}/custodian_users', 'getCustodianUsers');
        Route::get('/{id}/projects_users', 'getProjectsUsers');
        Route::get('/{id}/rules', 'getRules');
        Route::get('/{id}/users', 'usersWithCustodianApprovals');
        Route::get('/{id}/organisations/{organisationId}/users', 'getOrganisationUsers');

        // Write
        Route::post('/', 'store');
        Route::post('/push', 'push');
        Route::post('/{id}/invite', 'invite');
        Route::post('/{id}/projects', 'addProject');

        // Update
        Route::put('/{id}', 'update');
        Route::patch('/{id}', 'edit');
        Route::patch('/{id}/rules', 'updateCustodianRules');

        // Delete
        Route::delete('/{id}', 'destroy');
    });



Route::middleware('auth:api')
    ->prefix('v1/custodian_users')
    ->controller(CustodianUserController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::patch('{id}', 'edit');
        Route::delete('{id}', 'destroy');
        Route::post('invite/{id}', 'invite');
    });

// Departments
Route::middleware('auth:api')
    ->prefix('v1/departments')
    ->controller(DepartmentController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::patch('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

// Endorsements
Route::middleware('auth:api')
    ->prefix('v1/endorsements')
    ->controller(EndorsementController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
    });

// Projects
Route::middleware('auth:api')
    ->prefix('v1/projects')
    ->controller(ProjectController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::patch('{id}', 'edit');
        Route::delete('{id}', 'destroy');

        // Project user management
        Route::get('user/{registryId}/approved', 'getApprovedProjects');
        Route::get('{id}/users', 'getProjectUsers');
        Route::get('{id}/all_users', 'getAllUsersFlagProject');
        Route::put('{id}/all_users', 'updateAllProjectUsers');
        Route::post('{id}/users', 'addProjectUser');
        Route::put('{projectId}/users/{registryId}', 'updateProjectUser');
        Route::delete('{projectId}/users/{registryId}', 'deleteUserFromProject');
        Route::put('{projectId}/users/{registryId}/primary_contact', 'makePrimaryContact');
    });

Route::middleware('auth:api')
    ->prefix('v1/registries')
    ->controller(RegistryController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::patch('{id}', 'edit');
        Route::delete('{id}', 'destroy');
    });


Route::middleware('auth:api')
    ->prefix('v1/experiences')
    ->controller(ExperienceController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::patch('{id}', 'edit');
        Route::delete('{id}', 'destroy');
    });


Route::middleware('auth:api')
    ->prefix('v1/identities')
    ->controller(IdentityController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::patch('{id}', 'edit');
        Route::delete('{id}', 'destroy');
    });

Route::middleware('auth:api')
    ->prefix('v1/organisations')
    ->controller(OrganisationController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/{id}/idvt', 'idvt');
        Route::get('/{id}/counts/certifications', 'countCertifications');
        Route::get('/{id}/counts/users', 'countUsers');
        Route::get('/{id}/counts/projects/present', 'countPresentProjects');
        Route::get('/{id}/counts/projects/past', 'countPastProjects');
        Route::get('/{id}/projects/present', 'presentProjects');
        Route::get('/{id}/projects/past', 'pastProjects');
        Route::get('/{id}/projects/future', 'futureProjects');
        Route::get('/{id}/projects', 'getProjects');
        Route::get('/{id}/users', 'getUsers');
        Route::get('/{id}/delegates', 'getDelegates');
        Route::get('/{id}/registries', 'getRegistries');
        Route::get('/ror/{ror}', 'validateRor');

        Route::post('/', 'store');
        Route::post('/unclaimed', 'storeUnclaimed');

        // Update
        Route::put('/{id}', 'update');
        Route::patch('/{id}', 'edit');

        // Delete
        Route::delete('/{id}', 'destroy');
    });


// 🟡 Write actions (invites and permissions) — same role access
Route::middleware(['auth:api'])
    ->prefix('v1/organisations')
    ->group(function () {
        Route::controller(OrganisationController::class)->group(function () {
            Route::post('/{id}/invite', 'invite');
            Route::post('/{id}/invite_user', 'inviteUser');
        });

        Route::controller(PermissionController::class)->group(function () {
            Route::post('/permissions', 'assignOrganisationPermissionsToFrom');
        });
    });

Route::middleware('auth:api')->get('v1/accreditations/{registryId}', [AccreditationController::class, 'indexByRegistryId']);
Route::middleware('auth:api')->post('v1/accreditations/{registryId}', [AccreditationController::class, 'storeByRegistryId']);
Route::middleware('auth:api')->put('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'updateByRegistryId']);
Route::middleware('auth:api')->patch('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'editByRegistryId']);
Route::middleware('auth:api')->delete('v1/accreditations/{id}/{registryId}', [AccreditationController::class, 'destroyByRegistryId']);

Route::middleware('auth:api')->get('v1/affiliations/{registryId}', [AffiliationController::class, 'indexByRegistryId']);
Route::middleware('auth:api')->post('v1/affiliations/{registryId}', [AffiliationController::class, 'storeByRegistryId']);
Route::middleware('auth:api')->put('v1/affiliations/{id}', [AffiliationController::class, 'update']);
Route::middleware('auth:api')->patch('v1/affiliations/{id}', [AffiliationController::class, 'edit']);
Route::middleware('auth:api')->delete('v1/affiliations/{id}', [AffiliationController::class, 'destroy']);
Route::middleware('auth:api')->put('v1/affiliations/{registryId}/affiliation/{id}', [AffiliationController::class, 'updateRegistryAffiliation']);


Route::middleware('auth:api')->get('v1/professional_registrations/registry/{registryId}', [ProfessionalRegistrationController::class, 'indexByRegistryId']);
Route::middleware('auth:api')->post('v1/professional_registrations/registry/{registryId}', [ProfessionalRegistrationController::class, 'storeByRegistryId']);
Route::middleware('auth:api')->put('v1/professional_registrations/{id}', [ProfessionalRegistrationController::class, 'update']);
Route::middleware('auth:api')->patch('v1/professional_registrations/{id}', [ProfessionalRegistrationController::class, 'edit']);
Route::middleware('auth:api')->delete('v1/professional_registrations/{id}', [ProfessionalRegistrationController::class, 'destroy']);

Route::middleware('auth:api')->get('v1/educations/{registryId}', [EducationController::class, 'indexByRegistryId']);
Route::middleware('auth:api')->get('v1/educations/{id}/{registryId}', [EducationController::class, 'showByRegistryId']);
Route::middleware('auth:api')->post('v1/educations/{registryId}', [EducationController::class, 'storeByRegistryId']);
Route::middleware('auth:api')->put('v1/educations/{id}/{registryId}', [EducationController::class, 'updateByRegistryId']);
Route::middleware('auth:api')->patch('v1/educations/{id}/{registryId}', [EducationController::class, 'editByRegistryId']);
Route::middleware('auth:api')->delete('v1/educations/{id}/{registryId}', [EducationController::class, 'destroyByRegistryId']);

Route::middleware('auth:api')->get('v1/sectors', [SectorController::class, 'index']);
Route::middleware('auth:api')->get('v1/sectors/{id}', [SectorController::class, 'show']);
Route::middleware('auth:api')->post('v1/sectors', [SectorController::class, 'store']);
Route::middleware('auth:api')->put('v1/sectors/{id}', [SectorController::class, 'update']);
Route::middleware('auth:api')->patch('v1/sectors/{id}', [SectorController::class, 'edit']);
Route::middleware('auth:api')->delete('v1/sectors/{id}', [SectorController::class, 'destroy']);

Route::middleware('auth:api')->get('v1/resolutions/{registryId}', [ResolutionController::class, 'indexByRegistryId']);
Route::middleware('auth:api')->post('v1/resolutions/{registryId}', [ResolutionController::class, 'storeByRegistryId']);

Route::middleware('auth:api')->get('v1/histories', [HistoryController::class, 'index']);
Route::middleware('auth:api')->get('v1/histories/{id}', [HistoryController::class, 'show']);
Route::middleware('auth:api')->post('v1/histories', [HistoryController::class, 'store']);

Route::middleware('auth:api')->get('v1/infringements', [InfringementController::class, 'index']);
Route::middleware('auth:api')->get('v1/infringements/{id}', [InfringementController::class, 'show']);
Route::middleware('auth:api')->post('v1/infringements', [InfringementController::class, 'store']);

Route::middleware('auth:api')->get('v1/permissions', [PermissionController::class, 'index']);

Route::middleware('auth:api')->get('v1/email_templates', [EmailTemplateController::class, 'index']);

Route::middleware('auth:api')->post('v1/trigger_email', [TriggerEmailController::class, 'spawnEmail']);

Route::middleware('auth:api')->post('v1/files', [FileUploadController::class, 'store']);
Route::middleware('auth:api')->get('v1/files/{id}', [FileUploadController::class, 'show']);
Route::middleware('auth:api')->get('v1/files/{id}/download', [FileUploadController::class, 'download']);


Route::middleware('auth:api')->post('v1/approvals/{entity_type}', [ApprovalController::class, 'store']);
Route::middleware('auth:api')->get('v1/approvals/{entity_type}/{id}/custodian/{custodian_id}', [ApprovalController::class, 'getEntityHasCustodianApproval']);
Route::middleware('auth:api')->delete('v1/approvals/{entity_type}/{id}/custodian/{custodian_id}', [ApprovalController::class, 'delete']);

Route::middleware(['check.custodian.access', 'verify.signed.payload'])->post('v1/request_access', [RegistryReadRequestController::class, 'request']);
Route::middleware('auth:api')->patch('v1/request_access/{id}', [RegistryReadRequestController::class, 'acceptOrReject']);

Route::middleware('auth:api')->get('v1/webhooks/receivers', [WebhookController::class, 'getAllReceivers']);
Route::middleware('auth:api')->get('v1/webhooks/receivers/{custodianId}', [WebhookController::class, 'getReceiversByCustodian']);
Route::middleware('auth:api')->post('v1/webhooks/receivers', [WebhookController::class, 'createReceiver']);
Route::middleware('auth:api')->put('v1/webhooks/receivers/{custodianId}', [WebhookController::class, 'updateReceiver']);
Route::middleware('auth:api')->delete('v1/webhooks/receivers/{custodianId}', [WebhookController::class, 'deleteReceiver']);
Route::middleware('auth:api')->get('v1/webhooks/event-triggers', [WebhookController::class, 'getAllEventTriggers']);

Route::middleware('auth:api')->put('v1/custodian_config/update-active/{id}', [CustodianModelConfigController::class, 'updateCustodianModelConfigsActive']);
Route::middleware('auth:api')->post('v1/custodian_config', [CustodianModelConfigController::class, 'store']);
Route::middleware('auth:api')->get('v1/custodian_config/{id}', [CustodianModelConfigController::class, 'getByCustodianID']);
Route::middleware('auth:api')->put('v1/custodian_config/{id}', [CustodianModelConfigController::class, 'update']);
Route::middleware('auth:api')->delete('v1/custodian_config/{id}', [CustodianModelConfigController::class, 'destroy']);
Route::middleware('auth:api')->get('v1/custodian_config/{id}/entity_models', [CustodianModelConfigController::class, 'getEntityModels']);

Route::middleware('auth:api')->get('v1/project_details', [ProjectDetailController::class, 'index']);
Route::middleware('auth:api')->get('v1/project_details/{id}', [ProjectDetailController::class, 'show']);
Route::middleware('auth:api')->post('v1/project_details', [ProjectDetailController::class, 'store']);
Route::middleware('auth:api')->put('v1/project_details/{id}', [ProjectDetailController::class, 'update']);
Route::middleware('auth:api')->delete('v1/project_details/{id}', [ProjectDetailController::class, 'destroy']);
Route::middleware('auth:api')->post('v1/project_details/query_gateway_dur', [ProjectDetailController::class, 'queryGatewayDurByProjectID']);

Route::middleware('auth:api')->get('v1/project_roles', [ProjectRoleController::class, 'index']);
Route::middleware('auth:api')->get('v1/project_roles/{id}', [ProjectRoleController::class, 'show']);
Route::middleware('auth:api')->post('v1/project_roles', [ProjectRoleController::class, 'store']);
Route::middleware('auth:api')->put('v1/project_roles/{id}', [ProjectRoleController::class, 'update']);

Route::middleware('auth:api')->get('v1/system_config', [SystemConfigController::class, 'index']);
Route::middleware('auth:api')->post('v1/system_config', [SystemConfigController::class, 'store']);
Route::middleware('auth:api')->get('v1/system_config/{name}', [SystemConfigController::class, 'getByName']);

Route::middleware('auth:api')->get('v1/rules', [RulesEngineManagementController::class, 'getRules']);

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
