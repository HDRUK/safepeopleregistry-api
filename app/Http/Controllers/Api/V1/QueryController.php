<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Registry;
use App\Exceptions\NotFoundException;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QueryController extends Controller
{
    public function query(Request $request): JsonResponse
    {
        $input = $request->all();
        $registry = Registry::with([
            'user',
            'identity',
            'history',
            'training',
            'projects',
            'affiliations'
        ])->where('digi_ident', $input['ident'])->first();
        if ($registry) {
            return response()->json([
                'message' => 'success',
                'data' => $registry,
            ], 200);
        }

        throw new NotFoundException();
    }
}
