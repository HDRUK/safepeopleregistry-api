<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;

class ActionLogController extends Controller
{
    public function getUserActionLog($id)
    {
        $logs = ActionLog::where('user_id', $id)->latest()->paginate(10);

        if ($logs->isEmpty()) {
            return response()->json(['message' => 'No action logs found for this user'], 404);
        }

        return response()->json($logs);
    }

}
