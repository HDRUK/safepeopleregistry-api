<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TriggerEmail;

class TriggerEmailController extends Controller
{
    public function spawnEmail(Request $request): JsonResponse
    {
        $input = $request->all();

        TriggerEmail::spawnEmail($input);

        return response()->json([
            'message' => 'success',
            'data' => null,
        ]);
    }
}
