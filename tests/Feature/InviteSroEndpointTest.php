<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use TriggerEmail;

class InviteSroEndpointTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    private const PAYLOAD = [
        'first_name' => 'Sam',
        'last_name' => 'Senior',
        'email' => 'sam.senior@example.com',
    ];

    private Organisation $organisation;
    private User $delegate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        Notification::fake();
        TriggerEmail::spy();

        $this->organisation = Organisation::factory()->create();
        $this->delegate = $this->createOrganisationUser($this->organisation, isSro: false);
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

    private function inviteSro(User $actor, array $payload = self::PAYLOAD)
    {
        return $this->actingAs($actor)
            ->json('POST', self::TEST_URL . "/{$this->organisation->id}/sro/invite", $payload);
    }

    private function assertNotInvited(): void
    {
        $this->assertDatabaseMissing('users', ['email' => self::PAYLOAD['email']]);
    }

    public function test_a_delegate_can_invite_an_sro_when_there_is_none(): void
    {
        $response = $this->inviteSro($this->delegate)->assertStatus(201);

        $invitee = User::where('email', self::PAYLOAD['email'])->sole();
        $this->assertSame($invitee->id, $response->json('data'));
        $this->assertTrue($invitee->is_sro);
    }

    public function test_the_sro_can_invite_their_replacement(): void
    {
        $sro = $this->createOrganisationUser($this->organisation, isSro: true);

        $this->inviteSro($sro)->assertStatus(201);

        $this->assertTrue($sro->fresh()->is_sro);
    }

    public function test_an_admin_can_invite_an_sro(): void
    {
        $this->inviteSro($this->admin)->assertStatus(201);
    }

    public function test_once_there_is_an_sro_a_delegate_cannot_invite_one(): void
    {
        $this->createOrganisationUser($this->organisation, isSro: true);

        $this->inviteSro($this->delegate)->assertStatus(403);

        $this->assertNotInvited();
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
    public function test_users_outside_the_organisation_cannot_invite_an_sro(string $actorType): void
    {
        $actor = match ($actorType) {
            'detached delegate' => $this->createOrganisationUser(null, isSro: false),
            'other organisation delegate' => $this->createOrganisationUser(Organisation::factory()->create(), isSro: false),
            'researcher' => $this->user,
        };

        $this->inviteSro($actor)->assertStatus(403);

        $this->assertNotInvited();
    }

    public function test_an_existing_user_cannot_be_invited(): void
    {
        $this->inviteSro($this->delegate, [...self::PAYLOAD, 'email' => $this->user->email])
            ->assertStatus(409);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing first name' => [['first_name' => null]],
            'missing last name' => [['last_name' => null]],
            'missing email' => [['email' => null]],
            'invalid email' => [['email' => 'not-an-email']],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_the_invitee_details_must_be_valid(array $override): void
    {
        $this->inviteSro($this->delegate, [...self::PAYLOAD, ...$override])->assertStatus(400);

        $this->assertNotInvited();
    }
}
