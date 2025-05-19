<?php

namespace App\Http\Traits;

use Auth;
use App\Models\User;

trait ResolvesUser
{
    protected function getAuthenticatedUser(): ?User
    {
        $obj = json_decode(Auth::token(), true);

        if (isset($obj['sub'])) {
            $sub = $obj['sub'];
            return User::where('keycloak_id', $sub)->first();
        }

        return Auth::guard()->user();
    }

    protected function userIsInAnyGroup(User $user, array $groups): bool
    {
        return in_array($user->user_group, $groups, true);
    }
}
