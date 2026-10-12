<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Keycloak;
use KeycloakGuard\ActingAsKeycloakUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClaimUserTest extends TestCase
{
    use ActingAsKeycloakUser;

    private const INVITE_CODE = '6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10';
    private const CLAIMANT_SUB = '0b6c8f7e-2d1a-4c3b-9e8f-7a6b5c4d3e2f';

    private User $invitee;
    private PendingInvite $invite;

    protected function setUp(): void
    {
        parent::setUp();

        // The claimant has a Keycloak account but no local user yet.
        config(['keycloak.load_user_from_database' => false]);
        // Seeding can resolve the guard before the request carries a token; start from a fresh one.
        Auth::forgetGuards();

        Keycloak::shouldReceive('getUserInfo')->andReturn(new Response(new PsrResponse(200, [], json_encode([
            'sub' => self::CLAIMANT_SUB,
            'given_name' => 'Jane',
            'family_name' => 'Doe',
            'email' => 'jane.doe@example.com',
        ]))));

        $organisation = Organisation::factory()->create();
        $this->invitee = $this->createUnclaimedUser(User::GROUP_ORGANISATIONS, $organisation);
        $this->invite = $this->createInvite($this->invitee, self::INVITE_CODE);
    }

    private function createUnclaimedUser(string $userGroup, ?Organisation $organisation = null): User
    {
        return User::factory()->create([
            'user_group' => $userGroup,
            'organisation_id' => $organisation?->id,
            'keycloak_id' => null,
            'unclaimed' => 1,
        ]);
    }

    private function createInvite(User $user, string $inviteCode): PendingInvite
    {
        return PendingInvite::create([
            'user_id' => $user->id,
            'organisation_id' => $user->organisation_id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => $inviteCode,
            'type' => 'delegate_invite',
        ]);
    }

    private function claim(int $userId, array $payload = ['invite_code' => self::INVITE_CODE]): TestResponse
    {
        return $this->actingAsKeycloakUser(null, ['sub' => self::CLAIMANT_SUB])
            ->json('POST', "/api/auth/claimUser/{$userId}", $payload);
    }

    private function assertUnclaimed(User $user): void
    {
        $user = $user->fresh();

        $this->assertTrue($user->unclaimed);
        $this->assertNull($user->keycloak_id);
    }

    public function test_an_invitee_can_claim_their_account_with_their_invite_code(): void
    {
        $this->claim($this->invitee->id)->assertStatus(201);

        $invitee = $this->invitee->fresh();
        $this->assertFalse($invitee->unclaimed);
        $this->assertSame(self::CLAIMANT_SUB, $invitee->keycloak_id);
        $this->assertSame('jane.doe@example.com', $invitee->email);
        $this->assertSame(PendingInvite::STATE_COMPLETE, $this->invite->fresh()->status);
    }

    public function test_an_invite_code_cannot_claim_a_different_user(): void
    {
        $otherInvitee = $this->createUnclaimedUser(User::GROUP_ORGANISATIONS, Organisation::factory()->create());
        $this->createInvite($otherInvitee, 'a3e1b2c4-5d6f-4a7b-8c9d-0e1f2a3b4c5d');

        $this->claim($otherInvitee->id)->assertStatus(404);

        $this->assertUnclaimed($otherInvitee);
        $this->assertUnclaimed($this->invitee);
        $this->assertSame(PendingInvite::STATE_PENDING, $this->invite->fresh()->status);
    }

    public static function invalidInviteCodes(): array
    {
        return [
            'missing' => [[]],
            'not a uuid' => [['invite_code' => 'not-a-code']],
        ];
    }

    #[DataProvider('invalidInviteCodes')]
    public function test_a_claim_without_a_valid_invite_code_is_rejected(array $payload): void
    {
        $this->claim($this->invitee->id, $payload)->assertStatus(400);

        $this->assertUnclaimed($this->invitee);
    }

    public function test_an_unknown_invite_code_cannot_claim(): void
    {
        $this->claim($this->invitee->id, ['invite_code' => 'a3e1b2c4-5d6f-4a7b-8c9d-0e1f2a3b4c5d'])
            ->assertStatus(404);

        $this->assertUnclaimed($this->invitee);
    }

    public function test_an_invite_code_can_only_be_used_once(): void
    {
        $this->claim($this->invitee->id)->assertStatus(201);

        $this->claim($this->invitee->id)->assertStatus(404);
    }

    public function test_only_organisation_users_can_be_claimed(): void
    {
        $researcher = $this->createUnclaimedUser(User::GROUP_USERS);
        $this->createInvite($researcher, 'a3e1b2c4-5d6f-4a7b-8c9d-0e1f2a3b4c5d');

        $this->claim($researcher->id, ['invite_code' => 'a3e1b2c4-5d6f-4a7b-8c9d-0e1f2a3b4c5d'])
            ->assertStatus(400);

        $this->assertUnclaimed($researcher);
    }
}
