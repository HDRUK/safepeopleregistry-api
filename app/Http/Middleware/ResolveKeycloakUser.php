<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ResolveKeycloakUser
{
    public function handle(Request $request, Closure $next)
    {
        $obj = json_decode(Auth::token(), true);
        if ($obj) {
            if (isset($obj['sub'])) {
                $sub = $obj['sub'];
                $user = User::where('keycloak_id', $sub)->first();
                if ($user) {
                    Auth::setUser($user);
                }
            }
        }

        return $next($request);
    }
}
