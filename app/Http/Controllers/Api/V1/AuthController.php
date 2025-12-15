<?php

namespace App\Http\Controllers\Api\V1;

use Keycloak;
use Carbon\Carbon;
use App\Models\User;
use App\Models\State;
use App\Models\Affiliation;
use Illuminate\Http\Request;
use App\Models\PendingInvite;
use Illuminate\Http\Response;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RegistryManagementController as RMC;

class AuthController extends Controller
{
    use Responses;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function acceptInvite(int $id)
    {
        $pendingInvite = PendingInvite::where('user_id', $id)->first();

        if ($pendingInvite) {
            $pendingInvite->invite_accepted_at = Carbon::now();
            $pendingInvite->status = PendingInvite::STATE_COMPLETE;
            $pendingInvite->save();
        }

        return $pendingInvite;
    }

    public function registerKeycloakUser(Request $request): JsonResponse
    {
        $response = Keycloak::getUserInfo($request->headers->get('Authorization'));
        $payload = $response->json();

        $createdUser = RMC::createNewUser($payload, $request);

        if ($createdUser) {
            $userId = isset($createdUser['unclaimed_user_id']) ? $createdUser['unclaimed_user_id'] : $createdUser['user_id'];

            $user = User::where('id', $userId)->first();

            $user->setState(State::STATE_REGISTERED);

            if (isset($createdUser['unclaimed_user_id'])) {
                $pendingInvite = $this->acceptInvite($createdUser['unclaimed_user_id']);

                if ($pendingInvite) {
                    $registryId = $user->registry_id;
                    $organisationId = $pendingInvite->organisation_id;
                    $email = $user->email;

                    $existingAffiliation = Affiliation::where('organisation_id', $organisationId)
                        ->where('email', $email)
                        ->where('registry_id', $registryId)
                        ->first();

                    if (!$existingAffiliation && $user->user_group === User::GROUP_USERS) {
                        Affiliation::create([
                            'organisation_id' => $organisationId,
                            'member_id' => '',
                            'relationship' => null,
                            'from' => null,
                            'to' => null,
                            'department' => null,
                            'role' => null,
                            'email' => $email,
                            'ror' => null,
                            'registry_id' => $registryId,
                        ]);
                    }
                }


                return response()->json([
                    'message' => 'success',
                    'data' => $user,
                ], 201);
            }

            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 201);
        }

        return response()->json([
            'message' => 'failed',
            'data' => null,
        ], 400);
    }

    public function claimUser(Request $request, int $userId): JsonResponse
    {
        $response = Keycloak::getUserInfo($request->headers->get('Authorization'));
        $input = $response->json();

        $userToReplace = User::where('id', $userId)->first();

        if (!$userToReplace) {
            return response()->json([
                'message' => 'User not found',
                'data' => null,
            ], 400);
        }

        if ($userToReplace->user_group !== User::GROUP_ORGANISATIONS) {
            return response()->json([
                'message' => 'Only works for organisation admins ',
                'data' => null,
            ], 400);
        }

        if ($userToReplace->unclaimed === 0) {
            return response()->json([
                'message' => 'Account already claimed',
                'data' => null,
            ], 400);
        }

        $this->acceptInvite($userToReplace->id);

        $userToReplace->first_name = $input['given_name'];
        $userToReplace->last_name = $input['family_name'];
        $userToReplace->email = $input['email'];
        $userToReplace->keycloak_id = $input['sub'];
        $userToReplace->unclaimed = 0;
        $userToReplace->t_and_c_agreed = 1;
        $userToReplace->t_and_c_agreement_date = now();

        $userToReplace->save();


        return response()->json([
            'message' => 'success',
            'data' => $userToReplace
        ], 201);
    }

    public function meUnclaimed(Request $request): JsonResponse
    {
        $token = Auth::token();
        $input = $request->only(['invite_code']);

        if (!$token) {
            return $this->UnauthorisedResponse();
        }

        $arr = json_decode($token, true);

        if (!empty($input['invite_code'])) {
            $pendingInvite = PendingInvite::where([
                'invite_code' => $input['invite_code']
            ])->first();
            $user = User::where(['id' => $pendingInvite->user_id, 'unclaimed' => 1])->first();
        } elseif (isset($arr['email'])) {
            $user = User::where(['email' => $arr['email'], 'unclaimed' => 1])->first();
        }

        if (!isset($user)) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($user);
    }

    public function me(Request $request): JsonResponse
    {
        $token = Auth::token();

        if (!$token || $token === 'null') {
            return response()->json([
                'message' => 'unauthorised',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        $arr = json_decode($token, true);

        if (!isset($arr['sub'])) {
            return response()->json([
                'message' => 'not found',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        $user = User::where('keycloak_id', $arr['sub'])->first();

        //If unclaimed user and account type and just logged in

        if (!$user) {
            return response()->json([
                'message' => 'not found',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'success',
            'data' => $user,
        ], Response::HTTP_OK);
    }
}
