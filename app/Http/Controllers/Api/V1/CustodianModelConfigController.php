<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\CustodianModelConfig;
use App\Models\Custodian;
use App\Models\EntityModelType;
use App\Models\DecisionModel;
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
        $conf = CustodianModelConfig::where('custodian_id', $id)->get();
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
            $conf = CustodianModelConfig::findOrFail($id);
            $conf->update($input);


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
            $conf = CustodianModelConfig::where('id', $id)->first();
            $conf->update([
                'active' => 0,
            ]);

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
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                      @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                      @OA\Property(property="active", type="boolean", example=true)
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
    public function getEntityModels(GetEntityModelsRequest $request, int $id): JsonResponse
    {
        $entityModelType = $request->input('entity_model_type');

        $entityModelTypeId = EntityModelType::where('name', $entityModelType)->value('id');

        if (!$entityModelTypeId) {
            return $this->NotFoundResponse();
        }

        $entityModels = DecisionModel::with(['custodianModelConfig' => function ($query) use ($id) {
            $query->where('custodian_id', $id);
        }])
        ->whereHas('custodianModelConfig', function ($query) use ($id) {
            $query->where('custodian_id', $id);
        })
        ->where('entity_model_type_id', $entityModelTypeId)
        ->get();

        if ($entityModels->isEmpty()) {
            return $this->NotFoundResponse();
        }

        $entityModels = $entityModels->map(function ($model) {
            return [
                'id' => $model->id,
                'name' => $model->name,
                'description' => $model->description,
                'active' => optional($model->custodianModelConfig)->active,
            ];
        });

        return $this->OKResponse($entityModels);
    }
    /**
      * @OA\Put(
      *      path="/api/v1/custodian_config/update-active/{id}",
      *      summary="Update active status of custodian model configs",
      *      description="Update the active status of specified custodian model configs for a given custodian",
      *      tags={"CustodianModelConfig"},
      *      security={{"bearerAuth":{}}},
      *      @OA\Parameter(
      *          name="id",
      *          in="path",
      *          required=true,
      *          description="ID of the custodian",
      *          @OA\Schema(type="integer")
      *      ),
      *      @OA\RequestBody(
      *          required=true,
      *          @OA\JsonContent(
      *              @OA\Property(
      *                  property="configs",
      *                  type="array",
      *                  @OA\Items(
      *                      type="object",
      *                      @OA\Property(property="entity_model_id", type="integer", example=1),
      *                      @OA\Property(property="active", type="boolean", example=true)
      *                  )
      *              )
      *          )
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Success",
      *          @OA\JsonContent(
      *              @OA\Property(property="message", type="string", example="Custodian model configs updated successfully"),
      *              @OA\Property(property="data", type="array",
      *                  @OA\Items(
      *                      type="object",
      *                      @OA\Property(property="entity_model_id", type="integer", example=1),
      *                      @OA\Property(property="active", type="boolean", example=true)
      *                  )
      *              )
      *          )
      *      ),
      *      @OA\Response(
      *          response=400,
      *          description="Bad Request",
      *          @OA\JsonContent(
      *              @OA\Property(property="message", type="string", example="Invalid input")
      *          )
      *      ),
      *      @OA\Response(
      *          response=404,
      *          description="Not Found",
      *          @OA\JsonContent(
      *              @OA\Property(property="message", type="string", example="Custodian or one or more entity models not found")
      *          )
      *      )
      * )
      */
    public function updateCustodianModelConfigsActive(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'configs' => 'required|array',
            'configs.*.entity_model_id' => 'required|integer|exists:entity_models,id',
            'configs.*.active' => 'required|boolean',
        ]);

        $configs = $request->input('configs');
        $updatedConfigs = [];

        foreach ($configs as $config) {
            $custodianModelConfig = CustodianModelConfig::where('custodian_id', $id)
                ->where('entity_model_id', $config['entity_model_id'])
                ->first();

            if ($custodianModelConfig) {
                $custodianModelConfig->active = $config['active'];
                $custodianModelConfig->save();
                $updatedConfigs[] = [
                    'entity_model_id' => $custodianModelConfig->entity_model_id,
                    'active' => $custodianModelConfig->active,
                ];
            }
        }

        if (count($updatedConfigs) !== count($configs)) {
            return $this->NotFoundResponse();
        }
        return $this->OKResponse($updatedConfigs);
    }
}
