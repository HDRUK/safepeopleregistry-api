<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ProjectRole;
use App\Traits\CommonFunctions;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectRoleController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/project_roles",
     *      summary="Return a list of ProjectRole",
     *      description="Return a list of ProjectRole",
     *      tags={"ProjectRole"},
     *      summary="ProjectRole@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/ProjectRole"
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
        $roles = ProjectRole::all();
        return $this->OKResponse($roles);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/project_roles/{id}",
     *      summary="Return a ProjectRole",
     *      description="Return a ProjectRole",
     *      tags={"ProjectRole"},
     *      summary="ProjectRole@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ProjectRole entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="ProjectRole entry ID"
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/ProjectRole"
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
        $role = ProjectRole::where('id', $id)->first();
        if ($role) {
            return $this->OKResponse($role);
        }

        return $this->NotFoundResponse();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/project_roles",
     *      summary="Create a ProjectRole",
     *      description="Create a ProjectRole",
     *      tags={"ProjectRole"},
     *      summary="ProjectRole@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="ProjectRole definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/ProjectRole"
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
            $input = $request->only(app(ProjectRole::class)->getFillable());
            $role = ProjectRole::create($input);
            if ($role) {
                return $this->CreatedResponse($role->id);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/project_roles/{id}",
     *      summary="Update a ProjectRole entry",
     *      description="Update a ProjectRole entry",
     *      tags={"ProjectRole"},
     *      summary="ProjectRole@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ProjectRole entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="ProjectRole entry ID"
     *         )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="ProjectRole definition",
     *          @OA\JsonContent(
     *                  ref="#/components/schemas/ProjectRole"
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
     *                  ref="#/components/schemas/ProjectRole"
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
            $input = $request->only(app(ProjectRole::class)->getFillable());
            $role = tap(ProjectRole::where('id', $id))->update($input)->first();

            return $this->OKResponse($role);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Purposefully not allowing anyone to delete Roles at this level.
}
