<?php

namespace App\Enums;

/**
 * Roles a Custodian User can hold. Each value is the name of its row in `permissions`.
 * A Custodian User holds at most one role (enforced by CustodianUserHasPermissionObserver).
 * Other permissions, such as CUSTODIAN_KEYCARD_*, can sit alongside a role.
 */
enum CustodianRole: string
{
    case Admin = 'CUSTODIAN_ADMIN';
    case Approver = 'CUSTODIAN_APPROVER';
    case ReadOnly = 'CUSTODIAN_READ_ONLY';

    /**
     * Permission names of every role, for queries such as whereIn.
     *
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}
