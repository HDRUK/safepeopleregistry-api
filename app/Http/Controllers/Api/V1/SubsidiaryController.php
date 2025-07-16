<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationHasSubsidiary;
use App\Models\Subsidiary;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use Illuminate\Support\Facades\Gate;

class SubsidiaryController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Post(
     *      path="/api/v1/subsidiaries/organisations/{id}",
     *      summary="Create a subsidiary entry",
     *      description="Create a subsidiary entry",
     *      tags={"subsidiaries"},
     *      summary="subsidiaries@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="orgId",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="subsidiary definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Subsidiary",
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
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/Subsidiary",
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
    public function store(Request $request, int $orgId): JsonResponse
    {
        try {
            $input = $request->only(app(Subsidiary::class)->getFillable());
            $org = Organisation::findOrFail($orgId);

            if (!Gate::allows('update', $org)) {
                return $this->ForbiddenResponse();
            }

            $subsidiary = $this->addSubsidiary($orgId, $input);

            return $this->CreatedResponse($subsidiary);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/subsidiaries/{subsidiaryId}/organisations/{orgId}",
     *      summary="Update a subsidiary entry",
     *      description="Update a subsidiary entry",
     *      tags={"subsidiaries"},
     *      summary="subsidiaries@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="orgId",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="subsidiaryId",
     *         in="path",
     *         description="subsidiary entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="subsidiary entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="subsidiary definition",
     *          @OA\JsonContent(
     *                  ref="#/components/schemas/Subsidiary",
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
     *                  ref="#/components/schemas/Subsidiary",
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
    public function update(Request $request, int $subsidiaryId, int $orgId): JsonResponse
    {
        try {
            $input = $request->only(app(Subsidiary::class)->getFillable());
            $org = Organisation::findOrFail($orgId);

            if (!Gate::allows('update', $org)) {
                return $this->ForbiddenResponse();
            }

            $subsidiary = $this->addSubsidiary($orgId, $input);

            return $this->OKResponse($subsidiary);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/subsidiaries/{subsidiaryId}/organisations/{orgId}",
     *      summary="Delete an subsidiary entry from the system by ID",
     *      description="Delete an subsidiary entry from the system",
     *      tags={"subsidiaries"},
     *      summary="subsidiaries@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="orgId",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="subsidiaryId",
     *         in="path",
     *         description="subsidiary entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="subsidiary entry ID",
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
    public function destroy(Request $request, int $subsidiaryId, int $orgId): JsonResponse
    {
        try {
            $subsidiary = Subsidiary::findOrFail($subsidiaryId);
            $org = Organisation::findOrFail($orgId);

            if (!Gate::allows('delete', $org)) {
                return $this->ForbiddenResponse();
            }

            $subsidiary->delete();

            return $this->OKResponse(null);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function addSubsidiary(int $organisationId, array $subsidiary)
    {
        if (is_null($subsidiary['name'])) {
            return;
        }

        $subsidiaryData = [
            'name' => $subsidiary['name'],
        ];

        $subsidiaryValues = [
            'address_1' => $subsidiary['address_1'] ?? null,
            'address_2' => $subsidiary['address_2'] ?? null,
            'town' => $subsidiary['town'] ?? null,
            'county' => $subsidiary['county'] ?? null,
            'country' => $subsidiary['country'] ?? null,
            'postcode' => $subsidiary['postcode'] ?? null,
            'website' => $subsidiary['website'] ?? null,
        ];

        $subsidiary = Subsidiary::updateOrCreate($subsidiaryData, $subsidiaryValues);

        OrganisationHasSubsidiary::updateOrCreate(
            [
                'organisation_id' => $organisationId,
                'subsidiary_id' => $subsidiary->id
            ]
        );

        return $subsidiary;
    }
}
