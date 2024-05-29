<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\SystemConfig;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use App\Exception\NotFoundException;

class SystemConfigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $systemConfig = SystemConfig::all();

        return response()->json([
            'message' => 'success',
            'data' => $systemConfig,
        ], 200);
    }

    public function getByName(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            $systemConfig = SystemConfig::where('name', $input['name'])->first();

            return response()->json([
                'message' => 'success',
                'data' => $systemConfig,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
