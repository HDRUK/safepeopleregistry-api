<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
        /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (config('app.env') !== 'prod') {
            return parent::handle($request, $next, ...$guards);
        }

        // Check if this is a Horizon request
        if ($request->is('horizon') || $request->is('horizon/*')) {
            return $this->handleHorizonAuth($request, $next, $guards);
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Handle Horizon authentication with JWT support
     */
    protected function handleHorizonAuth(Request $request, Closure $next, array $guards)
    {
        $guard = $guards[0] ?? 'api';

        $token = null;
        if ($request->has('token')) {
            $token = $request->query('token');
        }

        if ($token) {
            try {
                $request->headers->set('Authorization', 'Bearer ' . $token);

                $user = $this->getUserFromToken($token);
                
                if ($user) {
                    session([
                        'horizon_authenticated' => true,
                        'horizon_user_id' => $user->id,
                    ]);

                    Auth::setUser($user);
                    return $next($request);
                }

            } catch (Exception $e) {
                throw $e;
            }
        }

        if (session()->has('horizon_authenticated') && session('horizon_authenticated') === true) {
            $userId = session('horizon_user_id');
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    Auth::setUser($user);
                    return $next($request);
                } else {
                    session()->forget(['horizon_authenticated', 'horizon_user_id']);
                }
            }
        }

        if (Auth::guard($guard)->check()) {
            return $next($request);
        }

        return redirect()->away(config('speedi.system.portal_url'));
    }

    protected function getUserFromToken(string $token): ?User
    {
        // Decode JWT payload
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            
            if (!$payload || !isset($payload['sub'])) {
                return null;
            }

            $userId = $payload['sub'];
            
            return User::where('keycloak_id', $userId)->first();
            
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : redirect()->away(config('speedi.system.portal_url'));
    }
}
