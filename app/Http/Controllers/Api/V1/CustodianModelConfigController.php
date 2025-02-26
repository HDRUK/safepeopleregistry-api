<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\CustodianModelConfig;
use App\Models\Custodian;
use App\Models\EntityModelType;
use App\Models\EntityModel;
use App\Traits\CommonFunctions;
use App\Http\Traits\Responses;
use App\Http\Requests\CustodianModelConfig\CreateCustodianModelConfigRequest;
use App\Http\Requests\CustodianModelConfig\UpdateCustodianModelConfigRequest;
use App\Http\Requests\CustodianModelConfig\GetEntityModelsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustodianModelConfigController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/custodian_config/{id}",
     *      summary="Return a list of Custodian config",
     *      description="Return a list of Custodian config",
     *      tags={"CustodianModelConfig"},
     *      summary="CustodianModelConfig@getByCustodianID",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="CustodianModelConfig entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="CustodianModelConfig entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                   ref="#/components/schemas/CustodianModelConfig"
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
    public function getByCustodianID(Request $request, int $id): JsonResponse
    {
        $conf = CustodianModelConfig::where('custodian_id', $id);
        if (!$conf) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($conf);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/custodian_config",
     *      summary="Create a CustodianModelConfig entry",
     *      description="Create a CustodianModelConfig entry",
     *      tags={"CustodianModelConfig"},
     *      summary="CustodianModelConfig@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="CustodianModelConfig definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/CustodianModelConfig"
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
     *                  ref="#/components/schemas/CustodianModelConfig"
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
    public function store(CreateCustodianModelConfigRequest $request): JsonResponse
    {
        try {
            $input = $request->only(app(CustodianModelConfig::class)->getFillable());

            $conf = CustodianModelConfig::create([
                'entity_model_id' => $input['entity_model_id'],
                'active' => $input['active'],
                'custodian_id' => $input['custodian_id'],
            ]);

            return $this->CreatedResponse($conf->id);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // LS - Not sure if we'd use this, leaving in for now
    //
    // public function show(Request $request, int $id): JsonResponse
    // {
    //     //
    // }

    /**
     * @OA\Put(
     *      path="/api/v1/custodian_config/{id}",
     *      summary="Update an CustodianModelConfig entry",
     *      description="Update an CustodianModelConfig entry",
     *      tags={"CustodianModelConfig"},
     *      summary="CustodianModelConfig@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="CustodianModelConfig entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="CustodianModelConfig entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="CustodianModelConfig definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/CustodianModelConfig"
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
     *                  ref="#/components/schemas/CustodianModelConfig"
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
    public function update(UpdateCustodianModelConfigRequest $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(CustodianModelConfig::class)->getFillable());
            $conf = tap(CustodianModelConfig::where('id', $id))->update($input)->first();

            return $this->OKResponse($conf);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/custodian_config/{id}",
     *      summary="Delete a CustodianModelConfig entry from the system by ID",
     *      description="Delete a CustodianModelConfig entry from the system",
     *      tags={"CustodianModelConfig"},
     *      summary="CustodianModelConfig@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="CustodianModelConfig entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="CustodianModelConfig entry ID",
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
            CustodianModelConfig::where('id', $id)->first()->delete();
            return $this->OKResponse(null);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @OA\Get(
     *      path="/api/v1/entity_models",
     *      summary="Get entity models for custodian config",
     *      description="Retrieve entity models associated with custodian config based on the specified entity_model_type",
     *      tags={"CustodianModelConfig"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="entity_model_type",
     *          in="query",
     *          required=true,
     *          description="Type of entity model to retrieve",
     *          @OA\Schema(
     *              type="string",
     *              enum={"decision_model", "user_validation_rules", "org_validation_rules"}
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Decision Model A"),
     *                      @OA\Property(property="entity_model_type_id", type="integer", example=1),
     *                      @OA\Property(property="description", type="string", nullable=true, example="This is a decision model for process A"),
     *                      @OA\Property(property="model_data", type="object", nullable=true),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                      @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="No entity models found")
     *          )
     *      )
     * )
     */
    public function getEntityModels(GetEntityModelsRequest $request): JsonResponse
    {
        $entityModelType = $request->input('entity_model_type');

        $entityModelTypeId = EntityModelType::where('name', $entityModelType)->value('id');

        if (!$entityModelTypeId) {
            return $this->NotFoundResponse('Invalid entity model type');
        }

        $entityModelIds = CustodianModelConfig::pluck('entity_model_id')->unique();

        $entityModels = EntityModel::whereIn('id', $entityModelIds)
            ->where('entity_model_type_id', $entityModelTypeId)
            ->get();

        if ($entityModels->isEmpty()) {
            return $this->NotFoundResponse('No entity models found for the specified type');
        }

        return $this->OKResponse($entityModels);
    }
}
