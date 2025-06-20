<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\Custodian;
use App\Models\CustodianHasProjectOrganisation;
use App\Models\UserAuditLog;
use Illuminate\Support\Facades\Gate;
use App\Traits\CommonFunctions;

class CustodianHasProjectOrganisationController extends Controller
{
    use Responses;
    use CommonFunctions;


    /**
     * @OA\Get(
     *      path="/api/v1/custodian_approvals/{custodianId}/projectOrganisations",
     *      operationId="indexCustodianProjectOrganisations",
     *      tags={"Custodian Project Organisations"},
     *      summary="List all project organisations associated with a custodian",
     *      description="Returns a list of all custodian project organisation approvals for a specific custodian",
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
     *                  @OA\Items(ref="#/components/schemas/CustodianHasProjectOrganisation")
     *              )
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
    public function index(Request $request, int $custodianId)
    {
        try {
            $custodian = Custodian::findOrFail($custodianId);

            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $searchName = $request->input('name');
            $perPage = $request->integer('per_page', (int)$this->getSystemConfig('PER_PAGE'));

            $projectId = $request->input('project_id');

            $records = CustodianHasProjectOrganisation::with([
                'modelState.state',
                'projectOrganisation.organisation.sroOfficer',
                'projectOrganisation.project',
            ])
                ->where('custodian_id', $custodianId)
                ->when(!empty($searchName), function ($query) use ($searchName) {
                    $query->where(function ($subQuery) use ($searchName) {
                        $subQuery->orWhereHas('projectOrganisation.organisation', function ($q) use ($searchName) {
                            /** @phpstan-ignore-next-line */
                            $q->searchViaRequest(['name' => $searchName]);
                        });
                    });
                })
                ->when(!empty($projectId), function ($query) use ($projectId) {
                    $query->whereHas('projectOrganisation.project', function ($q) use ($projectId) {
                        $q->where('id', $projectId);
                    });
                })
                ->paginate($perPage);

            return $this->OKResponse($records);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodian_approvals/{custodianId}/projectOrganisations/{projectOrganisationId}",
     *      operationId="showCustodianProjectOrganisation",
     *      tags={"Custodian Project Organisations"},
     *      summary="Get custodian approval for a project organisation",
     *      description="Returns custodian approval details for a specific project organisation",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="custodianId",
     *          in="path",
     *          description="ID of the custodian",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectOrganisationId",
     *          in="path",
     *          description="ID of the project organisation",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", ref="#/components/schemas/CustodianHasProjectOrganisation")
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
        Request $request,
        int $custodianId,
        int $projectOrganisationId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $puhca = CustodianHasProjectOrganisation::with([
                'modelState.state',
                'projectOrganisation.organisation'
            ])
                ->where([
                    'project_has_organisation_id' => $projectOrganisationId,
                    'custodian_id' => $custodianId
                ])->first();

            return $this->OKResponse($puhca);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/custodian_approvals/{custodianId}/projectOrganisations/{projectOrganisationId}",
     *      operationId="updateCustodianProjectOrganisation",
     *      tags={"Custodian Project Organisations"},
     *      summary="Update custodian approval for a project organisation",
     *      description="Updates approval status and/or comment for a project organisation",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="custodianId",
     *          in="path",
     *          description="ID of the custodian",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectOrganisationId",
     *          in="path",
     *          description="ID of the project organisation",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="approved", type="boolean", example=true, description="Approval status"),
     *              @OA\Property(property="comment", type="string", example="Updated comment", description="Optional comment"),
     *              @OA\Property(property="status", type="string", example="approved", description="Workflow state")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", ref="#/components/schemas/CustodianHasProjectOrganisation")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="cannot transition to state = [status]")
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
    public function update(
        Request $request,
        int $custodianId,
        int $projectOrganisationId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('update', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $cho = CustodianHasProjectOrganisation::where([
                'project_has_organisation_id' => $projectOrganisationId,
                'custodian_id' => $custodianId,
            ])->first();
            if (!$cho) {
                return $this->NotFoundResponse();
            }

            $status = $request->get('status');

            if (isset($status)) {
                $originalStatus = $cho->getState();
                if ($cho->canTransitionTo($status)) {
                    $cho->transitionTo($status);
                } else {
                    return $this->ErrorResponse('cannot transition to state = ' . $status);
                }

                $comment = $request->get('comment');
                if (isset($comment)) {
                    $pho = $cho->projectOrganisation;
                    $organisation = $pho->organisation;
                    $project = $pho->project;
                    $organisationId = $organisation->id;
                    $orgName = $organisation->organisation_name;

                    // find all project users that have this affiliation
                    $filteredProjectUsers = $project->projectUsers()
                        ->whereHas('affiliation', function ($query) use ($organisationId) {
                            $query->where('organisation_id', $organisationId);
                        })
                        ->with('registry.user')
                        ->get();

                    // find their User ID
                    $userIds = $filteredProjectUsers
                        ->map(function ($projectUser) {
                            return optional($projectUser->registry->user)->id;
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    foreach ($userIds as $userId) {
                        $details = [
                            'organisation_name' => $orgName,
                            'original_status' => $originalStatus,
                            'new_status' => $status,
                            'comment' => $comment
                        ];

                        UserAuditLog::create([
                            'user_id' => $userId,
                            'entity'   => CustodianHasProjectOrganisation::class,
                            'entity_id' =>  $cho->id,
                            'details' => $details,
                        ]);
                    }
                };
            }

            return $this->OKResponse($cho);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodian_approvals/projectOrganisations/getWorkflowStates",
     *      operationId="getProjectOrganisationWorkflowStates",
     *      tags={"Custodian Project Organisations"},
     *      summary="Get all workflow states for custodian project organisation approvals",
     *      description="Returns a list of all possible workflow states",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(type="string", example="pending")
     *              )
     *          )
     *      )
     * )
     */
    public function getWorkflowStates(Request $request)
    {
        $model = new CustodianHasProjectOrganisation();
        return $this->OKResponse($model->getAllStates());
    }
}
