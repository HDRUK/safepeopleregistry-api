<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasUserCustodianApproval;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\Custodian;
use Illuminate\Support\Facades\Gate;

class ProjectHasUserCustodianApprovalController extends Controller
{
    use Responses;


    /**
     * @OA\Get(
     *      path="/api/v1/custodians/{custodianId}/projecUsers/{projectUserId}",
     *      operationId="getProjectUserCustodianApproval",
     *      tags={"Project Custodian Approval"},
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
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="project_has_user_id", type="integer", example=1),
     *                  @OA\Property(property="custodian_id", type="integer", example=1),
     *                  @OA\Property(property="approved", type="boolean", example=true),
     *                  @OA\Property(property="comment", type="string", example="Approval comment"),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time"),
     *                  @OA\Property(
     *                      property="projectHasUser",
     *                      type="object",
     *                      @OA\Property(
     *                          property="project",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="title", type="string")
     *                      ),
     *                      @OA\Property(
     *                          property="role",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="name", type="string")
     *                      ),
     *                      @OA\Property(
     *                          property="affiliation",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="organisation_id", type="integer"),
     *                          @OA\Property(
     *                              property="organisation",
     *                              type="object",
     *                              @OA\Property(property="id", type="integer"),
     *                              @OA\Property(property="organisation_name", type="string")
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="modelState",
     *                      type="object",
     *                      @OA\Property(property="state", type="object")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Not found")
     *          )
     *      )
     * )
     */
    public function show(
        Request $request,
        int $custodianId,
        int $projectUserId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $puhca = ProjectHasUserCustodianApproval::with([
                'modelState.state',
                'projectHasUser.project:id,title',
                'projectHasUser.role:id,name',
                'projectHasUser.affiliation:id,organisation_id',
                'projectHasUser.affiliation.organisation:id,organisation_name'
            ])
                ->where([
                    'project_has_user_id' => $projectUserId,
                    'custodian_id' => $custodianId
                ])->first();

            return $this->OKResponse($puhca);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/custodians/{custodianId}/projectUsers/{projectUserId}",
     *      operationId="updateProjectUserCustodianApproval",
     *      tags={"Project Custodian Approval"},
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
     *              @OA\Property(property="approved", type="boolean", example=true, description="Approval status"),
     *              @OA\Property(property="comment", type="string", example="Updated approval comment", description="Optional comment"),
     *              @OA\Property(property="status", type="string", example="approved", description="State machine status")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/ProjectHasUserCustodianApproval"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="cannot transition to state = [status]")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Not found")
     *          )
     *      )
     * )
     */
    public function update(
        Request $request,
        int $custodianId,
        int $projectUserId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('update', $custodian)) {
                return $this->ForbiddenResponse();
            }


            $phuca = ProjectHasUserCustodianApproval::where([
                'project_has_user_id' => $projectUserId,
                'custodian_id' => $custodianId,
            ])->first();

            $updateData = $request->only(['approved', 'comment']);

            if (!empty($updateData)) {
                $phuca->update($updateData);
            }
            $status = $request->get('status');

            if (isset($status)) {
                if ($phuca->canTransitionTo($status)) {
                    $phuca->transitionTo($status);
                } else {
                    return $this->ErrorResponse('cannot transition to state = ' . $status);
                }
            }

            return $this->OKResponse($phuca);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
