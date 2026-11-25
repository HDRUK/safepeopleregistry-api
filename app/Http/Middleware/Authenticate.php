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
        $horizonPath = trim(config('horizon.path'), '/');

        if ($request->is($horizonPath) || $request->is($horizonPath . '/*')) {
            return $this->handleHorizonAuth($request, $next, $guards);
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Handle Horizon authentication via a server-side session (web guard).
     *
     * Browsers can't send a Keycloak Bearer header, so the session is created by
     * the Keycloak authorization-code login in HorizonAuthController. Tokens are
     * never accepted via a URL query parameter.
     */
    protected function handleHorizonAuth(Request $request, Closure $next, array $guards)
    {
        $guard = $guards[0] ?? 'web';

        if (Auth::guard($guard)->check()) {
            Auth::shouldUse($guard);

            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(401);
        }

        return redirect()->guest(route('horizon.login'));
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : redirect()->away(config('speedi.system.portal_url'));
    }
}
