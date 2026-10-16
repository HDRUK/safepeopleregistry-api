<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use App\Services\OrganisationMembershipService;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Keycloak;
use KeycloakGuard\ActingAsKeycloakUser;
use Mockery;
use Tests\TestCase;
use TriggerEmail;

class SroClaimHandoverTest extends TestCase
{
    use ActingAsKeycloakUser;

    private const INVITE_CODE = '6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10';
    private const CLAIMANT_SUB = '0b6c8f7e-2d1a-4c3b-9e8f-7a6b5c4d3e2f';
    private const INVITEE_EMAIL = 'sam.senior@example.com';

    private OrganisationMembershipService $service;
    private Organisation $organisation;
    private User $inviter;
    private User $invitedSro;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        TriggerEmail::spy();

        // The claimant has a Keycloak account but no local user yet.
        config(['keycloak.load_user_from_database' => false]);
        // Seeding can resolve the guard before the request carries a token; start from a fresh one.
        Auth::forgetGuards();

        Keycloak::shouldReceive('getUserInfo')->andReturn(new Response(new PsrResponse(200, [], json_encode([
            'sub' => self::CLAIMANT_SUB,
            'given_name' => 'Sam',
            'family_name' => 'Senior',
            'email' => self::INVITEE_EMAIL,
        ]))));

        $this->service = new OrganisationMembershipService();
        $this->organisation = Organisation::factory()->create();
        $this->inviter = $this->createMember(isSro: false);
        $this->invitedSro = $this->createMember(isSro: true, unclaimed: 1, email: self::INVITEE_EMAIL);
        $this->invitedSro->invited_by = $this->inviter->id;
        $this->invitedSro->save();

        PendingInvite::create([
            'user_id' => $this->invitedSro->id,
            'organisation_id' => $this->organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => self::INVITE_CODE,
            'type' => 'sro_invite',
        ]);
    }

    private function createMember(bool $isSro, int $unclaimed = 0, ?string $email = null): User
    {
        return User::factory()->create([
            'email' => $email ?? fake()->unique()->safeEmail(),
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
            'keycloak_id' => $unclaimed ? null : fake()->uuid(),
        ]);
    }

    // What a claim leaves behind before the handover: the invitee is claimed.
    private function markClaimed(User $user): User
    {
        $user->unclaimed = false;
        $user->keycloak_id = self::CLAIMANT_SUB;
        $user->save();

        return $user;
    }

    private function claimViaInviteCode()
    {
        return $this->actingAsKeycloakUser(null, ['sub' => self::CLAIMANT_SUB])
            ->json('POST', "/api/auth/claimUser/{$this->invitedSro->id}", ['invite_code' => self::INVITE_CODE]);
    }

    private function claimViaRegistration()
    {
        return $this->actingAsKeycloakUser(null, ['sub' => self::CLAIMANT_SUB])
            ->json('POST', '/api/auth/register');
    }

    public function test_claiming_makes_the_previous_sro_a_delegate(): void
    {
        $previousSro = $this->createMember(isSro: true);

        $handover = $this->service->completeSroClaim($this->markClaimed($this->invitedSro));

        $this->assertFalse($previousSro->fresh()->is_sro);
        $this->assertSame(1, $previousSro->fresh()->is_delegate);
        $this->assertSame($this->invitedSro->id, $this->organisation->claimedSro()->value('id'));
        $this->assertSame($previousSro->id, $handover->replacedSroId);
        $this->assertSame($this->inviter->id, $handover->actor->id);
    }

    public function test_claiming_with_no_previous_sro_replaces_nobody(): void
    {
        $handover = $this->service->completeSroClaim($this->markClaimed($this->invitedSro));

        $this->assertNull($handover->replacedSroId);
        $this->assertTrue($this->invitedSro->fresh()->is_sro);
    }

    public function test_claiming_as_a_delegate_hands_nothing_over(): void
    {
        $sro = $this->createMember(isSro: true);
        $invitedDelegate = $this->createMember(isSro: false, unclaimed: 1);

        $this->assertNull($this->service->completeSroClaim($this->markClaimed($invitedDelegate)));

        $this->assertTrue($sro->fresh()->is_sro);
    }

    public function test_the_new_sro_is_named_as_the_actor_when_the_inviter_is_unknown(): void
    {
        $this->invitedSro->invited_by = null;
        $this->invitedSro->save();

        $handover = $this->service->completeSroClaim($this->markClaimed($this->invitedSro));

        $this->assertSame($this->invitedSro->id, $handover->actor->id);
    }

    public function test_claiming_via_the_invite_code_hands_over_and_emails_everyone_affected(): void
    {
        $previousSro = $this->createMember(isSro: true);

        $this->claimViaInviteCode()->assertStatus(201);

        $this->assertFalse($previousSro->fresh()->is_sro);
        $this->assertTrue($this->invitedSro->fresh()->is_sro);

        $emails = [];
        TriggerEmail::shouldHaveReceived('spawnEmail')->withArgs(function (array $input) use (&$emails) {
            $emails[] = ['type' => $input['type'], 'to' => $input['to'], 'by' => $input['by']];

            return true;
        });

        $this->assertEqualsCanonicalizing([
            ['type' => 'SRO_CLAIMED_USER', 'to' => $this->invitedSro->id, 'by' => $this->inviter->id],
            ['type' => 'SRO_REPLACED_USER', 'to' => $previousSro->id, 'by' => $this->inviter->id],
            ['type' => 'SRO_APPOINTED_ORGANISATION', 'to' => $this->inviter->id, 'by' => $this->inviter->id],
        ], $emails);
    }

    public function test_claiming_via_registration_also_hands_over(): void
    {
        $previousSro = $this->createMember(isSro: true);

        $this->claimViaRegistration()->assertStatus(201);

        $this->assertFalse($this->invitedSro->fresh()->unclaimed);
        $this->assertFalse($previousSro->fresh()->is_sro);
        $this->assertSame([$this->invitedSro->id], $this->organisation->claimedSro()->pluck('id')->all());
        TriggerEmail::shouldHaveReceived('spawnEmail')->with(Mockery::on(
            fn (array $input) => $input['type'] === 'SRO_REPLACED_USER' && $input['to'] === $previousSro->id
        ));
    }
}
