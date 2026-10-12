<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\Organisations\OrganisationDelegates;
use App\Services\OrganisationMembershipService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class OrganisationMembershipServiceTest extends TestCase
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
        $this->actor = $this->createOrganisationUser($this->organisation, isSro: 0);
    }

    private function createOrganisationUser(?Organisation $organisation, int $isSro, int $unclaimed = 0): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation?->id,
            'is_sro' => $isSro,
            'is_delegate' => $isSro ? 0 : 1,
            'unclaimed' => $unclaimed,
        ]);
    }

    public function test_removing_a_delegate_detaches_them_from_the_organisation(): void
    {
        $delegate = $this->createOrganisationUser($this->organisation, isSro: 0);

        $removed = $this->service->removeDelegate($this->organisation, $delegate->id, $this->actor);

        $this->assertTrue($removed);
        $this->assertNull($delegate->fresh()->organisation_id);
        $this->assertNotContains($delegate->id, $this->organisation->delegates()->pluck('id'));
    }

    public function test_removing_a_delegate_is_recorded_in_the_activity_log(): void
    {
        $delegate = $this->createOrganisationUser($this->organisation, isSro: 0);

        $this->service->removeDelegate($this->organisation, $delegate->id, $this->actor);

        $activity = Activity::query()
            ->where('subject_type', Organisation::class)
            ->where('subject_id', $this->organisation->id)
            ->where('causer_id', $this->actor->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($delegate->id, $activity->properties['id']);
        $this->assertSame($this->organisation->id, $activity->properties['old']['organisation_id']);
        $this->assertNull($activity->properties['attributes']['organisation_id']);
    }

    public static function usersWhoCannotBeRemoved(): array
    {
        return [
            'the organisation sro' => ['sro'],
            'a delegate of another organisation' => ['other organisation delegate'],
            'a detached user' => ['detached'],
            'a researcher with the organisation id' => ['researcher'],
            'an unknown user' => ['unknown'],
        ];
    }

    #[DataProvider('usersWhoCannotBeRemoved')]
    public function test_only_delegates_of_the_organisation_can_be_removed(string $target): void
    {
        $user = match ($target) {
            'sro' => $this->createOrganisationUser($this->organisation, isSro: 1),
            'other organisation delegate' => $this->createOrganisationUser(Organisation::factory()->create(), isSro: 0),
            'detached' => $this->createOrganisationUser(null, isSro: 0),
            'researcher' => User::factory()->create([
                'user_group' => User::GROUP_USERS,
                'organisation_id' => $this->organisation->id,
            ]),
            'unknown' => null,
        };
        $organisationIdBefore = $user?->organisation_id;

        $removed = $this->service->removeDelegate($this->organisation, $user->id ?? 0, $this->actor);

        $this->assertFalse($removed);
        $this->assertSame($organisationIdBefore, $user?->fresh()->organisation_id);
        Queue::assertNotPushed(SendEmailJob::class);
        Notification::assertNothingSent();
    }

    public function test_a_delegate_cannot_be_removed_twice(): void
    {
        $delegate = $this->createOrganisationUser($this->organisation, isSro: 0);

        $this->assertTrue($this->service->removeDelegate($this->organisation, $delegate->id, $this->actor));
        $this->assertFalse($this->service->removeDelegate($this->organisation, $delegate->id, $this->actor));
    }

    public function test_the_removed_delegate_and_remaining_claimed_members_are_notified(): void
    {
        $delegate = $this->createOrganisationUser($this->organisation, isSro: 0);
        $sro = $this->createOrganisationUser($this->organisation, isSro: 1);
        $otherDelegate = $this->createOrganisationUser($this->organisation, isSro: 0);
        $unclaimedDelegate = $this->createOrganisationUser($this->organisation, isSro: 0, unclaimed: 1);
        $otherOrganisationDelegate = $this->createOrganisationUser(Organisation::factory()->create(), isSro: 0);

        $this->service->removeDelegate($this->organisation, $delegate->id, $this->actor);

        $emailed = [];
        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) use (&$emailed) {
            $emailed[] = $job->to['email'];

            return true;
        });
        $this->assertEqualsCanonicalizing(
            [$delegate->email, $sro->email, $otherDelegate->email, $this->actor->email],
            $emailed
        );
        $this->assertNotContains($unclaimedDelegate->email, $emailed);
        $this->assertNotContains($otherOrganisationDelegate->email, $emailed);

        Notification::assertSentTo(
            $delegate,
            OrganisationDelegates::class,
            fn (OrganisationDelegates $notification) => str_contains(
                $notification->toDatabase($delegate)['message'],
                "removed {$delegate->first_name} {$delegate->last_name} as delegate"
            )
        );
    }
}
