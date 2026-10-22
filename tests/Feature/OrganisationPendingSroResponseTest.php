<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationPendingSroResponseTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';
    private const INVITE_CODE = '6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10';

    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();

        $this->organisation = Organisation::factory()->create();
    }

    private function createMember(bool $isSro, int $unclaimed = 0): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
        ]);
    }

    private function inviteSro(User $invitee): PendingInvite
    {
        return PendingInvite::create([
            'user_id' => $invitee->id,
            'organisation_id' => $this->organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => self::INVITE_CODE,
            'invite_sent_at' => '2026-10-01 09:00:00',
            'type' => 'sro_invite',
        ]);
    }

    private function show()
    {
        return $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$this->organisation->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_the_invited_sro_and_when_they_were_invited(): void
    {
        $invitedSro = $this->createMember(isSro: true, unclaimed: 1);
        $this->inviteSro($invitedSro);

        $this->show()
            ->assertJsonPath('data.pending_sro.id', $invitedSro->id)
            ->assertJsonPath('data.pending_sro.email', $invitedSro->email)
            ->assertJsonPath('data.pending_sro.departments', [])
            ->assertJsonPath('data.pending_sro.pending_sro_invite.invite_sent_at', '2026-10-01 09:00:00');
    }

    public function test_the_invite_code_is_never_exposed(): void
    {
        $this->inviteSro($this->createMember(isSro: true, unclaimed: 1));

        $response = $this->show();

        $this->assertSame(['invite_sent_at'], array_keys($response->json('data.pending_sro.pending_sro_invite')));
        $this->assertStringNotContainsString(self::INVITE_CODE, $response->getContent());
    }

    public function test_show_has_no_pending_sro_without_an_invitation(): void
    {
        $this->createMember(isSro: true);

        $this->show()->assertJsonPath('data.pending_sro', null);
    }

    public function test_an_invitee_who_was_replaced_is_not_the_pending_sro(): void
    {
        $replaced = $this->createMember(isSro: false, unclaimed: 1);
        $this->inviteSro($replaced);

        $this->show()->assertJsonPath('data.pending_sro', null);
    }

    public function test_the_claimed_and_pending_sro_are_returned_separately(): void
    {
        $claimedSro = $this->createMember(isSro: true);
        $invitedSro = $this->createMember(isSro: true, unclaimed: 1);
        $this->inviteSro($invitedSro);

        $this->show()
            ->assertJsonPath('data.claimed_sro.id', $claimedSro->id)
            ->assertJsonPath('data.pending_sro.id', $invitedSro->id);
    }
}
