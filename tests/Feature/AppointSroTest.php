<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use App\Services\OrganisationMembershipService;
use Illuminate\Support\Facades\Notification;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use TriggerEmail;

class AppointSroTest extends TestCase
{
    private OrganisationMembershipService $service;
    private Organisation $organisation;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = new OrganisationMembershipService();
        $this->organisation = Organisation::factory()->create();
        $this->actor = User::factory()->create();
    }

    /**
     * @return array<int, array{type: string, to: int}>
     */
    private function spawnedEmails(): array
    {
        $emails = [];

        TriggerEmail::shouldHaveReceived('spawnEmail')->withArgs(function (array $input) use (&$emails) {
            $emails[] = ['type' => $input['type'], 'to' => $input['to']];

            return true;
        });

        return $emails;
    }

    private function createMember(?Organisation $organisation, bool $isSro, int $unclaimed = 0): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation?->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
            'keycloak_id' => $unclaimed ? null : fake()->uuid(),
        ]);
    }

    public function test_a_delegate_becomes_the_sro_when_there_is_none(): void
    {
        $delegate = $this->createMember($this->organisation, isSro: false);

        $this->assertTrue($this->service->appointSro($this->organisation, $delegate->id, $this->actor));

        $this->assertTrue($delegate->fresh()->is_sro);
        $this->assertSame($delegate->id, $this->organisation->claimedSro()->value('id'));
    }

    public function test_appointing_a_new_sro_makes_the_current_sro_a_delegate(): void
    {
        $currentSro = $this->createMember($this->organisation, isSro: true);
        $delegate = $this->createMember($this->organisation, isSro: false);

        $this->service->appointSro($this->organisation, $delegate->id, $this->actor);

        $this->assertFalse($currentSro->fresh()->is_sro);
        $this->assertSame(1, $currentSro->fresh()->is_delegate);
        $this->assertSame($this->organisation->id, $currentSro->fresh()->organisation_id);
        $this->assertTrue($delegate->fresh()->is_sro);
        $this->assertSame(0, $delegate->fresh()->is_delegate);
        $this->assertSame([$delegate->id], $this->organisation->claimedSro()->pluck('id')->all());
    }

    public function test_an_invited_sro_keeps_their_invitation_but_will_join_as_a_delegate(): void
    {
        $invitedSro = $this->createMember($this->organisation, isSro: true, unclaimed: 1);
        $invite = PendingInvite::create([
            'user_id' => $invitedSro->id,
            'organisation_id' => $this->organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => '6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10',
        ]);
        $delegate = $this->createMember($this->organisation, isSro: false);

        $this->service->appointSro($this->organisation, $delegate->id, $this->actor);

        $invitedSro = $invitedSro->fresh();
        $this->assertFalse($invitedSro->is_sro);
        $this->assertSame(1, $invitedSro->is_delegate);
        $this->assertSame($this->organisation->id, $invitedSro->organisation_id);
        $this->assertTrue($invitedSro->unclaimed);
        $this->assertSame(PendingInvite::STATE_PENDING, $invite->fresh()->status);
    }

    public function test_appointing_the_current_sro_changes_nothing(): void
    {
        $currentSro = $this->createMember($this->organisation, isSro: true);

        $this->assertTrue($this->service->appointSro($this->organisation, $currentSro->id, $this->actor));

        $this->assertTrue($currentSro->fresh()->is_sro);
    }

    public static function usersWhoCannotBeAppointed(): array
    {
        return [
            'an invited (unclaimed) delegate' => ['invited delegate'],
            'a member of another organisation' => ['other organisation member'],
            'a detached user' => ['detached'],
            'a researcher with the organisation id' => ['researcher'],
            'an unknown user' => ['unknown'],
        ];
    }

    #[DataProvider('usersWhoCannotBeAppointed')]
    public function test_only_claimed_members_of_the_organisation_can_be_appointed(string $target): void
    {
        $currentSro = $this->createMember($this->organisation, isSro: true);
        $user = match ($target) {
            'invited delegate' => $this->createMember($this->organisation, isSro: false, unclaimed: 1),
            'other organisation member' => $this->createMember(Organisation::factory()->create(), isSro: false),
            'detached' => $this->createMember(null, isSro: false),
            'researcher' => User::factory()->create([
                'user_group' => User::GROUP_USERS,
                'organisation_id' => $this->organisation->id,
            ]),
            'unknown' => null,
        };

        $this->assertFalse($this->service->appointSro($this->organisation, $user->id ?? 0, $this->actor));

        $this->assertTrue($currentSro->fresh()->is_sro);
        $this->assertFalse((bool) $user?->fresh()->is_sro);
    }

    public function test_a_handover_emails_the_replaced_sro_and_the_organisation(): void
    {
        TriggerEmail::spy();
        $currentSro = $this->createMember($this->organisation, isSro: true);
        $delegate = $this->createMember($this->organisation, isSro: false);
        $otherDelegate = $this->createMember($this->organisation, isSro: false);
        $invitedDelegate = $this->createMember($this->organisation, isSro: false, unclaimed: 1);
        $this->createMember(Organisation::factory()->create(), isSro: false);

        $this->service->appointSro($this->organisation, $delegate->id, $this->actor);

        $this->assertEqualsCanonicalizing([
            ['type' => 'SRO_REPLACED_USER', 'to' => $currentSro->id],
            ['type' => 'SRO_APPOINTED_ORGANISATION', 'to' => $delegate->id],
            ['type' => 'SRO_APPOINTED_ORGANISATION', 'to' => $otherDelegate->id],
        ], $this->spawnedEmails());

        TriggerEmail::shouldHaveReceived('spawnEmail')->with(Mockery::on(
            fn (array $input) => $input['type'] === 'SRO_REPLACED_USER'
                && $input['by'] === $this->actor->id
                && $input['for'] === $delegate->id
                && $input['organisationId'] === $this->organisation->id
        ));
        TriggerEmail::shouldNotHaveReceived('spawnEmail', [Mockery::on(
            fn (array $input) => $input['to'] === $invitedDelegate->id
        )]);
    }

    public function test_a_first_appointment_only_emails_the_organisation(): void
    {
        TriggerEmail::spy();
        $delegate = $this->createMember($this->organisation, isSro: false);
        $otherDelegate = $this->createMember($this->organisation, isSro: false);

        $this->service->appointSro($this->organisation, $delegate->id, $this->actor);

        $this->assertEqualsCanonicalizing([
            ['type' => 'SRO_APPOINTED_ORGANISATION', 'to' => $delegate->id],
            ['type' => 'SRO_APPOINTED_ORGANISATION', 'to' => $otherDelegate->id],
        ], $this->spawnedEmails());
    }

    public function test_replacing_only_an_invited_sro_does_not_send_a_replaced_email(): void
    {
        TriggerEmail::spy();
        $invitedSro = $this->createMember($this->organisation, isSro: true, unclaimed: 1);
        $delegate = $this->createMember($this->organisation, isSro: false);

        $this->service->appointSro($this->organisation, $delegate->id, $this->actor);

        TriggerEmail::shouldNotHaveReceived('spawnEmail', [Mockery::on(
            fn (array $input) => $input['type'] === 'SRO_REPLACED_USER' || $input['to'] === $invitedSro->id
        )]);
    }

    public function test_re_appointing_the_current_sro_sends_nothing(): void
    {
        TriggerEmail::spy();
        $currentSro = $this->createMember($this->organisation, isSro: true);

        $this->service->appointSro($this->organisation, $currentSro->id, $this->actor);

        TriggerEmail::shouldNotHaveReceived('spawnEmail');
    }

    public function test_a_refused_appointment_sends_nothing(): void
    {
        TriggerEmail::spy();
        $this->createMember($this->organisation, isSro: true);
        $outsider = $this->createMember(Organisation::factory()->create(), isSro: false);

        $this->service->appointSro($this->organisation, $outsider->id, $this->actor);

        TriggerEmail::shouldNotHaveReceived('spawnEmail');
    }

    public function test_the_handover_is_recorded_in_the_activity_log(): void
    {
        $currentSro = $this->createMember($this->organisation, isSro: true);
        $delegate = $this->createMember($this->organisation, isSro: false);

        // LogsActivity listens for model events, which the base TestCase turns off.
        $this->withTemporaryObservers(
            fn () => $this->service->appointSro($this->organisation, $delegate->id, $this->actor)
        );

        $isSroChange = fn (User $user) => Activity::query()
            ->where('log_name', 'user')
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->latest('id')
            ->first()?->properties['attributes']['is_sro'] ?? null;

        $this->assertFalse($isSroChange($currentSro));
        $this->assertTrue($isSroChange($delegate));
    }
}
