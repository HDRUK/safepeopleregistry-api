<?php

namespace Tests\Traits;

use App\Enums\CustodianRole;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;

trait CustodianActors
{
    protected function grantCustodianAdmin(CustodianUser $custodianUser): void
    {
        $permission = Permission::where('name', CustodianRole::Admin->value)->firstOrFail();

        CustodianUserHasPermission::firstOrCreate([
            'custodian_user_id' => $custodianUser->id,
            'permission_id' => $permission->id,
        ]);
    }

    /**
     * Create a User + linked CustodianUser in the given custodian, holding the given permission.
     *
     * @return array{0: User, 1: CustodianUser}
     */
    protected function makeCustodianUserActor(int $custodianId, string $permissionName): array
    {
        $custodianUser = CustodianUser::factory()->create(['custodian_id' => $custodianId]);
        $user = User::factory()->create([
            'user_group' => User::GROUP_CUSTODIANS,
            'email' => $custodianUser->email,
            'keycloak_id' => (string) Str::uuid(),
            'custodian_user_id' => $custodianUser->id,
            'unclaimed' => 0,
        ]);

        $permission = Permission::where('name', $permissionName)->firstOrFail();
        CustodianUserHasPermission::create([
            'custodian_user_id' => $custodianUser->id,
            'permission_id' => $permission->id,
        ]);

        return [$user, $custodianUser];
    }
}
