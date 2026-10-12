<?php

namespace Tests\Unit;

use App\Enums\OrganisationRole;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrganisationRoleTest extends TestCase
{
    public static function organisationUsers(): array
    {
        return [
            'sro only' => [1, 0, OrganisationRole::Sro],
            'sro and delegate' => [1, 1, OrganisationRole::Sro],
            'delegate only' => [0, 1, OrganisationRole::Delegate],
            'neither flag' => [0, 0, OrganisationRole::Delegate],
        ];
    }

    public static function nonOrganisationUsers(): array
    {
        $cases = [];

        foreach ([User::GROUP_USERS, User::GROUP_ADMINS, User::GROUP_CUSTODIANS] as $group) {
            foreach ([[1, 0], [1, 1], [0, 1], [0, 0]] as [$isSro, $isDelegate]) {
                $cases["{$group} sro={$isSro} delegate={$isDelegate}"] = [$group, $isSro, $isDelegate];
            }
        }

        return $cases;
    }

    #[DataProvider('organisationUsers')]
    public function test_it_derives_the_organisation_role_from_the_user_flags(
        int $isSro,
        int $isDelegate,
        OrganisationRole $expected
    ): void {
        $user = new User([
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
        ]);

        $this->assertSame($expected, $user->organisationRole());
    }

    #[DataProvider('nonOrganisationUsers')]
    public function test_it_has_no_organisation_role_outside_the_organisations_group(
        string $group,
        int $isSro,
        int $isDelegate
    ): void {
        $user = new User([
            'user_group' => $group,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
        ]);

        $this->assertNull($user->organisationRole());
    }
}
