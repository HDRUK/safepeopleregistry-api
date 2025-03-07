<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\ValidationLog;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\ProjectHasUser;
use App\Models\ProjectHasCustodian;
use App\Models\Registry;
use Carbon\Carbon;
use App\Http\Traits\Responses;

class ValidationLogController extends Controller
{
    use Responses;
    /**
     * @OA\Get(
     *     path="/api/v1/validation_logs/{custodianId}/{projectId}/{registryId}",
     *     summary="Get Validation Logs for Custodian, Project, and Registry",
     *     description="Retrieve validation logs associated with a given custodian, project, and registry.",
     *     tags={"Validation Logs"},
     *
     *     @OA\Parameter(
     *         name="custodianId",
     *         in="path",
     *         required=true,
     *         description="The ID of the custodian entity",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="The ID of the project entity",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="The ID of the registry entity",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with validation logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ValidationLog")
     *             )
     *         )
     *     )
     * )
     */
    public function getCustodianProjectUserValidationLogs(
        Request $request,
        int $custodianId,
        int $projectId,
        int $registryId
    ): JsonResponse {
        try {
            $registry = Registry::findOrFail($registryId);
            $phu = ProjectHasUser::where(
                [
                    'project_id' => $projectId,
                    'user_digital_ident' => $registry->digi_ident
                ]
            )->first();

            if (is_null($phu)) {
                return $this->NotFoundResponse();
            }

            $phc = ProjectHasCustodian::where(
                [
                    'project_id' => $projectId,
                    'custodian_id' => $custodianId
                ]
            )->first();

            if (is_null($phc)) {
                return $this->NotFoundResponse();
            }

            $logs = ValidationLog::where('entity_type', Custodian::class)
                ->where('entity_id', $custodianId)
                ->where('secondary_entity_type', Project::class)
                ->where('secondary_entity_id', $projectId)
                ->where('tertiary_entity_type', Registry::class)
                ->where('tertiary_entity_id', $registryId)
                ->with("comments")
                ->get();

            return $this->OKResponse($logs);

        } catch (Exception $e) {
            return $this->ErrorResponse();
        }

    }

    /**
     * @OA\Get(
     *     path="/api/v1/validation_logs/{id}",
     *     summary="Get  a Validation Log",
     *     description="Retrieve a specific entry for a validation log .",
     *     tags={"Validation Log with comments"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the validation log",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Validation log with comments",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ValidationLog"))
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Validation log not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Validation log not found"))
     *     )
     * )
     */
    public function index($validationLogId): JsonResponse
    {
        $validationLog = ValidationLog::with("comments")->find($validationLogId);
        if (!$validationLog) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($validationLog);
    }

    /**
    * @OA\Get(
    *     path="/api/v1/validation_logs/{id}/comments",
    *     summary="Get all comments for a Validation Log",
    *     description="Retrieve all comments associated with a specific validation log entry.",
    *     tags={"Validation Log Comments"},
    *     security={{"bearerAuth":{}}},
    *
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="The ID of the validation log",
    *         @OA\Schema(type="integer")
    *     ),
    *
    *     @OA\Response(
    *         response=200,
    *         description="Validation log with comments",
    *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ValidationLog"))
    *     ),
    *
    *     @OA\Response(
    *         response=404,
    *         description="Validation log not found",
    *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Validation log not found"))
    *     )
    * )
    */
    public function comments($validationLogId): JsonResponse
    {
        $validationLog = ValidationLog::find($validationLogId);
        if (!$validationLog) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($validationLog->comments);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/validation_logs/{id}",
     *     summary="Update a Validation Log",
     *     description="Update a validation log entry, including marking it as complete, incomplete, passed, or failed.",
     *     tags={"Validation Logs"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the validation log entry",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="complete", type="boolean", description="Mark the validation log as complete"),
     *             @OA\Property(property="incomplete", type="boolean", description="Mark the validation log as incomplete"),
     *             @OA\Property(property="pass", type="boolean", description="Mark the validation log as passed"),
     *             @OA\Property(property="fail", type="boolean", description="Mark the validation log as failed")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Validation log status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Action status updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ValidationLog")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Validation log not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation log not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $log = ValidationLog::find($id);
        if (!$log) {
            return $this->NotFoundResponse();
        }

        if ($request->has('complete')) {
            $log->completed_at = Carbon::now();
        } elseif ($request->has('incomplete')) {
            $log->completed_at = null;
        }

        if ($request->has('pass')) {
            $log->completed_at = Carbon::now();
            $log->manually_confirmed = 1;
        } elseif ($request->has('fail')) {
            $log->completed_at = Carbon::now();
            $log->manually_confirmed = 0;
        }


        $log->save();
        $log->refresh();

        return $this->OKResponse($log);

    }



}
