<?php

namespace App\Http\Middleware;

use Exception;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $token = $request->bearerToken() ?? $request->query('token');

        // If we have a token, try to authenticate
        if ($token) {
            try {
                $request->headers->set('Authorization', 'Bearer ' . $token);
                
                if (Auth::guard($guard)->check()) {
                    return $next($request);
                }
            } catch (Exception $e) {
                throw $e;
            }
        }

        if (Auth::guard('web')->check() || Auth::guard($guard)->check()) {
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
