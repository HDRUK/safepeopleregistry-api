<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Custodian;
use Illuminate\Support\Str;

class CheckCrudAccess
{
    public function handle(Request $request, Closure $next, ...$checks): mixed
    {
        $obj = json_decode(Auth::token(),true);
        $user = null;
        if (isset($obj['sub'])) {
            $sub = $obj['sub'];
            $user = User::where('keycloak_id', $sub)->first();
        } else {
            $user = Auth::guard()->user();
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if($user->user_group === User::GROUP_ADMINS){
            return $next($request);
        }

        $resourceId = $request->route('id');
        $entity = $request->segment(3); // /api/v1/<model>
        $model = Str::studly(Str::singular($entity));

        $hasGroupAccess = false;
        $ownsResource = false;
        $adminsResource = false;

        $groupCheck = collect($checks)->first(fn($c) => str_starts_with($c, 'group='));
        if ($groupCheck) {
            $hasGroupAccess = $this->hasGroupAccess($user, $groupCheck);
        }

        $checkOwns = collect($checks)->first(fn($c) => str_starts_with($c, 'owns'));
        if($checkOwns){
            $ownsResource = $this->ownsResource($user, $resourceId);
        }

        $checkAdmin = collect($checks)->first(fn($c) => str_starts_with($c, 'admin'));
        if($checkAdmin){
            $adminsResource = $this->adminsResource($user, $model, $resourceId);
        }

        if (!$hasGroupAccess && !$ownsResource &&  !$adminsResource) {
            return response()->json(['message' => 'Forbidden: insufficient access rights'], 403);
        }

        return $next($request);
    }

    protected function hasGroupAccess($user, string $groupString): bool
    {
        $groups = $this->getValues($groupString, 'group');
        return in_array($user->user_group, $groups);
    }

    protected function ownsResource($user, $routeId): bool
    {
        $group = $user->user_group;
        return match ($group) {
            User::GROUP_CUSTODIANS => (int)$user->custodian_user->custodian_id === (int)$routeId,
            User::GROUP_USERS  => (int)$user->id === (int)$routeId,
            User::GROUP_ORGANISATIONS => (int)$user->organisation_id === (int)$routeId,
            default => false,
        };
    }

    protected function adminsResource($user, $modelName, $routeId): bool
    {
        $model = '\\App\\Models\\' . $modelName;
        $obj = $model::find((int)$routeId);
        if (!$obj) {
            return false;
        }

        $group = $user->user_group;

        return match ($group) {
            User::GROUP_ORGANISATIONS => match ($modelName) {
                'User' => (int)$user->organisation_id === (int)$obj->organisation_id,
                default => false,
            },
            default => false,
        };
    }

    private function getValues(string $orginial, string $key): array
    {
        $orginial = substr($orginial, strlen($key.'='));
        return explode('|', $orginial);
    }
}
