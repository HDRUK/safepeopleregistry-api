<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Carbon\Carbon;
use App\Models\Accreditation;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\RegistryHasAccreditation;
use App\Http\Requests\Accreditations\GetAccreditationByRegistry;
use App\Http\Requests\Accreditations\CreateAccreditationByRegistry;
use App\Http\Requests\Accreditations\DeleteAccreditationByRegistry;
use App\Http\Requests\Accreditations\UpdateAccreditationByRegistry;

/**
 * @OA\Tag(
 *     name="Accreditation",
 *     description="API endpoints for managing accreditations"
 * )
 */
class AccreditationController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *     path="/api/v1/registries/{registryId}/accreditations",
     *     tags={"Accreditation"},
     *     summary="Get accreditations by registry ID",
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
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Accreditation")
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      )
     *  )
     */
    public function indexByRegistryId(GetAccreditationByRegistry $request, int $registryId): JsonResponse
    {
        $rha = RegistryHasAccreditation::where('registry_id', $registryId)
            ->get()
            ->select('accreditation_id');

        $accreditations = Accreditation::whereIn('id', $rha)
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $accreditations,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/registries/{registryId}/accreditations",
     *     tags={"Accreditation"},
     *     summary="Create accreditation for a registry",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Accreditation")
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
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      )
     * )
     */
    public function storeByRegistryId(CreateAccreditationByRegistry $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $accreditation = Accreditation::create([
                'associated_organisation_name' => $input['associated_organisation_name'],
                'id_string' => $input['id_string'],
                'issue_date' => Carbon::parse($input['issue_date'])->toDateString(),
                'expiry_date' => Carbon::parse($input['expiry_date'])->toDateString(),
            ]);

            RegistryHasAccreditation::create([
                'registry_id' => $registryId,
                'accreditation_id' => $accreditation->id,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $accreditation->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/accreditations/{id}/registries/{registryId}",
     *     tags={"Accreditation"},
     *     summary="Update accreditation for a registry",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the accreditation",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Accreditation")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/Accreditation")
     *         )
     *     ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      )
     * )
     */
    public function updateByRegistryId(UpdateAccreditationByRegistry $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();
            $accreditation = Accreditation::where('id', $id)->first();

            $accreditation->associated_organisation_name = $input['associated_organisation_name'];
            $accreditation->id_string = $input['id_string'];
            $accreditation->issue_date = Carbon::parse($input['issue_date'])->toDateString();
            $accreditation->expiry_date = Carbon::parse($input['expiry_date'])->toDateString();

            $accreditation->save();

            return response()->json([
                'message' => 'success',
                'data' => $accreditation,
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Hidden from Swagger documentation
     */
    public function destroyByRegistryId(DeleteAccreditationByRegistry $request, int $id, int $registryId): JsonResponse
    {
        try {
            Accreditation::where('id', $id)->first()->delete();
            RegistryHasAccreditation::where([
                'accreditation_id' => $id,
                'registry_id' => $registryId,
            ])->delete();

            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
