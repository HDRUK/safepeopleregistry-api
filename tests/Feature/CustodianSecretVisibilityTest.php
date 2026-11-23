<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\User;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

/**
 * A Custodian's `unique_identifier` is the shared secret used to sign machine-to-machine
 * requests, so it must only be returned to admins and members of that Custodian.
 */
class CustodianSecretVisibilityTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CustodianActors;

    public const TEST_URL = '/api/v1/custodians';

    private Custodian $custodian;

    public function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
        $this->withUsers(true);

        $this->custodian = Custodian::findOrFail(
            CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id
        );
    }

    public function test_the_custodian_list_never_includes_the_secret(): void
    {
        $response = $this->as($this->organisation_admin)->json('GET', self::TEST_URL);

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $custodian) {
            $this->assertArrayNotHasKey('unique_identifier', $custodian);
        }
    }

    public function test_an_organisation_user_cannot_see_a_custodians_secret(): void
    {
        $this->as($this->organisation_admin)
            ->json('GET', self::TEST_URL . '/' . $this->custodian->id)
            ->assertStatus(200)
            ->assertJsonMissingPath('data.unique_identifier');
    }

    public function test_a_member_of_another_custodian_cannot_see_this_custodians_secret(): void
    {
        $otherCustodian = Custodian::factory()->create();
        [$otherMember] = $this->makeCustodianUserActor($otherCustodian->id, CustodianRole::Admin->value);

        $this->as($otherMember)
            ->json('GET', self::TEST_URL . '/' . $this->custodian->id)
            ->assertStatus(200)
            ->assertJsonMissingPath('data.unique_identifier');
    }

    public function test_looking_up_a_custodian_by_identifier_does_not_return_the_secret(): void
    {
        $this->as($this->organisation_admin)
            ->json('GET', self::TEST_URL . '/identifier/' . $this->custodian->unique_identifier)
            ->assertStatus(200)
            ->assertJsonMissingPath('data.unique_identifier');
    }

    public function test_a_member_can_see_their_own_custodians_secret(): void
    {
        $this->as($this->custodian_admin)
            ->json('GET', self::TEST_URL . '/' . $this->custodian->id)
            ->assertStatus(200)
            ->assertJsonPath('data.unique_identifier', $this->custodian->unique_identifier);
    }

    public function test_a_read_only_member_can_see_their_own_custodians_secret(): void
    {
        [$readOnly] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::ReadOnly->value);

        $this->as($readOnly)
            ->json('GET', self::TEST_URL . '/' . $this->custodian->id)
            ->assertStatus(200)
            ->assertJsonPath('data.unique_identifier', $this->custodian->unique_identifier);
    }

    public function test_a_system_admin_can_see_a_custodians_secret(): void
    {
        $this->as($this->admin)
            ->json('GET', self::TEST_URL . '/' . $this->custodian->id)
            ->assertStatus(200)
            ->assertJsonPath('data.unique_identifier', $this->custodian->unique_identifier);
    }

    private function as(User $user): self
    {
        return $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())->actingAs($user);
    }
}
