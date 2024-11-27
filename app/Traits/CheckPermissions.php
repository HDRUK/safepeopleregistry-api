<?php

namespace App\Traits;

use Auth;
use Illuminate\Http\Request;

trait CheckPermissions
{
    public function hasGroups(Request $request, array $permissionsRequired): bool
    {
        // Work on the basis that absolutely everyone has no access to do anything
        // at the start.
        $hasAccess = false;

        if (!$request->header('Authorization')) {
            return false;
        }

        $token = Auth::token();
        $arr = json_decode($token, true);

        foreach ($arr['realm_access']['roles'] as $value) {
            if (in_array($value, $permissionsRequired)) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return false;
        }

        return true;
    }
}
