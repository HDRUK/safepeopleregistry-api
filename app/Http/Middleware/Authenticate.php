<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
        /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Check if this is a Horizon request
        if ($request->is('horizon') || $request->is('horizon/*')) {
            return $this->handleHorizonAuth($request, $next, $guards);
        }

        // Default authentication handling
        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Handle Horizon authentication with JWT support
     */
    protected function handleHorizonAuth(Request $request, Closure $next, array $guards)
    {
        $guard = $guards[0] ?? 'api';

        // Try bearer token from header
        $token = $request->bearerToken();
        Log::info('Authenticate', [
            'request' => $request,
            'guard' => $guard,
        ]);
        
        // Fallback to query parameter
        if (!$token) {
            $token = $request->query('token');
        }

        Log::info('Authenticate', [
            'token' => $token,
            'guard' => $guard,
            'request' => $request,
        ]);

        // If we have a token, try to authenticate
        if ($token) {
            try {
                $request->headers->set('Authorization', 'Bearer ' . $token);
                
                Log::info('Authenticate', [
                    'token' => $token,
                    'request' => $request,
                    'guard' => $guard,
                    'guardIsAuthenticated' => Auth::guard($guard)->check(),
                ]);
                
                if (Auth::guard($guard)->check()) {
                    return $next($request);
                }
            } catch (Exception $e) {
                throw $e;
            }
        }

        if (Auth::guard($guard)->check()) {
            return $next($request);
        }

        return redirect()->away(config('speedi.system.portal_url'));
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : redirect()->away(config('speedi.system.portal_url'));
    }
}
