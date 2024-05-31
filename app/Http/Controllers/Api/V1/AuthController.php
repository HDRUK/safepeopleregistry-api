<?php

namespace App\Http\Controllers\Api\V1;

use Keycloak;

use App\Models\User;
use App\Jobs\SendEmailJob;
use Hdruk\LaravelMjml\Models\EmailTemplate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Laravel\Socialite\Facades\Socialite;

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
        // $this->middleware('auth:api', [
        //     'except' => [
        //         'login'
        //     ]
        // ]);
    }

    public function registerUser(Request $request): JsonResponse
    {
        $input = $request->all();
        if (Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_researcher' => true,
        ])) {
            $user = User::where('email', $input['email'])->first();
            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'failed',
            'data' => null,
        ]);
    }

    public function registerIssuer(Request $request): JsonResponse
    {
        $input = $request->all();
        if (Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_issuer' => true,
        ])) {
            $user = User::where('email', $input['email'])->first();
            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'failed',
            'data' => null,
        ]);
    }

    public function registerOrganisation(Request $request): JsonResponse
    {
        $input = $request->all();
        if (Keycloak::create([
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'is_organisation' => true,
        ])) {
            $user = User::where('email', $input['email'])->first();
            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'failed',
            'data' => null,
        ]);
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
            ], 200);
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
