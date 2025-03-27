<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Affiliation;
use App\Models\RegistryHasAffiliation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;

class AffiliationController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/affiliations/{registryId}",
     *      summary="Return a list of affiliations by registry id",
     *      description="Return a list of affiliations by registry id",
     *      tags={"Affiliations"},
     *      summary="Affiliations@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliations registry id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliations registry id",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/Affiliation")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $rha = RegistryHasAffiliation::where('registry_id', $registryId)
            ->get()
            ->select('affiliation_id');

        $affiliations = Affiliation::with(
            [
                'organisation' => function ($query) {
                    $query->select(
                        'id',
                        'organisation_name',
                        'unclaimed',
                        'lead_applicant_email'
                    );
                },
            ]
        )
        ->whereHas(
            'registryHasAffiliations',
            function ($query) use ($registryId) {
                $query->where('registry_id', $registryId);
            }
        )
        ->paginate((int) $this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $affiliations,
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/affiliations/{registryId}",
     *      summary="Create an Affiliation entry",
     *      description="Create an Affiliation entry",
     *      tags={"Affiliations"},
     *      summary="Affiliations@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="registry_id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Affiliation definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Affiliation"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Affiliation"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function storeByRegistryId(Request $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $affiliation = Affiliation::create([
                'organisation_id' => $input['organisation_id'],
                'member_id' => $request['member_id'],
                'relationship' => $input['relationship'],
                'from' => $input['from'],
                'to' => $input['to'],
                'department' => $input['department'],
                'role' => $input['role'],
                'email' => $input['email'],
                'ror' => $input['ror'],
                'registry_id' => $registryId,
            ]);

            RegistryHasAffiliation::create([
                'registry_id' => $registryId,
                'affiliation_id' => $affiliation->id,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $affiliation->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/affiliations/{id}",
     *      summary="Update an Affiliation entry",
     *      description="Update an Affiliation entry",
     *      tags={"Affiliations"},
     *      summary="Affiliations@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Affiliation definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Affiliation"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Affiliation"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->only(app(Affiliation::class)->getFillable());
            $affiliation = Affiliation::findOrFail($id);
            $affiliation->update($input);


            return response()->json([
                'message' => 'success',
                'data' => $affiliation,
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/affiliations/{id}",
     *      summary="Edit an Affiliation entry",
     *      description="Edit an Affiliation entry",
     *      tags={"Affiliations"},
     *      summary="Affiliations@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Affiliation definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Affiliation"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Affiliation"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        try {

            $affiliation = Affiliation::where('id', $id)->first();

            $input = $request->only(app(Affiliation::class)->getFillable());
            $affiliation->update($input);

            return response()->json([
                'message' => 'success',
                'data' => $affiliation,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/training/{id}",
     *      summary="Delete a affiliation entry from the system by ID",
     *      description="Delete a affiliation entry from the system",
     *      tags={"Affiliation"},
     *      summary="Affiliation@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            Affiliation::where('id', $id)->first()->delete();

            RegistryHasAffiliation::where(
                'affiliation_id',
                $id
            )->delete();

            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
