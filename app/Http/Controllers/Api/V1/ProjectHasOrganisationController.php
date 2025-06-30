<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;

/**
 * @OA\Tag(
 *     name="ProjectHasOrganisation",
 *     description="API endpoints for managing project-organisation relationships"
 * )
 */
class ProjectHasOrganisationController extends Controller
{
    use Responses;
    use CommonFunctions;

    /**
     * @OA\Get(
     *     path="/api/v1/project-organisations/{projectOrganisationId}",
     *     tags={"ProjectHasOrganisation"},
     *     summary="Get details of a project-organisation relationship",
     *     @OA\Parameter(
     *         name="projectOrganisationId",
     *         in="path",
     *         required=true,
     *         description="ID of the project-organisation relationship",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectHasOrganisation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project-organisation relationship not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An error occurred")
     *         )
     *     )
     * )
     */
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

            if (!$phu) {
                return $this->NotFoundResponse();
            }

            return $this->OKResponse($phu);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
