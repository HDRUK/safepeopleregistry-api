<?php

namespace App\Http\Controllers\Api\V1;

use Gateway;
use Exception;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\ProjectDetail;
use App\Http\Requests\Gateway\QueryGatewayDur;
use App\Traits\CommonFunctions;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectDetailController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/project_details",
     *      summary="Return a list of ProjectDetail",
     *      description="Return a list of ProjectDetail",
     *      tags={"ProjectDetail"},
     *      summary="ProjectDetail@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/ProjectDetail"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $projectDetails = ProjectDetail::paginate((int)$this->getSystemConfig('PER_PAGE'));
        return $this->OKResponse($projectDetails);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/project_details/{id}",
     *      summary="Return a ProjectDetail",
     *      description="Return a ProjectDetail",
     *      tags={"ProjectDetail"},
     *      summary="ProjectDetail@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ProjectDetail entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="ProjectDetail entry ID"
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/ProjectDetail"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          )
     *      )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $projectDetail = ProjectDetail::where('id', $id)->first();
        if ($projectDetail) {
            return $this->OKResponse($projectDetail);
        }

        return $this->NotFoundResponse();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/project_details",
     *      summary="Create a ProjectDetail",
     *      description="Create a ProjectDetail",
     *      tags={"ProjectDetails"},
     *      summary="ProjectDetails@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="ProjectDetail definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/ProjectDetail"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->only(app(ProjectDetail::class)->getFillable());
            $input['datasets'] = $this->safeJsonArray($input['datasets'] ?? []);
            $input['other_approval_committees'] = $this->safeJsonArray($input['other_approval_committees'] ?? []);
            $projectDetail = ProjectDetail::create($input);

            return $this->CreatedResponse($projectDetail->id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/project_details/{id}",
     *      summary="Update a ProjectDetail entry",
     *      description="Update a ProjectDetail entry",
     *      tags={"ProjectDetails"},
     *      summary="ProjectDetails@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ProjectDetails entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="ProjectDetails entry ID"
     *         )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="ProjectDetails definition",
     *          @OA\JsonContent(
     *                  ref="#/components/schemas/ProjectDetail"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/ProjectDetail"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(ProjectDetail::class)->getFillable());
            $projectDetail = ProjectDetail::where('id', $id)->first();
            $projectDetail->update($input);

            return $this->OKResponse($projectDetail);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/project_details/{id}",
     *      summary="Delete a ProjectDetail entry from the system by ID",
     *      description="Delete a ProjectDetail entry from the system",
     *      tags={"ProjectDetails"},
     *      summary="ProjectDetails@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ProjectDetails entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="ProjectDetails entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            ProjectDetail::where('id', $id)->delete();
            return $this->OKResponse(null);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Hidden from Swagger
    public function queryGatewayDurByProjectID(QueryGatewayDur $request): JsonResponse
    {
        try {
            $input = $request->only(['custodian_id', 'project_id']);

            $custodian = Custodian::where('id', $input['custodian_id'])->first();
            $project = Project::where('id', $input['project_id'])->first();

            return $this->OKResponse(Gateway::getDataUsesByProjectID($custodian->id, $project->id));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function safeJsonArray($value)
    {
        return json_encode(is_array($value) ? $value : []);
    }
}
