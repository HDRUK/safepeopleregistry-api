<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\Project;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\Affiliation;
use App\Models\ProjectRole;
use App\Models\Organisation;
use App\Models\CustodianUser;
use App\Models\ProjectHasUser;
use App\Jobs\SendEmailJob;
use App\Models\CustodianHasProjectUser;
use App\Enums\AffiliationEndDateSource;
use App\Notifications\Affiliations\AffiliationEnded;
use App\Notifications\Affiliations\AffiliationEndingSoon;
use App\Notifications\Affiliations\AffiliationEndDateRecorded;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\Traits\Authorisation;

class AffiliationLeaverTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/affiliations';

    private Organisation $organisation;
    private User $delegate;
    private User $researcher;
    private int $custodianId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers(withCustodianUser: true);
        Notification::fake();

        $this->organisation = Organisation::factory()->create();
        $this->delegate = $this->createOrganisationUser($this->organisation);
        $this->researcher = $this->createResearcher();
        $this->custodianId = $this->custodian_admin->custodian_user->custodian_id;
    }

    private function assertEmailsSentTo(User $recipient, int $times): void
    {
        Queue::assertPushed(SendEmailJob::class, fn (SendEmailJob $job) => $job->to['id'] === $recipient->id);
        $this->assertCount(
            $times,
            Queue::pushed(SendEmailJob::class, fn (SendEmailJob $job) => $job->to['id'] === $recipient->id)
        );
    }

    private function createOrganisationUser(Organisation $organisation): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_sro' => 0,
            'is_delegate' => 1,
            'unclaimed' => 0,
        ]);
    }

    private function createResearcher(): User
    {
        $registry = Registry::factory()->create();

        return User::factory()->create([
            'user_group' => User::GROUP_USERS,
            'registry_id' => $registry->id,
            'unclaimed' => 0,
        ]);
    }

    private function createAffiliation(string $state, array $attributes = []): Affiliation
    {
        $affiliation = Affiliation::factory()->create([
            'organisation_id' => $this->organisation->id,
            'registry_id' => $this->researcher->registry_id,
            'from' => Carbon::today()->subYear()->toDateString(),
            'current_employer' => true,
            ...$attributes,
        ]);
        $affiliation->setState($state);

        return $affiliation;
    }

    private function addToProject(Affiliation $affiliation): CustodianHasProjectUser
    {
        $project = Project::factory()->create();
        $project->custodians()->attach($this->custodianId);
        $projectHasUser = ProjectHasUser::create([
            'project_id' => $project->id,
            'user_digital_ident' => Registry::find($affiliation->registry_id)->digi_ident,
            'project_role_id' => ProjectRole::first()->id,
            'affiliation_id' => $affiliation->id,
        ]);

        $projectUser = CustodianHasProjectUser::create([
            'project_has_user_id' => $projectHasUser->id,
            'custodian_id' => $this->custodianId,
        ]);
        $projectUser->setState(State::STATE_VALIDATION_COMPLETE);

        return $projectUser;
    }

    private function recordEndDate(User $actor, Affiliation $affiliation, ?string $endDate)
    {
        return $this->actingAs($actor)
            ->json('PUT', self::TEST_URL . "/{$affiliation->id}/end_date", ['to' => $endDate]);
    }

    public function test_an_organisation_recording_a_past_end_date_ends_the_affiliation_and_its_projects(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);
        $projectUser = $this->addToProject($affiliation);
        $endDate = Carbon::today()->subWeek()->toDateString();

        $response = $this->recordEndDate($this->delegate, $affiliation, $endDate);

        $response->assertStatus(200);

        $affiliation->refresh();
        $this->assertEquals(State::STATE_AFFILIATION_LEFT, $affiliation->getState());
        $this->assertEquals($endDate, $affiliation->to);
        $this->assertFalse($affiliation->current_employer);
        $this->assertNotNull($affiliation->left_at);
        $this->assertNotNull($affiliation->end_date_recorded_at);
        $this->assertEquals($this->delegate->id, $affiliation->end_date_recorded_by);
        $this->assertEquals(AffiliationEndDateSource::Organisation, $affiliation->end_date_source);

        $projectUser->refresh();
        $this->assertEquals(State::STATE_USER_LEFT_PROJECT, $projectUser->getState());
        $this->assertNotNull($projectUser->access_revocation_required_at);
        $this->assertNull($projectUser->access_revoked_at);

        // History is kept: nothing is deleted.
        $this->assertDatabaseHas('project_has_users', ['affiliation_id' => $affiliation->id]);

        Notification::assertSentTo($this->researcher, AffiliationEndDateRecorded::class);
        Notification::assertSentTo($this->researcher, AffiliationEnded::class);
        Notification::assertSentTo($this->delegate, AffiliationEnded::class);
        Notification::assertSentTo($this->custodian_admin, AffiliationEnded::class);
        $this->assertEmailsSentTo($this->researcher, 1);
        $this->assertEmailsSentTo($this->delegate, 1);
        $this->assertEmailsSentTo($this->custodian_admin, 1);
    }

    public function test_a_user_recording_a_future_end_date_does_not_end_the_affiliation_yet(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, [
            'end_date_warning_sent_at' => Carbon::now(),
        ]);
        $endDate = Carbon::today()->addMonths(2)->toDateString();

        $response = $this->recordEndDate($this->researcher, $affiliation, $endDate);

        $response->assertStatus(200);

        $affiliation->refresh();
        $this->assertEquals(State::STATE_AFFILIATION_APPROVED, $affiliation->getState());
        $this->assertEquals($endDate, $affiliation->to);
        $this->assertTrue($affiliation->current_employer);
        $this->assertEquals(AffiliationEndDateSource::User, $affiliation->end_date_source);
        // A new date deserves a new warning.
        $this->assertNull($affiliation->end_date_warning_sent_at);

        Notification::assertSentTo($this->delegate, AffiliationEndDateRecorded::class);
        Notification::assertNothingSentTo($this->researcher);
    }

    public function test_a_future_end_date_can_be_removed(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, [
            'to' => Carbon::today()->addMonth()->toDateString(),
            'end_date_source' => AffiliationEndDateSource::User,
        ]);

        $response = $this->recordEndDate($this->researcher, $affiliation, null);

        $response->assertStatus(200);

        $affiliation->refresh();
        $this->assertNull($affiliation->to);
        $this->assertNull($affiliation->end_date_source);
        $this->assertNull($affiliation->end_date_recorded_at);
    }

    public function test_the_organisations_end_date_wins_over_the_users(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, [
            'to' => Carbon::today()->addMonth()->toDateString(),
            'end_date_source' => AffiliationEndDateSource::User,
        ]);
        $organisationDate = Carbon::today()->addMonths(2)->toDateString();

        $this->recordEndDate($this->delegate, $affiliation, $organisationDate)->assertStatus(200);

        $response = $this->recordEndDate($this->researcher, $affiliation, Carbon::today()->addMonths(3)->toDateString());

        $response->assertStatus(422);
        $this->assertEquals($organisationDate, $affiliation->refresh()->to);
    }

    public function test_an_ended_affiliation_cannot_be_given_a_new_end_date(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_LEFT, [
            'to' => Carbon::today()->subMonth()->toDateString(),
        ]);

        $response = $this->recordEndDate($this->delegate, $affiliation, Carbon::today()->toDateString());

        $response->assertStatus(422);
    }

    public function test_an_end_date_cannot_be_before_the_start_date(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);

        $response = $this->recordEndDate($this->delegate, $affiliation, Carbon::today()->subYears(2)->toDateString());

        $response->assertStatus(422);
    }

    public function test_another_organisation_cannot_record_an_end_date(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);
        $otherDelegate = $this->createOrganisationUser(Organisation::factory()->create());

        $response = $this->recordEndDate($otherDelegate, $affiliation, Carbon::today()->toDateString());

        $response->assertStatus(403);
        $this->assertEquals(State::STATE_AFFILIATION_APPROVED, $affiliation->refresh()->getState());
    }

    public function test_a_user_can_delete_a_fresh_affiliation_they_added_by_mistake(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_PENDING);

        $response = $this->actingAs($this->researcher)
            ->json('DELETE', self::TEST_URL . "/{$affiliation->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('affiliations', ['id' => $affiliation->id]);
    }

    public function test_an_approved_affiliation_cannot_be_deleted(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);

        $response = $this->actingAs($this->researcher)
            ->json('DELETE', self::TEST_URL . "/{$affiliation->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('affiliations', ['id' => $affiliation->id]);
    }

    public function test_an_affiliation_on_a_project_cannot_be_deleted(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_PENDING);
        $this->addToProject($affiliation);

        $response = $this->actingAs($this->researcher)
            ->json('DELETE', self::TEST_URL . "/{$affiliation->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('affiliations', ['id' => $affiliation->id]);
    }

    public function test_an_organisation_cannot_delete_an_affiliation(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_PENDING);

        $response = $this->actingAs($this->delegate)
            ->json('DELETE', self::TEST_URL . "/{$affiliation->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('affiliations', ['id' => $affiliation->id]);
    }

    public function test_an_approved_affiliation_cannot_be_edited(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);

        $response = $this->actingAs($this->researcher)
            ->json('PUT', self::TEST_URL . "/{$affiliation->id}", [
                'role' => 'Something else',
                'current_employer' => true,
            ]);

        $response->assertStatus(422);
    }

    public function test_an_affiliation_cannot_be_added_with_a_past_end_date(): void
    {
        $response = $this->actingAs($this->researcher)
            ->json('POST', self::TEST_URL . "/{$this->researcher->registry_id}", [
                'organisation_id' => $this->organisation->id,
                'member_id' => fake()->uuid(),
                'relationship' => 'employee',
                'from' => Carbon::today()->subYears(5)->toDateString(),
                'to' => Carbon::today()->subYear()->toDateString(),
                'department' => 'Research',
                'role' => 'Researcher',
                'email' => $this->researcher->email,
                'ror' => generateRorID(),
            ]);

        $response->assertStatus(422);
    }

    public function test_an_affiliation_cannot_be_added_with_an_unreadable_date(): void
    {
        $response = $this->actingAs($this->researcher)
            ->json('POST', self::TEST_URL . "/{$this->researcher->registry_id}", [
                'organisation_id' => $this->organisation->id,
                'member_id' => fake()->uuid(),
                'relationship' => 'employee',
                'from' => 'not a date',
                'department' => 'Research',
                'role' => 'Researcher',
                'email' => $this->researcher->email,
                'ror' => generateRorID(),
            ]);

        $response->assertStatus(400);
    }

    public function test_an_affiliation_cannot_be_edited_with_an_unreadable_date(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_PENDING);

        $response = $this->actingAs($this->researcher)
            ->json('PUT', self::TEST_URL . "/{$affiliation->id}", [
                'to' => 'not a date',
            ]);

        $response->assertStatus(400);
    }

    public function test_an_affiliation_can_be_added_with_a_future_end_date(): void
    {
        $endDate = Carbon::today()->addYear()->toDateString();

        $response = $this->actingAs($this->researcher)
            ->json('POST', self::TEST_URL . "/{$this->researcher->registry_id}", [
                'organisation_id' => $this->organisation->id,
                'member_id' => fake()->uuid(),
                'relationship' => 'employee',
                'from' => Carbon::today()->subYears(5)->toDateString(),
                'to' => $endDate . ' 00:00:00',
                'department' => 'Research',
                'role' => 'Researcher',
                'email' => $this->researcher->email,
                'ror' => generateRorID(),
            ]);

        $response->assertStatus(201);

        $affiliation = Affiliation::findOrFail($response->decodeResponseJson()['data']);
        $this->assertEquals($endDate, $affiliation->to);
        $this->assertTrue($affiliation->current_employer);
        $this->assertEquals(AffiliationEndDateSource::User, $affiliation->end_date_source);
    }

    public function test_the_scheduler_ends_affiliations_whose_end_date_has_arrived(): void
    {
        $today = Carbon::today()->toDateString();
        $approved = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, ['to' => $today]);
        $verified = $this->createAffiliation(State::STATE_AFFILIATION_PENDING, ['to' => $today]);
        $unverified = $this->createAffiliation(State::STATE_AFFILIATION_EMAIL_VERIFY, ['to' => $today]);
        $rejected = $this->createAffiliation(State::STATE_AFFILIATION_REJECTED, ['to' => $today]);
        $future = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, [
            'to' => Carbon::today()->addYear()->toDateString(),
        ]);

        $this->artisan('app:checking-affiliations')->assertExitCode(0);

        $this->assertEquals(State::STATE_AFFILIATION_LEFT, $approved->refresh()->getState());
        $this->assertEquals(State::STATE_AFFILIATION_LEFT, $verified->refresh()->getState());
        $this->assertEquals(State::STATE_AFFILIATION_NEVER_VERIFIED, $unverified->refresh()->getState());
        $this->assertEquals(State::STATE_AFFILIATION_REJECTED, $rejected->refresh()->getState());
        $this->assertEquals(State::STATE_AFFILIATION_APPROVED, $future->refresh()->getState());
    }

    public function test_the_deployment_step_ends_legacy_affiliations_quietly(): void
    {
        $pastDate = Carbon::today()->subYears(3)->toDateString();
        $historic = $this->createAffiliation(State::STATE_AFFILIATION_PENDING, ['to' => $pastDate]);
        $projectUser = $this->addToProject($historic);
        $unverified = $this->createAffiliation(State::STATE_AFFILIATION_EMAIL_VERIFY, ['to' => $pastDate]);
        $future = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, [
            'to' => Carbon::today()->addYear()->toDateString(),
        ]);
        Queue::fake();

        $step = require base_path('database/deployment-steps/2026_10_30_100200_end_legacy_affiliations_quietly.php');
        $step->handle();

        $historic->refresh();
        $this->assertEquals(State::STATE_AFFILIATION_LEFT, $historic->getState());
        $this->assertFalse($historic->current_employer);
        $this->assertNotNull($historic->left_at);
        $this->assertEquals(State::STATE_AFFILIATION_NEVER_VERIFIED, $unverified->refresh()->getState());
        $this->assertEquals(State::STATE_AFFILIATION_APPROVED, $future->refresh()->getState());

        // Projects are left, but Custodians are not asked to revoke access for a legacy leaver.
        $projectUser->refresh();
        $this->assertEquals(State::STATE_USER_LEFT_PROJECT, $projectUser->getState());
        $this->assertNull($projectUser->access_revocation_required_at);

        // Nobody is notified, and no emails or webhooks are queued.
        Notification::assertNothingSent();
        Queue::assertNothingPushed();

        // The scheduler then has nothing left to end.
        $this->artisan('app:checking-affiliations')->assertExitCode(0);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_the_scheduler_warns_once_that_an_affiliation_is_ending_soon(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED, [
            'to' => Carbon::today()->addDays(10)->toDateString(),
        ]);

        $this->artisan('app:checking-affiliations')->assertExitCode(0);
        $this->artisan('app:checking-affiliations')->assertExitCode(0);

        $this->assertNotNull($affiliation->refresh()->end_date_warning_sent_at);
        Notification::assertSentToTimes($this->researcher, AffiliationEndingSoon::class, 1);
        $this->assertEmailsSentTo($this->researcher, 1);
    }

    public function test_the_scheduler_reminds_custodians_to_revoke_access(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);
        $projectUser = $this->addToProject($affiliation);
        $projectUser->access_revocation_required_at = Carbon::now()->subDays(8);
        $projectUser->save();

        $this->artisan('app:checking-affiliations')->assertExitCode(0);
        $this->artisan('app:checking-affiliations')->assertExitCode(0);

        $this->assertNotNull($projectUser->refresh()->access_revocation_reminded_at);
        $this->assertEmailsSentTo($this->custodian_admin, 1);
    }

    public function test_a_custodian_can_list_and_confirm_access_revocations(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);
        $projectUser = $this->addToProject($affiliation);
        $this->recordEndDate($this->delegate, $affiliation, Carbon::today()->toDateString())->assertStatus(200);

        $response = $this->actingAs($this->custodian_admin)
            ->json('GET', "/api/v1/custodians/{$this->custodianId}/access_revocations");

        $response->assertStatus(200);
        $this->assertEquals([$projectUser->id], collect($response->decodeResponseJson()['data']['data'])->pluck('id')->all());

        $this->actingAs($this->custodian_admin)
            ->json('PUT', "/api/v1/custodians/{$this->custodianId}/access_revocations/{$projectUser->id}")
            ->assertStatus(200);

        $projectUser->refresh();
        $this->assertNotNull($projectUser->access_revoked_at);
        $this->assertEquals($this->custodian_admin->id, $projectUser->access_revoked_by);

        // Already confirmed.
        $this->actingAs($this->custodian_admin)
            ->json('PUT', "/api/v1/custodians/{$this->custodianId}/access_revocations/{$projectUser->id}")
            ->assertStatus(404);
    }

    public function test_another_custodian_cannot_confirm_access_revocations(): void
    {
        $affiliation = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);
        $projectUser = $this->addToProject($affiliation);
        $this->recordEndDate($this->delegate, $affiliation, Carbon::today()->toDateString())->assertStatus(200);

        $otherCustodian = Custodian::factory()->create();
        $otherCustodianUser = CustodianUser::factory()->create(['custodian_id' => $otherCustodian->id]);
        $otherUser = User::factory()->create([
            'user_group' => User::GROUP_CUSTODIANS,
            'custodian_user_id' => $otherCustodianUser->id,
        ]);

        $this->actingAs($otherUser)
            ->json('PUT', "/api/v1/custodians/{$this->custodianId}/access_revocations/{$projectUser->id}")
            ->assertStatus(403);

        $this->assertNull($projectUser->refresh()->access_revoked_at);
    }

    public function test_users_can_only_be_added_to_a_project_through_a_current_affiliation(): void
    {
        $left = $this->createAffiliation(State::STATE_AFFILIATION_LEFT, [
            'to' => Carbon::today()->subMonth()->toDateString(),
        ]);
        $current = $this->createAffiliation(State::STATE_AFFILIATION_APPROVED);
        $project = Project::factory()->create();
        $project->custodians()->attach($this->custodianId);

        $response = $this->actingAs($this->custodian_admin)
            ->json('GET', "/api/v1/projects/{$project->id}/all_users/{$this->researcher->id}");

        $response->assertStatus(200);
        $this->assertEquals([$current->id], collect($response->decodeResponseJson()['data'])->pluck('affiliation_id')->all());

        $addThrough = fn (Affiliation $affiliation) => $this->actingAs($this->custodian_admin)
            ->json('PUT', "/api/v1/projects/{$project->id}/all_users", [
                'users' => [[
                    'project_user_id' => null,
                    'registry_id' => $this->researcher->registry_id,
                    'affiliation_id' => $affiliation->id,
                    'role' => ['id' => ProjectRole::first()->id],
                ]],
            ]);

        $addThrough($left)->assertStatus(422);
        $addThrough($current)->assertStatus(200);
    }
}
