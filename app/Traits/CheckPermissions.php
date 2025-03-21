<?php

namespace App\Traits;

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

        // @phpstan-ignore-next-line
        $token = explode(' ', $request->header('Authorization'));
        $arr = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token[1])[1]))), true);

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
