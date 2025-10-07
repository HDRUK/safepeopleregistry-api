<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Resolution;
use App\Models\Infringement;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\InfringementHasResolution;
use App\Http\Requests\Resolutions\GetResolutionByRegistry;
use App\Http\Requests\Resolutions\CreateResolutionByRegistry;

/**
 * @OA\Tag(
 *     name="Resolution",
 *     description="API endpoints for managing resolutions"
 * )
 */
class ResolutionController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *     path="/api/v1/registries/{registryId}/resolutions",
     *     tags={"Resolution"},
     *     summary="Get resolutions by registry ID",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Resolution")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     )
     * )
     */
    public function indexByRegistryId(GetResolutionByRegistry $request, int $registryId): JsonResponse
    {
        $resolutions = Resolution::where('registry_id', $registryId)->get();

        return response()->json([
            'message' => 'success',
            'data' => $resolutions,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/registries/{registryId}/resolutions",
     *     tags={"Resolution"},
     *     summary="Create a new resolution for a registry",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Resolution")
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
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *         )
     *     )
     * )
     */
    public function storeByRegistryId(CreateResolutionByRegistry $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $resolution = Resolution::create([
                'comment' => $input['comment'],
                'custodian_by' => $input['custodian_by'],
                'registry_id' => $registryId,
                'resolved' => $input['resolved'],
            ]);

            if (isset($input['infringement_id'])) {
                $infringement = Infringement::where('id', $input['infringement_id'])->first();
                if ($infringement) {
                    InfringementHasResolution::create([
                        'resolution_id' => $resolution->id,
                        'infringement_id' => $infringement->id,
                    ]);
                }
            }

            return response()->json([
                'message' => 'success',
                'data' => $resolution->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
