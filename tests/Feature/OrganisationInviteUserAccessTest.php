<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationInviteUserAccessTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        Notification::fake();

        $this->organisation = Organisation::factory()->create();
    }

    private function createOrganisationUser(?Organisation $organisation, bool $isSro = false): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation?->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
        ]);
    }

    private function invite(User $actor, string $email)
    {
        return $this->actingAs($actor)
            ->json('POST', self::TEST_URL . "/{$this->organisation->id}/invite_user", [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => $email,
                'user_group' => User::GROUP_ORGANISATIONS,
                'is_delegate' => 1,
            ]);
    }

    public static function allowedActors(): array
    {
        return [
            'admin' => ['admin'],
            'sro of the organisation' => ['sro'],
            'delegate of the organisation' => ['delegate'],
        ];
    }

    #[DataProvider('allowedActors')]
    public function test_admins_and_members_can_invite_into_the_organisation(string $actorType): void
    {
        $actor = match ($actorType) {
            'admin' => $this->admin,
            'sro' => $this->createOrganisationUser($this->organisation, isSro: true),
            'delegate' => $this->createOrganisationUser($this->organisation),
        };

        $this->invite($actor, 'jane.doe@example.com')->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@example.com',
            'organisation_id' => $this->organisation->id,
        ]);
    }

    public static function outsiders(): array
    {
        return [
            'delegate of another organisation' => ['other organisation delegate'],
            'detached organisation user' => ['detached'],
            'researcher' => ['researcher'],
        ];
    }

    #[DataProvider('outsiders')]
    public function test_outsiders_cannot_invite_into_the_organisation(string $actorType): void
    {
        $actor = match ($actorType) {
            'other organisation delegate' => $this->createOrganisationUser(Organisation::factory()->create()),
            'detached' => $this->createOrganisationUser(null),
            'researcher' => $this->user,
        };

        $this->invite($actor, 'jane.doe@example.com')->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'jane.doe@example.com']);
    }

    public function test_outsiders_cannot_learn_whether_an_email_is_registered(): void
    {
        $outsider = $this->createOrganisationUser(Organisation::factory()->create());

        $this->invite($outsider, $this->user->email)->assertStatus(403);
    }
}
