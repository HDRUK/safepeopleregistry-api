<?php

namespace App\Http\Middleware;

use Closure;
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
        if ($request->is('horizon') || $request->is('horizon/*')) {
            return $this->handleHorizonAuth($request, $next, $guards);
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Handle Horizon authentication via the standard Keycloak guard.
     * Token must be supplied as a Bearer header — never via a URL query parameter.
     */
    protected function handleHorizonAuth(Request $request, Closure $next, array $guards)
    {
        $guard = $guards[0] ?? 'api';

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
