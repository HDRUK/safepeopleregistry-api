<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Affiliation;
use App\Models\State;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;
use App\Http\Traits\Responses;

class AffiliationController extends Controller
{
    use CommonFunctions;
    use Responses;

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
        $affiliations = Affiliation::with(
            [
                'modelState.state',
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
            ->where(['registry_id' => $registryId])
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

            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateRegistryAffiliation(Request $request, int $registryId, int $affiliationId): JsonResponse
    {
        try {

            $validated = $request->validate([
                'status' => 'required|string|in:approved,rejected',
            ]);

            $status = strtolower($validated['status']);

            $affiliation = Affiliation::where(
                [
                    'registry_id' => $registryId,
                    'id' => $affiliationId
                ]
            )->first();
            if (!$affiliation) {
                return $this->NotFoundResponse();
            }

            $statusSlugMap = [
                'approved' => State::STATE_AFFILIATION_APPROVED,
                'rejected' => State::STATE_AFFILIATION_REJECTED,
            ];

            if (!array_key_exists($status, $statusSlugMap)) {
                return $this->ErrorResponse('Unknown status');
            }

            $newStateSlug = $statusSlugMap[$status];

            if (!$affiliation->canTransitionTo($newStateSlug)) {
                return $this->ErrorResponse(
                    'Invalid state transition. ' .
                        $affiliation->getState() .
                        ' => ' . $newStateSlug
                );
            }

            $affiliation->transitionTo($newStateSlug);

            return $this->OKResponse($affiliation->getState());
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
