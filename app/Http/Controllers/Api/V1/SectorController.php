<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Sector;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sectors\GetSector;
use App\Http\Requests\Sectors\DeleteSector;
use App\Http\Requests\Sectors\UpdateSector;

/**
 * @OA\Tag(
 *     name="Sector",
 *     description="API endpoints for managing sectors"
 * )
 */
class SectorController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *     path="/api/v1/sectors",
     *     tags={"Sector"},
     *     summary="Get a list of sectors",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Sector")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $sectors = Sector::paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $sectors,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sectors/{id}",
     *     tags={"Sector"},
     *     summary="Get a specific sector by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the sector",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Sector")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sector not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sector not found")
     *         )
     *     )
     * )
     */
    public function show(GetSector $request, int $id): JsonResponse
    {
        try {
            $sector = Sector::findOrFail($id);

            return response()->json([
                'message' => 'success',
                'data' => $sector,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Sector not found',
                'data' => null,
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sectors",
     *     tags={"Sector"},
     *     summary="Create a new sector",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Sector")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/sectors/{id}",
     *     tags={"Sector"},
     *     summary="Update an existing sector",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the sector",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Sector")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(ref="#/components/schemas/Sector")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sector not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sector not found")
     *         )
     *     )
     * )
     */
    public function update(UpdateSector $request, int $id): JsonResponse
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

    /**
     * @OA\Delete(
     *     path="/api/v1/sectors/{id}",
     *     tags={"Sector"},
     *     summary="Delete a sector",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the sector",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sector not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sector not found")
     *         )
     *     )
     * )
     */
    public function destroy(DeleteSector $request, int $id): JsonResponse
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
