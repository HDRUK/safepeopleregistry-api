<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Custodian;

class CheckCrudAccess
{
    public function handle(Request $request, Closure $next, string $entityType,  ...$checks): mixed
    {
        $user = Auth::guard()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if($user->user_group === User::GROUP_ADMINS){
            return $next($request);
        }

        $resourceId = $request->route('id');

        if (in_array('group', $checks)) {
            if (!$this->hasGroupAccess($user, $entityType)) {
                return response()->json(['message' => 'Forbidden: group access denied' ], 403);
            }
        }
    
        if (in_array('owns', $checks)) {
            if (!$this->ownsResource($user, $entityType, $resourceId)) {
                return response()->json(['message' => 'Forbidden: entity is not owned by this user'], 403);
            }
        }

        return $next($request);
    }

    protected function hasGroupAccess($user, string $entityType): bool
    {
        return match ($entityType) {
            'custodian' => $user->user_group === User::GROUP_CUSTODIANS,
            'user' => true,
            'organisation' => $user->user_group === User::GROUP_ORGANISATIONS,
            default => false,
        };
    }

    protected function ownsResource($user, string $entityType, $routeId): bool
    {
        return match ($entityType) {
            'custodian' => (int)$user->custodian_user->custodian_id === (int)$routeId,
            'user' => (int)$user->id === (int)$routeId,
            'organisation' => (int)$user->organisation_id === (int)$routeId,
            default => false,
        };
    }
}
