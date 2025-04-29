<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Traits\ResolvesUser;

class IsCustodian
{
    use ResolvesUser;
    public function handle(Request $request, Closure $next, ...$checks): mixed
    {
        $user = $this->getAuthenticatedUser();
        if ($user->user_group === User::GROUP_CUSTODIANS) {
            return $next($request);
        }
        return response()->json(['Forbidden (not custodian)'], 403);
    }
}
