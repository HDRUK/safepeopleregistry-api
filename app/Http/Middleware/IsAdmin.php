<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Traits\ResolvesUser;

class IsAdmin
{
    use ResolvesUser;
    public function handle(Request $request, Closure $next, ...$checks): mixed
    {
        $user = $this->getAuthenticatedUser();

        if ($user->user_group === User::GROUP_ADMINS) {
            return $next($request);
        }
        return response()->json(['Forbidden (not admin)'], 403);
    }
}
