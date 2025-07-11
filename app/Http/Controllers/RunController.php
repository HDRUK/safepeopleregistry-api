<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Jobs\UpdateActionNotifications;

class RunController extends Controller
{
    public function runJob(Request $request): JsonResponse
    {
        UpdateActionNotifications::dispatch();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
