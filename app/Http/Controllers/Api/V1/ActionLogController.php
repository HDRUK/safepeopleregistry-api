<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\User;
use Carbon\Carbon;

class ActionLogController extends Controller
{
    public function getUserActionLog($userId)
    {
        $logs = ActionLog::where('entity_type', User::class)
                ->where('entity_id', $userId)
                ->get();

        if ($logs->isEmpty()) {
            return response()->json(['message' => 'No action logs found for this user'], 404);
        }

        return response()->json(['data' => $logs]);
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
    public function update(Request $request, $id)
    {
        $log = ActionLog::find($id);
        if (!$log) {
            return response()->json(['message' => 'Action log not found'], 404);
        }

        if ($request->has('complete')) {
            $log->completed_at = Carbon::now();
        } elseif ($request->has('incomplete')) {
            $log->completed_at = null;
        }
        $log->save();
        $log->refresh();

        return response()->json([
            'message' => 'Action status updated successfully',
            'data' => $log
        ]);
    }


}
