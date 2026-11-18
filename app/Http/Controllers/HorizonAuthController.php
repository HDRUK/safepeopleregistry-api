<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

/**
 * Keycloak authorization-code login for the Horizon dashboard.
 *
 * Creates a server-side session on the `web` guard so a browser can reach
 * Horizon without a Bearer header. Access to the dashboard itself is still
 * decided by the `viewHorizon` gate. See docs/ARCHITECTURE/HORIZON_DASHBOARD_AUTH.md.
 */
class HorizonAuthController extends Controller
{
    public function login(): SymfonyRedirectResponse
    {
        return $this->keycloak()->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        // Socialite validates the OAuth `state` against the session (CSRF).
        $keycloakUser = $this->keycloak()->user();

        $user = User::where('keycloak_id', $keycloakUser->getId())->first();

        // Only users allowed to view Horizon get a session at all; everyone
        // else is stopped here rather than being signed in and then refused.
        if (! $user || Gate::forUser($user)->denies('viewHorizon')) {
            abort(403);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended('/' . trim(config('horizon.path'), '/'));
    }

    /**
     * The Keycloak driver with its redirect URL pointed at the Horizon callback.
     *
     * Socialite::driver() is typed as the Provider contract, which has no
     * redirectUrl(); the Keycloak provider is an OAuth2 AbstractProvider.
     */
    private function keycloak(): AbstractProvider
    {
        $driver = Socialite::driver('keycloak');

        if (! $driver instanceof AbstractProvider) {
            throw new \LogicException('Keycloak Socialite driver must be an OAuth2 provider.');
        }

        return $driver->redirectUrl(route('horizon.callback'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(config('speedi.system.portal_url'));
    }
}
