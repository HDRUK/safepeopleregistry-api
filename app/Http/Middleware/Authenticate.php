<?php

namespace App\Http\Middleware;

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
        
        // Fallback to query parameter
        if (!$token) {
            $token = $request->query('token');
        }

        // If we have a token, try to authenticate
        if ($token) {
            try {
                // Set the token in the request for your JWT middleware/guard
                $request->headers->set('Authorization', 'Bearer ' . $token);
                
                // Attempt to authenticate
                if (Auth::guard($guard)->check()) {
                    return $next($request);
                }
            } catch (\Exception $e) {
                // Token validation failed, continue to redirect
            }
        }

        // Check if already authenticated via session
        if (Auth::guard('web')->check() || Auth::guard($guard)->check()) {
            return $next($request);
        }

        // Not authenticated, redirect to portal
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
