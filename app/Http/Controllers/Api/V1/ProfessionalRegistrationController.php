<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalRegistration;
use App\Models\RegistryHasProfessionalRegistration;
use App\Http\Requests\ProfessionalRegistrations\DeleteProfessionRegistration;
use App\Http\Requests\ProfessionalRegistrations\UpdateProfessionRegistration;
use App\Http\Requests\ProfessionalRegistrations\GetProfessionRegistrationByRegistry;
use App\Http\Requests\ProfessionalRegistrations\CreateProfessionRegistrationByRegistry;

class ProfessionalRegistrationController extends Controller
{
    use CommonFunctions;

    //Hide from swagger
    public function indexByRegistryId(GetProfessionRegistrationByRegistry $request, int $registryId): JsonResponse
    {
        $professionalRegistrations = ProfessionalRegistration::withWhereHas(
            'registryHasProfessionalRegistrations',
            function ($query) use ($registryId) {
                $query->where('registry_id', '=', $registryId);
            }
        )
        ->paginate((int) $this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $professionalRegistrations,
        ], 200);
    }

    //Hide from swagger
    public function storeByRegistryId(CreateProfessionRegistrationByRegistry $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            if (isset($input['name']) && isset($input['member_id'])) {
                $professionalRegistration = ProfessionalRegistration::create([
                    'name' => $input['name'],
                    'member_id' => $input['member_id'],
                ]);

                RegistryHasProfessionalRegistration::create([
                    'registry_id' => $registryId,
                    'professional_registration_id' => $professionalRegistration->id,
                ]);

                return response()->json([
                    'message' => 'success',
                    'data' => $professionalRegistration,
                ], 201);
            }

            return response()->json([
                'message' => 'failed',
                'data' => 'all fields were defined'
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/professional_registrations/{id}",
     *      summary="Update a Professional Registrations entry",
     *      description="Update a Professional Registrations entry",
     *      tags={"Professional Registrations"},
     *      summary="Professional Registrations@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Professional Registrations entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Professional Registrations entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Professional Registrations definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="member_id", type="string", example="ABC1234"),
     *              @OA\Property(property="name", type="string", example="ONS"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="member_id", type="string", example="ABC1234"),
     *                  @OA\Property(property="name", type="string", example="ONS"),
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
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
    public function update(UpdateProfessionRegistration $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            if (isset($input['name']) && isset($input['member_id'])) {
                $professionalRegistration = ProfessionalRegistration::where('id', $id)->first();

                $professionalRegistration->name = $input['name'];
                $professionalRegistration->member_id = $input['member_id'];

                $professionalRegistration->save();

                return response()->json([
                    'message' => 'success',
                    'data' => $professionalRegistration,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => 'not all fields were defined'
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger
    public function destroy(DeleteProfessionRegistration $request, int $id): JsonResponse
    {
        try {
            ProfessionalRegistration::where('id', $id)->first()->delete();

            RegistryHasProfessionalRegistration::where([
                'professional_registration_id' => $id,
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
