<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class RemoveDelegateTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    private Organisation $organisation;
    private User $delegate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        Notification::fake();

        $this->organisation = Organisation::factory()->create();
        $this->delegate = $this->createOrganisationUser($this->organisation, isSro: 0);
    }

    private function createOrganisationUser(Organisation $organisation, int $isSro): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => 0,
        ]);
    }

    private function removeDelegate(User $actor, int $userId, ?int $organisationId = null)
    {
        $organisationId ??= $this->organisation->id;

        return $this->actingAs($actor)
            ->json('DELETE', self::TEST_URL . "/{$organisationId}/delegates/{$userId}");
    }

    public static function actors(): array
    {
        return [
            'admin' => ['admin', false, 200],
            'sro' => ['sro', true, 200],
            'delegate before the organisation has an sro' => ['delegate', false, 200],
            'delegate once the organisation has an sro' => ['delegate', true, 200],
            'detached delegate' => ['detached delegate', false, 403],
            'delegate of another organisation' => ['other organisation delegate', false, 403],
            'researcher' => ['researcher', false, 403],
        ];
    }

    #[DataProvider('actors')]
    public function test_who_can_remove_a_delegate(string $actor, bool $organisationHasSro, int $expectedStatus): void
    {
        $sro = $organisationHasSro ? $this->createOrganisationUser($this->organisation, isSro: 1) : null;

        $user = match ($actor) {
            'admin' => $this->admin,
            'sro' => $sro,
            'delegate' => $this->createOrganisationUser($this->organisation, isSro: 0),
            'other organisation delegate' => $this->createOrganisationUser(Organisation::factory()->create(), isSro: 0),
            'researcher' => $this->user,
            'detached delegate' => User::factory()->create([
                'user_group' => User::GROUP_ORGANISATIONS,
                'organisation_id' => null,
                'is_sro' => 0,
                'is_delegate' => 1,
            ]),
        };

        $this->removeDelegate($user, $this->delegate->id)->assertStatus($expectedStatus);

        $expectedOrganisationId = $expectedStatus === 200 ? null : $this->organisation->id;
        $this->assertSame($expectedOrganisationId, $this->delegate->fresh()->organisation_id);
    }

    public function test_a_delegate_cannot_remove_themselves(): void
    {
        $this->removeDelegate($this->delegate, $this->delegate->id)->assertStatus(403);

        $this->assertSame($this->organisation->id, $this->delegate->fresh()->organisation_id);
    }

    public function test_a_delegate_cannot_remove_the_sro(): void
    {
        $sro = $this->createOrganisationUser($this->organisation, isSro: 1);

        $this->removeDelegate($this->delegate, $sro->id)->assertStatus(404);

        $this->assertSame($this->organisation->id, $sro->fresh()->organisation_id);
    }

    public function test_the_sro_cannot_be_removed(): void
    {
        $sro = $this->createOrganisationUser($this->organisation, isSro: 1);

        $this->removeDelegate($this->admin, $sro->id)->assertStatus(404);

        $this->assertSame($this->organisation->id, $sro->fresh()->organisation_id);
    }

    public function test_a_delegate_of_another_organisation_cannot_be_removed(): void
    {
        $otherOrganisation = Organisation::factory()->create();
        $otherDelegate = $this->createOrganisationUser($otherOrganisation, isSro: 0);

        $this->removeDelegate($this->admin, $otherDelegate->id)->assertStatus(404);

        $this->assertSame($otherOrganisation->id, $otherDelegate->fresh()->organisation_id);
    }

    public function test_an_unknown_user_cannot_be_removed(): void
    {
        $this->removeDelegate($this->admin, User::max('id') + 1)->assertStatus(404);
    }

    public function test_a_delegate_cannot_be_removed_twice(): void
    {
        $this->removeDelegate($this->admin, $this->delegate->id)->assertStatus(200);
        $this->removeDelegate($this->admin, $this->delegate->id)->assertStatus(404);
    }

    public function test_an_unknown_organisation_is_rejected(): void
    {
        $this->removeDelegate($this->admin, $this->delegate->id, Organisation::max('id') + 1)->assertStatus(400);

        $this->assertSame($this->organisation->id, $this->delegate->fresh()->organisation_id);
    }

    public function test_a_removed_delegate_loses_access_to_the_organisation(): void
    {
        $this->actingAs($this->delegate)
            ->json('GET', self::TEST_URL . "/{$this->organisation->id}/delegates")
            ->assertStatus(200);

        $this->removeDelegate($this->admin, $this->delegate->id)->assertStatus(200);

        $this->actingAs($this->delegate->fresh())
            ->json('GET', self::TEST_URL . "/{$this->organisation->id}/delegates")
            ->assertStatus(403);
    }
}
