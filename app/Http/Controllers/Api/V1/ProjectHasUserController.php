<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasUser;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;

class ProjectHasUserController extends Controller
{
    use Responses;
    use CommonFunctions;

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
        int $projectUserId,
    ) {
        try {
            $phu = ProjectHasUser::with([
                'registry.user',
                'project:id,title',
                'role:id,name',
                'affiliation:id,organisation_id',
                'affiliation.organisation:id,organisation_name'
            ])
                ->where([
                    'id' => $projectUserId,
                ])->first();

            return $this->OKResponse($phu);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
