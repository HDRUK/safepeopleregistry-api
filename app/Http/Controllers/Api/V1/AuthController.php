<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationDelegate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Keycloak;

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

    public function registerUser(Request $request): JsonResponse
    {
        $input = $request->all();
        $retVal = Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_researcher' => true,
            'organisation_id' => isset($input['organisation_id']) ? $input['organisation_id'] : null,
            'consent_scrape' => $input['consent_scrape'],
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

    public function registerIssuer(Request $request): JsonResponse
    {
        $input = $request->all();

        $retVal = Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_issuer' => true,
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
}
