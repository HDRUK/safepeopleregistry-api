<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use App\Services\OrganisationMembershipService;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AppointSroTest extends TestCase
{
    private OrganisationMembershipService $service;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = new OrganisationMembershipService();
        $this->organisation = Organisation::factory()->create();
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

        $this->assertTrue($this->service->appointSro($this->organisation, $delegate->id));

        $this->assertTrue($delegate->fresh()->is_sro);
        $this->assertSame($delegate->id, $this->organisation->claimedSro()->value('id'));
    }

    public function test_appointing_a_new_sro_makes_the_current_sro_a_delegate(): void
    {
        $currentSro = $this->createMember($this->organisation, isSro: true);
        $delegate = $this->createMember($this->organisation, isSro: false);

        $this->service->appointSro($this->organisation, $delegate->id);

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

        $this->service->appointSro($this->organisation, $delegate->id);

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

        $this->assertTrue($this->service->appointSro($this->organisation, $currentSro->id));

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

        $this->assertFalse($this->service->appointSro($this->organisation, $user->id ?? 0));

        $this->assertTrue($currentSro->fresh()->is_sro);
        $this->assertFalse((bool) $user?->fresh()->is_sro);
    }

    public function test_the_handover_is_recorded_in_the_activity_log(): void
    {
        $currentSro = $this->createMember($this->organisation, isSro: true);
        $delegate = $this->createMember($this->organisation, isSro: false);

        // LogsActivity listens for model events, which the base TestCase turns off.
        $this->withTemporaryObservers(
            fn () => $this->service->appointSro($this->organisation, $delegate->id)
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
