<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\User;
use App\Models\State;
use App\Models\Project;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\Affiliation;
use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\ProjectHasUser;
use App\Traits\CommonFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectUser\CustodianChangeStatus;
use App\Http\Requests\CustodianHasProjectUser\GetCustodianHasProjectUser;
use App\Http\Requests\CustodianHasProjectUser\GetAllCustodianHasProjectUser;
use App\Http\Requests\CustodianHasProjectUser\UpdateCustodianHasProjectUser;

class CustodianHasProjectUserController extends Controller
{
    use Responses;
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/custodian_approvals/{custodianId}/projectUsers",
     *      operationId="indexCustodianProjectUsers",
     *      tags={"Custodian Project Users"},
     *      summary="List all project users associated with a custodian",
     *      description="Returns a list of all custodian project user approvals for a specific custodian",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="custodianId",
     *          in="path",
     *          description="ID of the custodian",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/CustodianHasProjectUser")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Forbidden")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Custodian Not Found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Custodian not found")
     *          )
     *      )
     * )
     */
    public function index(GetAllCustodianHasProjectUser $request, int $custodianId)
    {
        try {
            $custodian = Custodian::findOrFail($custodianId);

            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $searchName = $request->input('name');
            $perPage = $request->integer('per_page', (int)$this->getSystemConfig('PER_PAGE'));

            $projectId = $request->input('project_id');

            // candidate for eloquent optimisation by switching to raw SQL
            $records = CustodianHasProjectUser::query()
                ->where('custodian_id', $custodianId)
                ->with([
                    'modelState.state',
                    'projectHasUser' => function ($query) {
                        $query->with([
                            'registry.user:id,registry_id,first_name,last_name,email',
                            'project:id,title',
                            'role:id,name',
                            'affiliation:id,organisation_id',
                            'affiliation.modelState.state',
                            'affiliation.organisation:id,organisation_name'
                        ]);
                    },
                ])
                ->withProjectJoins()
                ->when($request->filled('name'), function ($query) use ($searchName) {
                    $query->where(function ($subQuery) use ($searchName) {
                        $subQuery->whereHas('projectHasUser.project', function ($q) use ($searchName) {
                            /** @phpstan-ignore-next-line */
                            $q->searchViaRequest(['title' => $searchName]);
                        });

                        $subQuery->orWhereHas('projectHasUser.registry.user', function ($q) use ($searchName) {
                            /** @phpstan-ignore-next-line */
                            $q->searchViaRequest(['name' => $searchName]);
                        });

                        $subQuery->orWhereHas('projectHasUser.affiliation.organisation', function ($q) use ($searchName) {
                            /** @phpstan-ignore-next-line */
                            $q->searchViaRequest(['organisation_name' => $searchName]);
                        });
                    });
                })
                ->when($request->filled('project_id'), function ($query) use ($projectId) {
                    $query->whereHas('projectHasUser.project', function ($q) use ($projectId) {
                        $q->where('id', $projectId);
                    });
                })
                ->filterByState()
                ->applySorting()
                ->select('custodian_has_project_has_user.*')
                ->paginate($perPage);

            return $this->OKResponse($records);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodian_approvals/{custodianId}/projectUsers/{projectUserId}",
     *      operationId="showCustodianProjectUser",
     *      tags={"Custodian Project Users"},
     *      summary="Get custodian approval for a project user",
     *      description="Returns custodian approval details for a specific project user",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="custodianId",
     *          in="path",
     *          description="ID of the custodian",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectUserId",
     *          in="path",
     *          description="ID of the project user",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", ref="#/components/schemas/CustodianHasProjectUser")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Forbidden")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Not found")
     *          )
     *      )
     * )
     */
    public function show(
        GetCustodianHasProjectUser $request,
        int $custodianId,
        int $projectUserId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $puhca = CustodianHasProjectUser::query()
                ->where([
                    'project_has_user_id' => $projectUserId,
                    'custodian_id' => $custodianId
                ])
                ->with([
                    'modelState.state',
                    'projectHasUser' => function ($query) {
                        $query->with([
                            'registry.user',
                            'project:id,title',
                            'role:id,name',
                            'affiliation:id,organisation_id,email',
                            'affiliation.modelState.state',
                            'affiliation.organisation:id,organisation_name'
                        ]);
                    },
                ])
                ->first();

            return $this->OKResponse($puhca);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/custodian_approvals/{custodianId}/projectUsers/{projectUserId}",
     *      operationId="updateCustodianProjectUser",
     *      tags={"Custodian Project Users"},
     *      summary="Update custodian approval for a project user",
     *      description="Updates approval status and/or comment for a project user",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="custodianId",
     *          in="path",
     *          description="ID of the custodian",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectUserId",
     *          in="path",
     *          description="ID of the project user",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="approved", type="boolean", example=true, description="Approval status"),
     *              @OA\Property(property="comment", type="string", example="Updated approval comment", description="Optional comment"),
     *              @OA\Property(property="status", type="string", example="approved", description="State machine status")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", ref="#/components/schemas/CustodianHasProjectUser")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Forbidden")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="cannot transition to state = [status]")
     *          )
     *      )
     * )
     */

    public function update(
        UpdateCustodianHasProjectUser $request,
        int $custodianId,
        int $projectUserId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('update', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $phuca = CustodianHasProjectUser::where([
                'project_has_user_id' => $projectUserId,
                'custodian_id' => $custodianId,
            ])->first();

            $status = $request->get('status');

            if (isset($status)) {
                $originalStatus = $phuca->getState();
                if ($phuca->canTransitionTo($status)) {
                    $phuca->transitionTo($status);

                    $this->sendNotifications($custodianId, $projectUserId, $status, $originalStatus);
                } else {
                    return $this->ErrorResponse('cannot transition to state = ' . $status);
                }

                $comment = $request->get('comment');
                if (isset($comment)) {
                    $phu = $phuca->projectHasUser;
                    $user = $phu->registry->user;
                    $project = $phu->project;
                    $projectId = $project->id;
                    $projectName = $project->title;

                    activity()
                        ->causedBy(Auth::user())
                        ->performedOn($user)
                        ->withProperties([
                            'custodian_id' => $custodianId,
                            'project_id' => $projectId,
                            'project_name' => $projectName,
                            'original_status' => $originalStatus,
                            'new_status' => $status,
                        ])
                        ->event('status_changed')
                        ->useLog('custodian_project_validation_status')
                        ->log($comment);
                };
            }

            return $this->OKResponse($phuca);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    public function getWorkflowStates(Request $request)
    {
        return $this->OKResponse(CustodianHasProjectUser::getAllStates());
    }

    public function getWorkflowTransitions(Request $request)
    {
        return $this->OKResponse(CustodianHasProjectUser::getTransitions());
    }

    public function sendNotifications(int $custodianId, int $projectUserId, ?string $newState, ?string $oldState)
    {
        $projectUser = ProjectHasUser::with([
            'project',
            'registry.user',
            'affiliation',
            'affiliation.organisation'
        ])->findOrFail($projectUserId);

        $project = $projectUser->project;
        $user = $projectUser->registry->user;
        $affiliation = $projectUser->affiliation;
        $organisation = $affiliation?->organisation;
        $userOrganisation = User::where('organisation_id', $affiliation?->organisation_id)->first();
        $userCustodian = User::where('custodian_user_id', $custodianId)->first();

        $details = [
            'custodian_name' => $userCustodian->first_name . ' ' . $userCustodian->last_name,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'project_title' => $project->title,
            'organisation_name' => $organisation?->organisation_name,
            'old_state' => $oldState,
            'new_state' => $newState,
        ];
            
        if ($newState === State::STATE_MORE_USER_INFO_REQ) {
            // user
            Notification::send($user, new CustodianChangeStatus($details, 'user'));

            // organisation
            Notification::send($userOrganisation, new CustodianChangeStatus($details, 'organisation'));

            // custodians
            Notification::send($userCustodian, new CustodianChangeStatus($details, 'custodian'));
        }
    }
}
