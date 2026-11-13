<?php

namespace App\Observers;

use App\Enums\CustodianRole;
use App\Exceptions\CustodianRoleConflictException;
use App\Models\CustodianUserHasPermission;
use App\Models\Permission;

class CustodianUserHasPermissionObserver
{
    /**
     * Handle the CustodianUserHasPermission "creating" event.
     *
     * Custodian roles are mutually exclusive. Every row in this table is created through the
     * model, so refusing a second role here covers controllers, seeders and anything else.
     * Rows that aren't roles (e.g. CUSTODIAN_KEYCARD_*) are unaffected.
     *
     * @throws CustodianRoleConflictException
     */
    public function creating(CustodianUserHasPermission $row): void
    {
        $permission = Permission::find($row->permission_id);

        if (! $permission || CustodianRole::tryFrom($permission->name) === null) {
            return;
        }

        $holdsAnotherRole = CustodianUserHasPermission::query()
            ->where('custodian_user_id', $row->custodian_user_id)
            ->where('permission_id', '!=', $permission->id)
            ->whereHas('permission', fn ($query) => $query->whereIn('name', CustodianRole::permissionNames()))
            ->exists();

        if ($holdsAnotherRole) {
            throw new CustodianRoleConflictException();
        }
    }
}
