<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Sector;
use App\Traits\CommonFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    use CommonFunctions;

    public function index(Request $request): JsonResponse
    {
        $sectors = Sector::paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $sectors,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $sector = Sector::findOrFail($id);

            if (!$sector) {
                return response()->json([
                    'message' => 'not found',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'message' => 'success',
                'data' => $sector,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            $sector = Sector::create([
                'name' => $input['name'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $sector->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            $sector = Sector::where('id', $id)->first();
            $sector->name = $input['name'];

            if ($sector->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $sector,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
            ], 400);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function edit(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            $sector = Sector::where('id', $id)->first();
            $sector->name = isset($input['name']) ? $input['name'] : $sector->name;

            if ($sector->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $sector,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
            ], 400);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            Sector::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
