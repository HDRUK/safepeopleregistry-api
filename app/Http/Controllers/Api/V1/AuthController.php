<?php

namespace App\Http\Controllers\Api\V1;

use Keycloak;
use RegistryManagementController as RMC;
use Carbon\Carbon;
use App\Models\PendingInvite;
use App\Models\User;
use App\Models\Affiliation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function registerKeycloakUser(Request $request): JsonResponse
    {

        $response = Keycloak::getUserInfo($request->headers->get('Authorization'));
        $payload = $response->json();

        $user = RMC::createNewUser($payload, $request);

        if ($user) {
            if (isset($user['unclaimed_user_id'])) {
                $unclaimedUser = User::where('id', $user['unclaimed_user_id'])->first();
                $pendingInvite = PendingInvite::where('user_id', $user['unclaimed_user_id'])->first();
                if ($pendingInvite) {

                    $registryId = $unclaimedUser->registry_id;
                    $organisationId = $pendingInvite->organisation_id;
                    $email = $unclaimedUser->email;

                    $existingAffiliation = Affiliation::where('organisation_id', $organisationId)
                        ->where('email', $email)
                        ->where('registry_id', $registryId)
                        ->first();

                    if (!$existingAffiliation && $unclaimedUser->user_group === User::GROUP_USERS) {
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
                    $pendingInvite->invite_accepted_at = Carbon::now();
                    $pendingInvite->status = config('speedi.invite_status.COMPLETE');
                    $pendingInvite->save();
                }


                return response()->json([
                    'message' => 'success',
                    'data' => $unclaimedUser,
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

    public function claimUser(Request $request): JsonResponse
    {
        $response = Keycloak::getUserInfo($request->headers->get('Authorization'));
        $input = $response->json();
        $registryId = $request->input('registry_id');

        $userToReplace = User::where('registry_id', $registryId)->first();

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

    public function me(Request $request): JsonResponse
    {
        $token = Auth::token();
        if (!$token) {
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
