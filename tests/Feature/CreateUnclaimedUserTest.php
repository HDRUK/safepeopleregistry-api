<?php

namespace Tests\Feature;

use App\Models\User;
use RegistryManagementController as RMC;
use Tests\TestCase;

class CreateUnclaimedUserTest extends TestCase
{
    public function test_an_organisation_user_keeps_the_delegate_and_sro_flags_it_was_given(): void
    {
        $user = $this->createUnclaimedUser(User::GROUP_ORGANISATIONS, [
            'is_delegate' => 1,
            'is_sro' => 1,
        ]);

        $this->assertSame(1, $user->fresh()->is_delegate);
        $this->assertTrue($user->fresh()->is_sro);
    }

    public function test_an_organisation_user_is_neither_a_delegate_nor_an_sro_by_default(): void
    {
        $user = $this->createUnclaimedUser(User::GROUP_ORGANISATIONS);

        $this->assertSame(0, $user->fresh()->is_delegate);
        $this->assertFalse($user->fresh()->is_sro);
    }

    public function test_a_researcher_cannot_be_a_delegate_or_an_sro(): void
    {
        $user = $this->createUnclaimedUser(User::GROUP_USERS, [
            'is_delegate' => 1,
            'is_sro' => 1,
        ]);

        $this->assertSame(0, $user->fresh()->is_delegate);
        $this->assertFalse($user->fresh()->is_sro);
    }

    public function test_a_custodian_user_cannot_be_a_delegate_or_an_sro(): void
    {
        $user = $this->createUnclaimedUser(User::GROUP_CUSTODIANS, [
            'is_delegate' => 1,
            'is_sro' => 1,
        ]);

        $this->assertSame(0, $user->fresh()->is_delegate);
        $this->assertFalse($user->fresh()->is_sro);
    }

    private function createUnclaimedUser(string $userGroup, array $overrides = []): User
    {
        return RMC::createUnclaimedUser(array_merge([
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => fake()->unique()->safeEmail(),
            'user_group' => $userGroup,
            'organisation_id' => $userGroup === User::GROUP_ORGANISATIONS ? 1 : 0,
        ], $overrides));
    }
}
