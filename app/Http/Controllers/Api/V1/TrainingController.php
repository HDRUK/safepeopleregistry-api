<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\File;
use App\Models\Registry;
use App\Models\Training;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\TrainingHasFile;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Models\RegistryHasTraining;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Training\GetTraining;
use App\Http\Requests\Training\CreateTraining;
use App\Http\Requests\Training\DeleteTraining;
use App\Http\Requests\Training\UpdateTraining;
use App\Http\Requests\Training\GetTrainingByRegistry;
use App\Http\Requests\Training\GetLinkTrainingAndFile;

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
        $trainings = Training::with('files')
            ->searchViaRequest()
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
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
    public function indexByRegistryId(GetTrainingByRegistry $request, int $registryId): JsonResponse
    {
        $registry = Registry::findOrFail($registryId);
        if (!Gate::allows('view', $registry)) {
            return $this->ForbiddenResponse();
        }

        $linkedTraining = RegistryHasTraining::where([
            'registry_id' => $registryId,
        ])->select('training_id')->get()->toArray();

        $trainings = Training::with('files')
            ->whereIn('id', $linkedTraining)->get();
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
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
    public function show(GetTraining $request, int $id): JsonResponse
    {
        $training = Training::with('files')
            ->where('id', $id)->first();
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          ),
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
    public function update(UpdateTraining $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(Training::class)->getFillable());
            $training = Training::findOrFail($id);
            $training->update($input);

            return $this->OKResponse($training);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger
    public function destroy(DeleteTraining $request, int $id): JsonResponse
    {
        try {
            $training = Training::with('files')
                ->where('id', $id)->first();

            if (!is_null($training)) {
                // Delete files associated with this training entry
                foreach ($training->files as $file) {
                    $file->delete();

                    // Delete logical links between training and files
                    TrainingHasFile::where([
                        'training_id' => $training->id,
                        'file_id' => $file->id,
                    ])->delete();
                }

                // Finally delete the training entries
                $training->delete();

                RegistryHasTraining::where([
                    'training_id' => $training->id,
                ])->delete();
            } else {
                return $this->NotFoundResponse();
            }

            return $this->OKResponse(null);
        } catch (Exception $e) {
            return $this->ErrorResponse();
        }
    }

    public function linkTrainingFile(GetLinkTrainingAndFile $request, int $trainingId, int $fileId): JsonResponse
    {
        try {
            $training = Training::where('id', $trainingId)->first();
            $file = File::where('id', $fileId)->first();

            if ($training && $file) {
                TrainingHasFile::create([
                    'training_id' => $training->id,
                    'file_id' => $file->id,
                ]);

                return $this->OKResponse(null);
            }

            return $this->NotFoundResponse();
        } catch (Exception $e) {
            return $this->ErrorResponse();
        }
    }
}
