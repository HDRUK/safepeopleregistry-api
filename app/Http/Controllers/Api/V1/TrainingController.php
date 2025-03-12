<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\RegistryHasTraining;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Http\Requests\Training\CreateTraining;
use App\Http\Requests\Training\DeleteTraining;

class TrainingController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/training",
     *      summary="Return a list of Training entries",
     *      description="Return a list of Training entries",
     *      tags={"Training"},
     *      summary="Training@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Training"
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
        $trainings = Training::searchViaRequest()
            ->applySorting()
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return $this->OKResponse($trainings);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/training/registry/{id}",
     *      summary="Return a list of training by registry id",
     *      description="Return a list of training by registry id",
     *      tags={"Training"},
     *      summary="Training@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training registry id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training registry id",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Training"
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
    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $linkedTraining = RegistryHasTraining::where([
            'registry_id' => $registryId,
        ])->select('training_id')->get()->toArray();

        $trainings = Training::whereIn('id', $linkedTraining)->get();
        return $this->OKResponse($trainings);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/training/{id}",
     *      summary="Return a training record",
     *      description="Return a training record by registry id",
     *      tags={"Training"},
     *      summary="Training@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training id",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Training"
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
    public function show(Request $request, int $id): JsonResponse
    {
        $training = Training::where('id', $id)->first();
        if ($training) {
            return $this->OKResponse($training);
        }

        return $this->NotFoundResponse();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/training",
     *      summary="Create a Training entry",
     *      description="Create a Training entry",
     *      tags={"Training"},
     *      summary="Training@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Training definition",
     *          ref="#/components/schemas/Training"
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
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Training"
     *              )
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
    public function store(CreateTraining $request): JsonResponse
    {
        try {
            $input = $request->only(app(Training::class)->getFillable());
            $training = Training::create($input);

            RegistryHasTraining::create([
                'training_id' => $training->id,
                'registry_id' => $request->get('registry_id'),
            ]);

            return $this->CreatedResponse($training->id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/training/{id}",
     *      summary="Update a Training entry",
     *      description="Update a Training entry",
     *      tags={"Training"},
     *      summary="Training@update",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Training definition",
     *          ref="#/components/schemas/Training"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Training"
     *              )
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
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(Training::class)->getFillable());
            $training = tap(Training::where('id', $id))->update($input)->first();

            return $this->OKResponse($training);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger
    public function destroy(DeleteTraining $request, int $id): JsonResponse
    {
        try {
            Training::where('id', $id)->delete();
            RegistryHasTraining::where([
                'training_id' => $id,
                'registry_id' => $request->get('registry_id'),
            ])->delete();
            return $this->OKResponse(null);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
