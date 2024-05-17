<?php

namespace App\Http\Controllers\Api\V1;

use Seshac\Otp\Otp;

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

    /**
     * Spawns OAuth2 authentication flow via Keycloak OIDC.
     */
    public function loginKeycloak(Request $request)
    {
        return Socialite::driver('keycloak')->scopes(['openid'])->redirect();
    }

    /**
     * Governs callbacks from Keycloak once successful or failed auth
     * attempted.
     */
    public function loginKeycloakCallback(Request $request)
    {
        $user = Socialite::driver('keycloak')->stateless()->user();
        dd($user->token);
    }

    /**
     * Get a JWT via given credentials.
     * 
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $token = 'blah, blah';

        $input = $request->all();

        if (isset($input['step']) && $input['step'] === 'login') {
            $credentials = request([
                'email',
                'password',
            ]);

            $user = User::where('email', $credentials['email'])->first();
            if ($user) {
                $otp = Otp::setValidity(env('OTP_VALIDITY_MINUTES'))
                    ->setLength(env('OTP_LENGTH'))
                    ->setOnlyDigits(env('OTP_ONLY_DIGITS'))
                    ->generate($user->email);
                if ($otp->status) {
                    $user->otp = $otp->token;
                }
                if ($user->save()) {
                    $to = [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => ucwords($user->name),
                    ];

                    $template = EmailTemplate::where('identifier', '=', 'user_otp')->first();
                    SendEmailJob::dispatch($to, $template, []);

                    return response()->json([
                        'message' => 'otp_required',
                        'data' => null,
                    ], 200);
                }
            }
        }

        if (isset($input['step']) && $input['step'] === 'otp') {
            $verify = Otp::validate($input['email'], $input['otp']);
            if ($verify['status'] === true) {
                return $this->respondWithToken($token);
            }
        }

        // if (!$token = auth()->attempt($credentials)) {
        //     return response()->json([
        //         'error' => 'Unauthorised',
        //     ], 401);
        // }

        // return $this->repondWithOTP($token);

        // return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     * 
     * @return JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     * 
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'success',
        ], 200);
    }

    /**
     * Refresh a token
     * 
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure
     * 
     * @param string $token
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
