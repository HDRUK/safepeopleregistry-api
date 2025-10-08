<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Traits\Responses;
use App\Models\ProjectHasUser;
use App\Traits\CommonFunctions;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectHasUsers\GetProjectHasUser;
use App\Http\Requests\ProjectHasUsers\DeleteProjectHasUser;

class ProjectHasUserController extends Controller
{
    use Responses;
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/project_users/{id}",
     *      operationId="showProjectUser",
     *      tags={"Project User"},
     *      summary="Get project user details",
     *      description="Returns details for a specific project user",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
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
        GetProjectHasUser $request,
        int $id,
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
                    'id' => $id,
                ])->first();

            return $this->OKResponse($phu);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/project_users/{id}",
     *      summary="Delete a user from a project",
     *      description="Delete a user from a project",
     *      tags={"Projects"},
     *      summary="ProjectHasUser@delete",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="failed",
     *      )
     * )
     */
    public function delete(DeleteProjectHasUser $request, int $id)
    {
        try {
            $data = ProjectHasUser::where('id', $id);

            if ($data->first() !== null) {
                $data->delete();

                return $this->OKResponse(null);
            }

            return $this->NotFoundResponse();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
