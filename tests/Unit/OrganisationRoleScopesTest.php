<?php

namespace Tests\Unit;

use App\Enums\OrganisationRole;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Tests\TestCase;

class OrganisationRoleScopesTest extends TestCase
{
    #[DataProviderExternal(OrganisationRoleTest::class, 'organisationUsers')]
    public function test_scopes_select_organisation_users_by_role(
        int $isSro,
        int $isDelegate,
        OrganisationRole $expected
    ): void {
        $user = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
        ]);

        $this->assertSame($expected === OrganisationRole::Sro, User::sros()->whereKey($user->id)->exists());
        $this->assertSame($expected === OrganisationRole::Delegate, User::delegates()->whereKey($user->id)->exists());
    }

    #[DataProviderExternal(OrganisationRoleTest::class, 'nonOrganisationUsers')]
    public function test_scopes_exclude_users_outside_the_organisations_group(
        string $group,
        int $isSro,
        int $isDelegate
    ): void {
        $user = User::factory()->create([
            'user_group' => $group,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
        ]);

        $this->assertFalse(User::sros()->whereKey($user->id)->exists());
        $this->assertFalse(User::delegates()->whereKey($user->id)->exists());
    }

    public function test_scopes_agree_with_organisation_role_for_every_user(): void
    {
        $users = User::all();

        $expectedSroIds = $users
            ->filter(fn (User $user) => $user->organisationRole() === OrganisationRole::Sro)
            ->pluck('id')
            ->sort()
            ->values()
            ->all();
        $expectedDelegateIds = $users
            ->filter(fn (User $user) => $user->organisationRole() === OrganisationRole::Delegate)
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $this->assertNotEmpty($expectedSroIds);
        $this->assertNotEmpty($expectedDelegateIds);
        $this->assertSame($expectedSroIds, User::sros()->orderBy('id')->pluck('id')->all());
        $this->assertSame($expectedDelegateIds, User::delegates()->orderBy('id')->pluck('id')->all());
    }
}
