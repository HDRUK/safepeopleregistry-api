<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Registry;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Exceptions\NotFoundException;
use App\Http\Requests\Registries\GetRegistry;
use App\Http\Requests\Registries\DeleteRegistry;
use App\Http\Requests\Registries\UpdateRegistry;

class RegistryController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/registry",
     *      summary="Return a list of Registry entries",
     *      description="Return a list of Registry entries",
     *      tags={"Registry"},
     *      summary="Registry@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Registry"
     *              )
     *          ),
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
        if (!Gate::allows('viewAny', Registry::class)) {
            return $this->ForbiddenResponse();
        }
        $registries = Registry::with([
            'files',
        ])->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return $this->OKResponse($registries);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/registry/{id}",
     *      summary="Return a Registry entry by ID",
     *      description="Return a Registry entry by ID",
     *      tags={"Registry"},
     *      summary="Registry@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Registry"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *           ),
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
    public function show(GetRegistry $request, int $id): JsonResponse
    {
        $registry = Registry::with([
            'files',
        ])->findOrFail($id);

        if (!Gate::allows('view', $registry)) {
            return $this->ForbiddenResponse();
        }

        if ($registry) {
            return $this->OKResponse($registry);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/registry",
     *      summary="Create a Registry entry",
     *      description="Create a Registry entry",
     *      tags={"Registry"},
     *      summary="Registry@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Registry definition",
     *          ref="#/components/schemas/Registry"
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
     *          ref="#/components/schemas/Registry"
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
        if (!Gate::allows('admin')) {
            return $this->ForbiddenResponse();
        }
        try {
            $input = $request->only(app(Registry::class)->getFillable());
            $registry = Registry::create($input);



            return $this->CreatedResponse($registry->id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/registry/{id}",
     *      summary="Update a Registry entry",
     *      description="Update a Registry entry",
     *      tags={"Registry"},
     *      summary="Registry@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Registry definition",
     *          ref="#/components/schemas/Registry"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Registry"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *           ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
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
    public function update(UpdateRegistry $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(Registry::class)->getFillable());
            $registry = Registry::findOrFail($id);
            if (!Gate::allows('update', $registry)) {
                return $this->ForbiddenResponse();
            }
            $registry->update($input);


            return $this->OKResponse($registry);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/registry/{id}",
     *      summary="Delete a Registry entry from the system by ID",
     *      description="Delete a Registry entry from the system",
     *      tags={"Registry"},
     *      summary="Registry@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *           ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
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
    public function destroy(DeleteRegistry $request, int $id): JsonResponse
    {
        try {
            $registry = Registry::findOrFail($id);
            if (!Gate::allows('delete', $registry)) {
                return $this->ForbiddenResponse();
            }
            $registry->delete();
            return $this->OKResponse(null);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
