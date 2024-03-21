<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $this->middleware('auth:api', [
            'except' => [
                'login'
            ]
        ]);
    }

    /**
     * Get a JWT via given credentials.
     * 
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = request([
            'email',
            'password'
        ]);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'error' => 'Unauthorised',
            ], 401);
        }

        if (auth()->user()->uses_2fa) {
            if ($request->session()->has('2fa_passed')) {
                $request->session()->forget('2fa_passed');
            }

            $request->session()->put('2fa:user:id', auth()->user()->id);
            $request->session()->put('2fa:auth:attempt', true);
            $request->session()->put('2fa:auth:remember', $request->has('remember'));
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     * 
     * @return JsonResponse
     */
    public function me()
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
