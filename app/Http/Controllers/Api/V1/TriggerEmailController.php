<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use TriggerEmail;

class TriggerEmailController extends Controller
{
    use Responses;

    public function spawnEmail(Request $request): JsonResponse
    {
        if (! Gate::allows('notReadOnly')) {
            return $this->ForbiddenResponse();
        }

        $input = $request->all();

        TriggerEmail::spawnEmail($input);

        return response()->json([
            'message' => 'success',
            'data' => null,
        ]);
    }
}
