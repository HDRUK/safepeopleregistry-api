<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Custodian;
use Illuminate\Support\Str;
use App\Http\Traits\Responses;
use App\Http\Traits\ResolvesUser;

class IsOwner
{
    use Responses;
    use ResolvesUser;

    public function handle(Request $request, Closure $next, ...$checks): mixed
    {
        $user = $this->getAuthenticatedUser();

        
        $entity = $request->segment(3); // /api/v1/<model>
        $model = Str::studly(Str::singular($entity));

        $idName = 'id';
        $id = (int)$request->route($idName);
        if(!$id){
            $entity = $request->segment(4); 
            $model = Str::studly(Str::singular($entity));
            $idName = $entity . 'Id';
            $id = (int)$request->route($idName);
        }
        
        $ownsResource = $this->ownsResource($user, $model, $id);
        if(!$ownsResource){
            return $this->ForbiddenResponse();
        }
        return $next($request);
    }

    protected function ownsResource($user, $modelName, $id): bool
    {
        $model = '\\App\\Models\\' . $modelName;
        $obj = $model::where('id',(int)$id)->select('id')->first();
        if (!$obj) {
            return false;
        }

        $group = $user->user_group;

        return match ($modelName) {
                'User' => (int)$user->id === (int)$obj->id,
                'Registry' => (int)$user->registry_id === (int)$obj->id,
                'Custodian' =>  $group === User::GROUP_CUSTODIANS &&  (int) ($user->custodian_id ?? $user->custodian_user->custodian_id) === (int)$obj->id,
                'Organisation' => $group === User::GROUP_ORGANISATIONS && $user->is_delegate === 0 && (int)$user->organisation_id === (int)$obj->id,
                default => false,
            };
    }


}
