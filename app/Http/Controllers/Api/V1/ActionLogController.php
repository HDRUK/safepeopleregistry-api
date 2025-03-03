<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\User;

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

        return response()->json($logs);
    }

}
