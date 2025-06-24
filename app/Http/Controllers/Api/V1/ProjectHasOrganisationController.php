<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;

class ProjectHasOrganisationController extends Controller
{
    use Responses;
    use CommonFunctions;


    public function show(
        Request $request,
        int $projectOrganisationId,
    ) {
        try {
            $phu = ProjectHasOrganisation::with([
                'organisation',
            ])
                ->where([
                    'id' => $projectOrganisationId,
                ])->first();

            return $this->OKResponse($phu);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/project_organisations/{id}",
     *      summary="Delete a organisation from a project",
     *      description="Delete a organisation from a project",
     *      tags={"Projects"},
     *      summary="ProjectHasOrganisation@delete",
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
     *          response=404,
     *          description="not found",
     *      )
     * )
     */
    public function delete(Request $request, int $id)
    {
        try {
            $data = ProjectHasOrganisation::where('id', $id);

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
