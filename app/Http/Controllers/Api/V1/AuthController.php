<?php

namespace App\Http\Controllers\Api\V1;

use Keycloak;
use Exception;
use RegistryManagementController as RMC;
use App\Models\Organisation;
use App\Models\OrganisationDelegate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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
        $token = explode('Bearer ', $request->headers->get('Authorization'));
        $accountType = $request->get('account_type');

        $response = Keycloak::getUserInfo($request->headers->get('Authorization'));
        $payload = $response->json();

        if (RMC::createNewUser($payload, $accountType)) {
            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 201);
        }

        return response()->json([
            'message' => 'failed',
            'data' => 'user already exists',
        ], 400);
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

    public function registerUser(Request $request): JsonResponse
    {
        $input = $request->all();
        $retVal = Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_researcher' => true,
        ]);

        if ($retVal['success']) {
            $user = User::where('email', $input['email'])->first();

            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 201);
        }

        return response()->json([
            'message' => 'failed',
            'data' => $retVal['error'],
        ], 409); // Send a "CONFLICT" as record likely exists.);
    }

    public function registerCustodian(Request $request): JsonResponse
    {
        $input = $request->all();

        $retVal = Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_custodian' => true,
        ]);
        if ($retVal['success']) {
            $user = User::where('email', $input['email'])->first();

            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 201);
        }

        return response()->json([
            'message' => 'failed',
            'data' => $retVal['error'],
        ], 409); // Send a "CONFLICT" as record likely exists.
    }

    public function registerOrganisation(Request $request): JsonResponse
    {
        $input = $request->all();

        $retVal = Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_organisation' => true,
        ]);

        if ($retVal['success']) {
            $user = User::where('email', $input['email'])->first();
            $organisation = Organisation::create([
                'organisation_name' => $input['organisation_name'],
                'lead_applicant_organisation_email' => $input['lead_applicant_organisation_email'],
                'lead_applicant_organisation_name' => $input['lead_applicant_organisation_name'],
                'companies_house_no' => $input['companies_house_no'],
                'ce_certified' => $input['ce_certified'],
                'ce_certification_num' => $input['ce_certification_num'],
                'iso_27001_certified' => $input['iso_27001_certified'],
                'dsptk_ods_code' => $input['dsptk_ods_code'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'organisation_unique_id' => Str::random(40),
                'applicant_names' => '',
            ]);

            $user->organisation_id = $organisation->id;
            $user->save();

            if (isset($input['dpo_name']) && isset($input['dpo_email'])) {
                $parts = explode(' ', $input['dpo_name']);
                OrganisationDelegate::create([
                    'first_name' => $parts[0],
                    'last_name' => $parts[1],
                    'email' => $input['dpo_email'],
                    'is_dpo' => 1,
                    'is_hr' => 0,
                    'priority_order' => 0,
                    'organisation_id' => $organisation->id,
                ]);
            }

            if (isset($input['hr_name'])) {
                $parts = explode(' ', $input['hr_name']);
                OrganisationDelegate::create([
                    'first_name' => $parts[0],
                    'last_name' => $parts[1],
                    'email' => $input['hr_email'],
                    'is_dpo' => 0,
                    'is_hr' => 1,
                    'priority_order' => 0,
                    'organisation_id' => $organisation->id,
                ]);
            }

            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 201);
        }

        return response()->json([
            'message' => 'failed',
            'data' => $retVal['error'],
        ], 409); // Send a "CONFLICT" status, as a record likely exists
    }

    public function login(Request $request): JsonResponse
    {
        $input = $request->all();

        $return = Keycloak::login($input['email'], $input['password']);
        if ($return['status'] === 200) {
            $return['response']['user'] = $return['user'];

            return response()->json([
                'message' => 'success',
                'data' => $return['response'],
            ], 201);
        }

        return response()->json([
            'message' => 'failed',
            'data' => $return['response'],
        ], $return['status']);
    }

    public function logout(Request $request): JsonResponse
    {
        $input = $request->headers->all();
        $token = explode('Bearer ', $input['authorization'][0]);

        return response()->json([
            'message' => 'success',
            'data' => Keycloak::logout($token[1]),
        ]);
    }

    public function changePassword(Request $request, int $userId): JsonResponse
    {
        try {
            $input = $request->all();

            $user = User::where('id', $userId)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'failed',
                    'data' => null,
                    'error' => 'user not found',
                ], 404);
            }

            $response = Keycloak::changePassword($user->keycloak_id, $input['password']);
            if ($response->status() === 204) {
                return response()->json([
                    'message' => 'success',
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => $response->body(),
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
