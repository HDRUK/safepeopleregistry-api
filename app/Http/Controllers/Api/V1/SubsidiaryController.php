<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Subsidiary;
use App\Models\Organisation;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\OrganisationHasSubsidiary;
use App\Http\Requests\Subsidiaries\CreateSubsidiary;
use App\Http\Requests\Subsidiaries\DeleteSubsidiary;
use App\Http\Requests\Subsidiaries\UpdateSubsidiary;

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
     *         name="organisationId",
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
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
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function store(CreateSubsidiary $request, int $organisationId): JsonResponse
    {
        try {
            $input = $request->only(app(Subsidiary::class)->getFillable());
            $org = Organisation::findOrFail($organisationId);

            if (!Gate::allows('update', $org)) {
                return $this->ForbiddenResponse();
            }

            $array = [
                'name' => $input['name'],
                'address_1' => $input['address_1'] ?? null,
                'address_2' => $input['address_2'] ?? null,
                'town' => $input['town'] ?? null,
                'county' => $input['county'] ?? null,
                'country' => $input['country'] ?? null,
                'postcode' => $input['postcode'] ?? null,
                'website' => $input['website'] ?? null,
                'is_parent' => $input['is_parent'] ?? 0,
            ];

            $subsidiary = Subsidiary::create($array);

            OrganisationHasSubsidiary::create([
                'organisation_id' => $organisationId,
                'subsidiary_id' => $subsidiary->id
            ]);

            return $this->CreatedResponse($subsidiary->id);
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
     *         name="organisationId",
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
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
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function update(UpdateSubsidiary $request, int $subsidiaryId, int $organisationId): JsonResponse
    {
        try {
            $input = $request->only(app(Subsidiary::class)->getFillable());
            $org = Organisation::findOrFail($organisationId);

            if (!Gate::allows('update', $org)) {
                return $this->ForbiddenResponse();
            }

            $subsidiary = Subsidiary::findOrFail($subsidiaryId);
            $array = [
                'name' => $input['name'],
                'address_1' => $input['address_1'] ?? null,
                'address_2' => $input['address_2'] ?? null,
                'town' => $input['town'] ?? null,
                'county' => $input['county'] ?? null,
                'country' => $input['country'] ?? null,
                'postcode' => $input['postcode'] ?? null,
                'website' => $input['website'] ?? null,
                'is_parent' => $input['is_parent'] ?? 0,
            ];

            $subsidiary->update($array);

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
     *         name="organisationId",
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
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
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
    public function destroy(DeleteSubsidiary $request, int $subsidiaryId, int $organisationId): JsonResponse
    {
        try {
            $org = Organisation::findOrFail($organisationId);

            if (!Gate::allows('delete', $org)) {
                return $this->ForbiddenResponse();
            }

            Subsidiary::where('id', $subsidiaryId)->delete();

            return $this->OKResponse(null);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
