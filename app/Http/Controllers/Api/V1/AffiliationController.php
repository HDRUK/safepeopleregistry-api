<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use TriggerEmail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\State;
use App\Models\Affiliation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

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
        $loggedInUserId = $request->user()->id;
        $loggedInUser = User::where('id', $loggedInUserId)->first();
        $isUserGroupOrg = (!is_null($loggedInUser) && $loggedInUser->user_group === 'ORGANISATIONS') ? true : false;

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

        if ($isUserGroupOrg) {
            $affiliations->getCollection()->each(function ($affiliation) use ($loggedInUser) {
                if ($affiliation->organisation_id !== $loggedInUser->organisation_id) {
                    $affiliation->setAttribute('member_id', '***');
                }
            });
        }

        return response()->json([
            'message' => 'success',
            'data' => $affiliations,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/affiliations/{registryId}/organisation/{organisationId}",
     *      operationId="getOrganisationAffiliation",
     *      summary="Return a specific organisation's affiliation by registry ID and organisation ID",
     *      description="Get a specific organisation's affiliation for a given registry",
     *      tags={"Affiliations"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="registryId",
     *          in="path",
     *          required=true,
     *          description="Registry ID",
     *          example=1,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="organisationId",
     *          in="path",
     *          required=true,
     *          description="Organisation ID",
     *          example=100,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=123),
     *                  @OA\Property(property="registry_id", type="integer", example=1),
     *                  @OA\Property(property="organisation_id", type="integer", example=100),
     *                  @OA\Property(property="model_state_id", type="integer", example=2),
     *                  @OA\Property(property="model_state", type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="state_id", type="integer", example=5),
     *                      @OA\Property(property="state", type="object",
     *                          @OA\Property(property="id", type="integer", example=5),
     *                          @OA\Property(property="name", type="string", example="Approved")
     *                      )
     *                  ),
     *                  @OA\Property(property="organisation", type="object",
     *                      @OA\Property(property="id", type="integer", example=100),
     *                      @OA\Property(property="organisation_name", type="string", example="Example Org"),
     *                      @OA\Property(property="unclaimed", type="boolean", example=false),
     *                      @OA\Property(property="lead_applicant_email", type="string", example="lead@example.org")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Affiliation not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Affiliation not found")
     *          )
     *      )
     * )
     */
    public function getOrganisationAffiliation(Request $request, int $registryId, int $organisationId): JsonResponse
    {
        $affiliation = Affiliation::with(
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
            ->where(
                [
                    'registry_id' => $registryId,
                    'organisation_id' => $organisationId
                ]
            )
            ->first();


        return response()->json([
            'message' => 'success',
            'data' => $affiliation,
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
            $input = $request->only(app(Affiliation::class)->getFillable());

            $array = [
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
                'current_employer' => $input['current_employer'] ?? false
            ];
            if ($input['current_employer']) {
                $array['verification_code'] = Str::uuid()->toString();
                $array['verification_sent_at'] = Carbon::now();
            }
            $affiliation = Affiliation::create($array);

            return response()->json([
                'message' => 'success',
                'data' => $affiliation->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger docs
    public function resendVerificationEmail(Request $request, int $id): JsonResponse
    {
        try {
            if (!Gate::allows('isAdmin', Affiliation::class)) {
                return $this->ForbiddenResponse();
            }

            $affiliation = Affiliation::where([
                'id' => $id,
                'current_employer' => true,
                'is_verified' => false,
            ])->first();

            if (is_null($affiliation)) {
                return $this->BadRequestResponse();
            }

            $array = [
                'is_verified' => 0,
                'verification_code' => Str::uuid()->toString(),
                'verification_sent_at' => Carbon::now(),
            ];

            Affiliation::where('id', $id)->update($array);

            $email = [
                'type' => 'AFFILIATION_VERIFIED',
                'to' => $affiliation->id,
                'by' => $affiliation->id,
                'for' => $affiliation->id,
                'identifier' => 'affiliation_user_professional_email_confirm',
            ];

            TriggerEmail::spawnEmail($email);

            // Logic to resend the verification email
            return $this->OKResponse('Verification email resent');
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
     *      path="/api/v1/affiliations/verify_email",
     *      summary="Update an Affiliation entry",
     *      description="Update an Affiliation entry with verification",
     *      tags={"Affiliations"},
     *      summary="Affiliations@verifyEmail",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="verification_code",
     *         in="path",
     *         description="Email verification code",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="string",
     *            description="Email verification code",
     *         ),
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
     *              @OA\Property(property="data", type="string", example="Affiliation email verified")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     *      @OA\Response(
     *          response=404,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function verifyEmail(Request $request, string $verificationCode): JsonResponse
    {
        try {
            $affiliation = Affiliation::where([
                    'verification_code' => $verificationCode,
                    'is_verified'       => 0,
                    'current_employer'  => 1,
                ])
                ->where('verification_sent_at', '>=', now()->subMinutes((int)config('speedi.system.otp_affiliation_validity_minutes')))
                ->first();

            if (is_null($affiliation)) {
                return $this->NotFoundResponse();
            }

            $array = [
                'verification_code' => null,
                'is_verified' => 1,
                'verification_confirmed_at' => Carbon::now(),
            ];

            Affiliation::where('id', $affiliation->id)->update($array);

            return $this->OKResponse($affiliation->id);
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

            if (!$affiliation->is_verified && $affiliation->current_employer && $status === 'approved') {
                return $this->ErrorResponse('Affiliation is not verified');
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

    public function getWorkflowStates(Request $request)
    {
        return $this->OKResponse(Affiliation::getAllStates());
    }

    public function getWorkflowTransitions(Request $request)
    {
        return $this->OKResponse(Affiliation::getTransitions());
    }
}
