<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\CustodianHasProjectUser;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\Custodian;
use Illuminate\Support\Facades\Gate;
use App\Traits\CommonFunctions;

class CustodianHasProjectUserController extends Controller
{
    use Responses;
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/custodians/{custodianId}/projectUsers",
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

            $records = CustodianHasProjectUser::with([
                'modelState.state',
                'projectHasUser.registry.user',
                'projectHasUser.project:id,title',
                'projectHasUser.role:id,name',
                'projectHasUser.affiliation:id,organisation_id',
                'projectHasUser.affiliation.organisation:id,organisation_name'
            ])->where('custodian_id', $custodianId)
                ->paginate((int)$this->getSystemConfig('PER_PAGE'));

            return $this->OKResponse($records);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodians/{custodianId}/projectUsers/{projectUserId}",
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
        int $projectUserId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $puhca = CustodianHasProjectUser::with([
                'modelState.state',
                'projectHasUser.registry.user',
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

            $updateData = $request->only(['approved', 'comment']);

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
