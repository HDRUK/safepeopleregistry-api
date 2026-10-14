<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class AppointSroEndpointTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    private Organisation $organisation;
    private User $delegate;
    private User $otherDelegate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        Notification::fake();

        $this->organisation = Organisation::factory()->create();
        $this->delegate = $this->createOrganisationUser($this->organisation, isSro: false);
        $this->otherDelegate = $this->createOrganisationUser($this->organisation, isSro: false);
    }

    private function createOrganisationUser(?Organisation $organisation, bool $isSro): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation?->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => 0,
        ]);
    }

    private function appointSro(User $actor, array $payload)
    {
        return $this->actingAs($actor)
            ->json('PUT', self::TEST_URL . "/{$this->organisation->id}/sro", $payload);
    }

    public function test_a_delegate_can_appoint_themselves_when_there_is_no_sro(): void
    {
        $this->appointSro($this->delegate, ['user_id' => $this->delegate->id])->assertStatus(200);

        $this->assertTrue($this->delegate->fresh()->is_sro);
    }

    public function test_a_delegate_can_appoint_another_delegate_when_there_is_no_sro(): void
    {
        $this->appointSro($this->delegate, ['user_id' => $this->otherDelegate->id])->assertStatus(200);

        $this->assertTrue($this->otherDelegate->fresh()->is_sro);
    }

    public function test_once_there_is_an_sro_a_delegate_cannot_appoint(): void
    {
        $sro = $this->createOrganisationUser($this->organisation, isSro: true);

        $this->appointSro($this->delegate, ['user_id' => $this->delegate->id])->assertStatus(403);

        $this->assertTrue($sro->fresh()->is_sro);
        $this->assertFalse($this->delegate->fresh()->is_sro);
    }

    public function test_the_sro_can_hand_over_to_a_delegate(): void
    {
        $sro = $this->createOrganisationUser($this->organisation, isSro: true);

        $this->appointSro($sro, ['user_id' => $this->delegate->id])->assertStatus(200);

        $this->assertFalse($sro->fresh()->is_sro);
        $this->assertTrue($this->delegate->fresh()->is_sro);
    }

    public static function outsiders(): array
    {
        return [
            'detached delegate' => ['detached delegate'],
            'delegate of another organisation' => ['other organisation delegate'],
            'researcher' => ['researcher'],
        ];
    }

    #[DataProvider('outsiders')]
    public function test_users_outside_the_organisation_cannot_appoint(string $actorType): void
    {
        $actor = match ($actorType) {
            'detached delegate' => $this->createOrganisationUser(null, isSro: false),
            'other organisation delegate' => $this->createOrganisationUser(Organisation::factory()->create(), isSro: false),
            'researcher' => $this->user,
        };

        $this->appointSro($actor, ['user_id' => $this->delegate->id])->assertStatus(403);

        $this->assertFalse($this->delegate->fresh()->is_sro);
    }

    public function test_an_admin_can_appoint(): void
    {
        $this->appointSro($this->admin, ['user_id' => $this->delegate->id])->assertStatus(200);

        $this->assertTrue($this->delegate->fresh()->is_sro);
    }

    public function test_a_user_outside_the_organisation_cannot_be_appointed(): void
    {
        $outsider = $this->createOrganisationUser(Organisation::factory()->create(), isSro: false);

        $this->appointSro($this->delegate, ['user_id' => $outsider->id])->assertStatus(404);

        $this->assertFalse($outsider->fresh()->is_sro);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing user_id' => [[]],
            'non-integer user_id' => [['user_id' => 'abc']],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_the_appointee_must_be_given(array $payload): void
    {
        $this->appointSro($this->delegate, $payload)->assertStatus(400);
    }
}
