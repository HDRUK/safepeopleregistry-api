<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use function activity;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\ActionLogs\UpdateActionLog;
use App\Http\Requests\ActionLogs\GetEntityActionLog;

class ActionLogController extends Controller
{
    use Responses;
    /**
     * @OA\Get(
     *     path="/api/v1/{entity}/{id}/action_log",
     *     summary="Get Action Logs for an Entity",
     *     description="Retrieve action logs for a given entity type (users, organisations) by ID.",
     *     tags={"Action Logs"},
     *
     *     @OA\Parameter(
     *         name="entity",
     *         in="path",
     *         required=true,
     *         description="The entity type (e.g., users, organisations)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the entity",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with action logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ActionLog")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No action logs found for this entity",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No action logs found for this entity"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid entity type",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid entity type"
     *             )
     *         )
     *     )
     * )
     */
    public function getEntityActionLog(GetEntityActionLog $request, $entity, $id)
    {
        if (!isset(ActionLog::ENTITY_MAP[$entity])) {
            return $this->BadRequestResponse();
        }

        $logs = ActionLog::where('entity_type', ActionLog::ENTITY_MAP[$entity])
            ->where('entity_id', $id)
            ->get();

        if (count($logs) === 0) {
            return $this->NotFoundResponse();
        }

        if (!Gate::allows('view', $logs[0])) {
            return $this->ForbiddenResponse();
        }

        if ($logs->isEmpty()) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($logs);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/action_logs/{id}",
     *     summary="Update an Action Log",
     *     description="Update an action log entry, including marking it as complete or incomplete.",
     *     tags={"Action Logs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="complete",
     *         in="query",
     *         description="Mark as complete",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="incomplete",
     *         in="query",
     *         description="Mark as incomplete",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Action status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Action status updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ActionLog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Action log not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Action log not found")
     *         )
     *     )
     * )
     */
    public function update(UpdateActionLog $request, $id)
    {
        $log = ActionLog::find($id);
        if (!$log) {
            return response()->json(['message' => 'Action log not found'], 404);
        }

        if (!Gate::allows('update', $log)) {
            return $this->ForbiddenResponse();
        }

        if ($request->has('complete')) {
            $log->completed_at = Carbon::now();
            activity()
                ->causedBy(Auth::user())
                ->performedOn($log)
                ->event('action_log_completed')
                ->useLog('action_log')
                ->log($log->action);
        } elseif ($request->has('incomplete')) {
            $log->completed_at = null;
        }
        $log->save();
        $log->refresh();

        return $this->OKResponse($log);
    }
}
