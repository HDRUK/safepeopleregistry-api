<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use App\Services\OrganisationMembershipService;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;
use TriggerEmail;

class InviteSroTest extends TestCase
{
    private OrganisationMembershipService $service;
    private Organisation $organisation;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        TriggerEmail::spy();

        $this->service = new OrganisationMembershipService();
        $this->organisation = Organisation::factory()->create();
        $this->actor = User::factory()->create();
    }

    private function createMember(bool $isSro, int $unclaimed = 0): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
            'keycloak_id' => $unclaimed ? null : fake()->uuid(),
        ]);
    }

    private function invite(string $email = 'sam.senior@example.com'): ?User
    {
        return $this->service->inviteSro($this->organisation, 'Sam', 'Senior', $email, $this->actor);
    }

    public function test_it_creates_an_invited_sro_in_the_organisation(): void
    {
        $invitee = $this->invite()->fresh();

        $this->assertSame('sam.senior@example.com', $invitee->email);
        $this->assertSame(User::GROUP_ORGANISATIONS, $invitee->user_group);
        $this->assertSame($this->organisation->id, $invitee->organisation_id);
        $this->assertTrue($invitee->is_sro);
        $this->assertSame(0, $invitee->is_delegate);
        $this->assertTrue($invitee->unclaimed);
        $this->assertSame($this->actor->id, $invitee->invited_by);
    }

    public function test_it_emails_the_invitation(): void
    {
        $invitee = $this->invite();

        TriggerEmail::shouldHaveReceived('spawnEmail')->once()->with(Mockery::on(
            fn (array $input) => $input['type'] === 'SRO_INVITE'
                && $input['to'] === $invitee->id
                && $input['by'] === $this->actor->id
                && $input['organisationId'] === $this->organisation->id
        ));
    }

    public function test_the_current_sro_keeps_the_role_until_the_invitee_claims(): void
    {
        $currentSro = $this->createMember(isSro: true);

        $this->invite();

        $this->assertTrue($currentSro->fresh()->is_sro);
        $this->assertSame($currentSro->id, $this->organisation->claimedSro()->value('id'));
    }

    public function test_a_previously_invited_sro_keeps_their_invitation_but_will_join_as_a_delegate(): void
    {
        $previouslyInvited = $this->createMember(isSro: true, unclaimed: 1);
        $previousInvite = PendingInvite::create([
            'user_id' => $previouslyInvited->id,
            'organisation_id' => $this->organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => '6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10',
            'type' => 'sro_invite',
        ]);

        $this->invite();

        $previouslyInvited = $previouslyInvited->fresh();
        $this->assertFalse($previouslyInvited->is_sro);
        $this->assertSame(1, $previouslyInvited->is_delegate);
        $this->assertSame($this->organisation->id, $previouslyInvited->organisation_id);
        $this->assertSame(PendingInvite::STATE_PENDING, $previousInvite->fresh()->status);
    }

    public function test_an_existing_email_is_refused_and_nothing_changes(): void
    {
        $previouslyInvited = $this->createMember(isSro: true, unclaimed: 1);
        $existing = User::factory()->create();

        $this->assertNull($this->invite($existing->email));

        $this->assertSame(1, User::where('email', $existing->email)->count());
        $this->assertTrue($previouslyInvited->fresh()->is_sro);
        TriggerEmail::shouldNotHaveReceived('spawnEmail');
    }
}
