<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use KeycloakGuard\ActingAsKeycloakUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use TriggerEmail;

class ResendSroInviteTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    private Organisation $organisation;
    private User $delegate;
    private User $invitedSro;
    private PendingInvite $invite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        Notification::fake();
        TriggerEmail::spy();

        $this->organisation = Organisation::factory()->create();
        $this->delegate = $this->createMember(isSro: false);
        $this->invitedSro = $this->createMember(isSro: true, unclaimed: 1);
        $this->invite = PendingInvite::create([
            'user_id' => $this->invitedSro->id,
            'organisation_id' => $this->organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => '6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10',
            'invite_sent_at' => Carbon::now()->subDays(3),
            'type' => 'sro_invite',
        ]);
    }

    private function createMember(bool $isSro, int $unclaimed = 0, ?Organisation $organisation = null): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => ($organisation ?? $this->organisation)->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
            'keycloak_id' => $unclaimed ? null : fake()->uuid(),
        ]);
    }

    private function resend(User $actor)
    {
        return $this->actingAs($actor)
            ->json('POST', "/api/v1/organisations/{$this->organisation->id}/sro/resend_invite");
    }

    private function assertSroInviteResent(User $actor): void
    {
        TriggerEmail::shouldHaveReceived('spawnEmail')->once()->with(Mockery::on(
            fn (array $input) => $input['type'] === 'SRO_INVITE'
                && $input['to'] === $this->invitedSro->id
                && $input['by'] === $actor->id
                && $input['organisationId'] === $this->organisation->id
                && $input['inviteId'] === $this->invite->id
        ));
    }

    public function test_a_delegate_can_resend_the_invitation_when_there_is_no_sro(): void
    {
        $this->resend($this->delegate)->assertStatus(200);

        $this->assertSroInviteResent($this->delegate);
        // invite_sent_at is not cast to a date on PendingInvite.
        $this->assertTrue(Carbon::parse($this->invite->fresh()->invite_sent_at)->isToday());
    }

    public function test_the_sro_can_resend_the_invitation_to_their_replacement(): void
    {
        $sro = $this->createMember(isSro: true);

        $this->resend($sro)->assertStatus(200);

        $this->assertSroInviteResent($sro);
    }

    public function test_once_there_is_an_sro_a_delegate_cannot_resend(): void
    {
        $this->createMember(isSro: true);

        $this->resend($this->delegate)->assertStatus(403);

        TriggerEmail::shouldNotHaveReceived('spawnEmail');
    }

    public function test_users_outside_the_organisation_cannot_resend(): void
    {
        $outsider = $this->createMember(isSro: false, organisation: Organisation::factory()->create());

        $this->resend($outsider)->assertStatus(403);

        TriggerEmail::shouldNotHaveReceived('spawnEmail');
    }

    public static function nothingToResend(): array
    {
        return [
            'no invitation' => ['none'],
            'invitee replaced and joining as a delegate' => ['replaced'],
            'invitation already accepted' => ['accepted'],
        ];
    }

    #[DataProvider('nothingToResend')]
    public function test_there_is_nothing_to_resend(string $state): void
    {
        match ($state) {
            'none' => $this->invite->delete(),
            'replaced' => $this->invitedSro->forceFill(['is_sro' => false, 'is_delegate' => 1])->save(),
            'accepted' => $this->invite->update(['status' => PendingInvite::STATE_COMPLETE]),
        };

        $this->resend($this->delegate)->assertStatus(404);

        TriggerEmail::shouldNotHaveReceived('spawnEmail');
    }

    public function test_an_admin_resending_an_sro_invitation_sends_it_as_one(): void
    {
        $this->actingAs($this->admin)
            ->json('POST', "/api/v1/pending_invites/{$this->invite->id}/resend_invite")
            ->assertStatus(200);

        $this->assertSroInviteResent($this->admin);
    }
}
