<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationDelegatesTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    private function createOrganisationUser(Organisation $organisation, int $isSro, int $isDelegate): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isDelegate,
        ]);
    }

    public function test_get_delegates_lists_organisation_users_who_are_not_sros(): void
    {
        $organisation = Organisation::factory()->create();
        $this->createOrganisationUser($organisation, isSro: 1, isDelegate: 0);
        $this->createOrganisationUser($organisation, isSro: 1, isDelegate: 1);
        $delegateOnly = $this->createOrganisationUser($organisation, isSro: 0, isDelegate: 1);
        $neitherFlag = $this->createOrganisationUser($organisation, isSro: 0, isDelegate: 0);

        $otherOrganisation = Organisation::factory()->create();
        $this->createOrganisationUser($otherOrganisation, isSro: 0, isDelegate: 1);

        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$organisation->id}/delegates");

        $response->assertStatus(200);
        $this->assertEqualsCanonicalizing(
            [$delegateOnly->id, $neitherFlag->id],
            $response->json('data.*.id')
        );
    }

    public function test_has_delegates_filter_treats_neither_flag_as_delegate_and_both_flags_as_sro(): void
    {
        $withNeitherFlag = Organisation::factory()->create();
        $this->createOrganisationUser($withNeitherFlag, isSro: 0, isDelegate: 0);

        $withBothFlags = Organisation::factory()->create();
        $this->createOrganisationUser($withBothFlags, isSro: 1, isDelegate: 1);

        $withDelegates = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . '?has_delegates=1&per_page=1000')
            ->assertStatus(200)
            ->json('data.data.*.id');

        $withoutDelegates = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . '?has_delegates=0&per_page=1000')
            ->assertStatus(200)
            ->json('data.data.*.id');

        $this->assertContains($withNeitherFlag->id, $withDelegates);
        $this->assertNotContains($withBothFlags->id, $withDelegates);
        $this->assertContains($withBothFlags->id, $withoutDelegates);
        $this->assertNotContains($withNeitherFlag->id, $withoutDelegates);
    }
}
