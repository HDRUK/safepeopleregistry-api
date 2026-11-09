<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Affiliation;
use App\Models\Custodian;
use App\Models\CustodianHasProjectOrganisation;
use App\Models\CustodianUser;
use App\Models\Organisation;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasOrganisation;
use App\Models\Registry;
use App\Models\User;
use App\Notifications\RestrictedOrganisationRequested;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use KeycloakGuard\ActingAsKeycloakUser;
use Laravel\Pennant\Feature;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationVisibilityTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    private User $otherCustodian;

    private User $researcher;

    protected function setUp(): void
    {
        parent::setUp();
        // Restricted Organisations are hidden from single-record routes by middleware
        $this->enableMiddleware();
        $this->withUsers(withCustodianUser: true);

        $custodianUser = CustodianUser::factory()->create([
            'custodian_id' => Custodian::factory()->create()->id,
        ]);
        $this->otherCustodian = User::factory()->create([
            'user_group' => User::GROUP_CUSTODIANS,
            'custodian_user_id' => $custodianUser->id,
        ]);

        $this->researcher = $this->researcherWithRegistry();
    }

    private function researcherWithRegistry(): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_USERS,
            'registry_id' => Registry::factory()->create()->id,
        ]);
    }

    private function createOrganisationAs(User $actor): Organisation
    {
        $response = $this->actingAs($actor)
            ->json('POST', self::TEST_URL . '/unclaimed_before_superadmin_invitation', [
                'organisation_name' => fake()->unique()->company(),
            ]);

        $response->assertStatus(201);

        return Organisation::findOrFail($response->decodeResponseJson()['data']);
    }

    private function indexIds(User $actor): array
    {
        return $this->actingAs($actor)
            ->json('GET', self::TEST_URL . '?per_page=1000')
            ->assertStatus(200)
            ->json('data.data.*.id');
    }

    private function assertVisibleTo(User $actor, Organisation $organisation): void
    {
        $this->assertContains($organisation->id, $this->indexIds($actor));
        $this->actingAs($actor)->json('GET', self::TEST_URL . "/{$organisation->id}")->assertStatus(200);
        $this->assertTrue($organisation->isVisibleTo($actor));
    }

    private function assertHiddenFrom(User $actor, Organisation $organisation): void
    {
        $this->assertNotContains($organisation->id, $this->indexIds($actor));
        $this->actingAs($actor)->json('GET', self::TEST_URL . "/{$organisation->id}")->assertStatus(404);
        $this->assertFalse($organisation->isVisibleTo($actor));
    }

    private function affiliate(User $researcher, Organisation $organisation, bool $currentEmployer): void
    {
        Affiliation::factory()->create([
            'organisation_id' => $organisation->id,
            'registry_id' => $researcher->registry_id,
            'current_employer' => $currentEmployer,
        ]);
    }

    private function withSroRequirement(bool $enabled, callable $callback): void
    {
        $wasEnabled = Feature::active('SroRequirementEnabled');

        try {
            $enabled
                ? Feature::activate('SroRequirementEnabled')
                : Feature::deactivate('SroRequirementEnabled');

            $callback();
        } finally {
            $wasEnabled
                ? Feature::activate('SroRequirementEnabled')
                : Feature::deactivate('SroRequirementEnabled');
        }
    }

    public function test_a_custodian_created_organisation_is_restricted_to_that_custodian(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->assertTrue($organisation->is_restricted);
        $this->assertSame($this->custodian_admin->custodian_user->custodian_id, $organisation->created_by_custodian_id);
        $this->assertSame($this->custodian_admin->id, $organisation->created_by_user_id);

        $this->assertVisibleTo($this->custodian_admin, $organisation);
        $this->assertVisibleTo($this->admin, $organisation);
        $this->assertHiddenFrom($this->otherCustodian, $organisation);
        $this->assertHiddenFrom($this->researcher, $organisation);
    }

    public function test_a_researcher_created_organisation_is_hidden_even_from_its_creator_until_it_is_their_current_employer(): void
    {
        $organisation = $this->createOrganisationAs($this->researcher);

        $this->assertTrue($organisation->is_restricted);
        $this->assertNull($organisation->created_by_custodian_id);

        $this->assertHiddenFrom($this->researcher, $organisation);
        $this->assertHiddenFrom($this->custodian_admin, $organisation);

        $this->affiliate($this->researcher, $organisation, currentEmployer: true);

        $this->assertVisibleTo($this->researcher, $organisation);
        $this->assertHiddenFrom($this->custodian_admin, $organisation);
    }

    public function test_a_past_affiliation_does_not_make_a_restricted_organisation_visible(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->affiliate($this->researcher, $organisation, currentEmployer: false);

        $this->assertHiddenFrom($this->researcher, $organisation);
    }

    public function test_members_of_a_restricted_organisation_can_see_it(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $member = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
        ]);

        $this->assertVisibleTo($member, $organisation);
    }

    public function test_an_admin_created_organisation_is_not_restricted(): void
    {
        $organisation = $this->createOrganisationAs($this->admin);

        $this->assertFalse($organisation->is_restricted);
        $this->assertVisibleTo($this->otherCustodian, $organisation);
        $this->assertVisibleTo($this->researcher, $organisation);
    }

    public function test_unrestricted_organisations_are_visible_whether_approved_or_not(): void
    {
        $organisation = Organisation::factory()->create(['system_approved' => false]);

        $this->assertVisibleTo($this->otherCustodian, $organisation);
        $this->assertVisibleTo($this->researcher, $organisation);
    }

    public function test_approval_makes_a_restricted_organisation_visible_to_everyone(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}/approved", ['system_approved' => true])
            ->assertSuccessful();

        $organisation->refresh();

        $this->assertVisibleTo($this->otherCustodian, $organisation);
        $this->assertVisibleTo($this->researcher, $organisation);
    }

    public function test_a_restricted_organisation_on_another_custodians_project_is_hidden_from_their_lists_until_approved(): void
    {
        $organisation = $this->createOrganisationAs($this->researcher);
        $otherCustodianId = $this->otherCustodian->custodian_user->custodian_id;
        $this->linkToProjectOfCustodian($organisation, $otherCustodianId);

        $projectOrganisationIds = fn () => $this->actingAs($this->otherCustodian)
            ->json('GET', "/api/v1/custodian_approvals/{$otherCustodianId}/projectOrganisations?per_page=1000")
            ->assertStatus(200)
            ->json('data.data.*.project_organisation.organisation_id');

        $this->assertNotContains($organisation->id, $projectOrganisationIds());

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}/approved", ['system_approved' => true])
            ->assertSuccessful();

        $this->assertContains($organisation->id, $projectOrganisationIds());
    }

    public function test_a_restricted_organisation_on_another_custodians_project_is_hidden_from_project_organisation_details_until_approved(): void
    {
        $organisation = $this->createOrganisationAs($this->researcher);
        $otherCustodianId = $this->otherCustodian->custodian_user->custodian_id;
        $projectOrganisation = $this->linkToProjectOfCustodian($organisation, $otherCustodianId);

        $projectOrganisationUrl = "/api/v1/project_organisations/{$projectOrganisation->id}";
        $custodianApprovalUrl = "/api/v1/custodian_approvals/{$otherCustodianId}/projectOrganisations/{$projectOrganisation->id}";

        $this->actingAs($this->otherCustodian)->json('GET', $projectOrganisationUrl)->assertStatus(404);
        $this->actingAs($this->researcher)->json('GET', $projectOrganisationUrl)->assertStatus(404);
        $this->actingAs($this->admin)->json('GET', $projectOrganisationUrl)->assertStatus(200);
        // Same as an unknown project organisation, so the restricted one's existence isn't revealed
        $this->actingAs($this->otherCustodian)->json('GET', $custodianApprovalUrl)
            ->assertStatus(200)
            ->assertJsonPath('data', null);

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}/approved", ['system_approved' => true])
            ->assertSuccessful();

        $this->actingAs($this->otherCustodian)->json('GET', $projectOrganisationUrl)
            ->assertStatus(200)
            ->assertJsonPath('data.organisation_id', $organisation->id);
        $this->actingAs($this->otherCustodian)->json('GET', $custodianApprovalUrl)
            ->assertStatus(200)
            ->assertJsonPath('data.project_organisation.organisation_id', $organisation->id);
    }

    public function test_a_restricted_organisation_is_left_out_of_another_custodians_projects_until_approved(): void
    {
        $organisation = $this->createOrganisationAs($this->researcher);
        $otherCustodianId = $this->otherCustodian->custodian_user->custodian_id;
        $this->linkToProjectOfCustodian($organisation, $otherCustodianId);

        $projectOrganisationIds = fn () => $this->actingAs($this->otherCustodian)
            ->json('GET', "/api/v1/custodians/{$otherCustodianId}/projects?per_page=1000")
            ->assertStatus(200)
            ->json('data.data.*.organisations.*.id');

        $this->assertNotContains($organisation->id, $projectOrganisationIds());

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}/approved", ['system_approved' => true])
            ->assertSuccessful();

        $this->assertContains($organisation->id, $projectOrganisationIds());
    }

    public function test_affiliations_do_not_return_the_organisation_lead_applicant_email(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);
        $this->affiliate($this->researcher, $organisation, currentEmployer: true);

        $this->actingAs($this->otherCustodian)
            ->json('GET', "/api/v1/affiliations/{$this->researcher->registry_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.data.0.organisation.id', $organisation->id)
            ->assertJsonMissingPath('data.data.0.organisation.lead_applicant_email');
    }

    private function linkToProjectOfCustodian(Organisation $organisation, int $custodianId): ProjectHasOrganisation
    {
        $project = Project::factory()->create();
        ProjectHasCustodian::create(['project_id' => $project->id, 'custodian_id' => $custodianId]);
        $projectOrganisation = ProjectHasOrganisation::create([
            'project_id' => $project->id,
            'organisation_id' => $organisation->id,
        ]);
        // Observers are disabled in tests, so create what ProjectHasOrganisationObserver would
        CustodianHasProjectOrganisation::create([
            'project_has_organisation_id' => $projectOrganisation->id,
            'custodian_id' => $custodianId,
        ]);

        return $projectOrganisation;
    }

    public function test_the_creating_custodian_is_not_returned_by_the_api(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->actingAs($this->custodian_admin)
            ->json('GET', self::TEST_URL . "/{$organisation->id}")
            ->assertStatus(200)
            ->assertJsonMissingPath('data.created_by_custodian_id')
            ->assertJsonMissingPath('data.created_by_user_id');
    }

    // The web lets a researcher pick an unapproved Organisation when the API returned
    // it because it is restricted, i.e. they may see it (e.g. their current employer)
    public function test_a_researcher_is_told_their_current_employer_is_restricted(): void
    {
        $organisation = $this->createOrganisationAs($this->researcher);
        $this->affiliate($this->researcher, $organisation, currentEmployer: true);

        $returned = collect(
            $this->actingAs($this->researcher)
                ->json('GET', self::TEST_URL . '?per_page=1000')
                ->assertStatus(200)
                ->json('data.data')
        )->firstWhere('id', $organisation->id);

        $this->assertNotNull($returned);
        $this->assertTrue($returned['is_restricted']);
        $this->assertArrayNotHasKey('created_by_custodian_id', $returned);
    }

    public function test_restriction_fields_cannot_be_set_by_the_client(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}", [
                'is_restricted' => false,
                'created_by_custodian_id' => null,
                'created_by_user_id' => $this->researcher->id,
            ])
            ->assertSuccessful();

        $organisation->refresh();
        $this->assertTrue($organisation->is_restricted);
        $this->assertSame($this->custodian_admin->custodian_user->custodian_id, $organisation->created_by_custodian_id);
        $this->assertSame($this->custodian_admin->id, $organisation->created_by_user_id);
    }

    public static function inviteActors(): array
    {
        return [
            'non-admin, sro requirement disabled' => [false, false, 403],
            'admin, sro requirement disabled' => [true, false, 201],
            'non-admin, sro requirement enabled' => [false, true, 201],
        ];
    }

    #[DataProvider('inviteActors')]
    public function test_only_admins_can_invite_an_organisation_when_the_sro_requirement_is_disabled(
        bool $asAdmin,
        bool $sroRequirement,
        int $expectedStatus
    ): void {
        $organisation = Organisation::factory()->create([
            'unclaimed' => 1,
            'system_approved' => false,
            'lead_applicant_email' => fake()->unique()->safeEmail(),
        ]);

        $this->withSroRequirement($sroRequirement, function () use ($organisation, $asAdmin, $expectedStatus) {
            $this->actingAs($asAdmin ? $this->admin : $this->custodian_admin)
                ->json('POST', self::TEST_URL . "/{$organisation->id}/invite")
                ->assertStatus($expectedStatus);
        });

        $this->assertSame(!$sroRequirement && $asAdmin, $organisation->fresh()->system_approved);
    }

    // The flag-on create flow: /unclaimed, then /invite
    private function createUnclaimedOrganisationAs(User $actor): Organisation
    {
        $response = $this->actingAs($actor)
            ->json('POST', self::TEST_URL . '/unclaimed', [
                'organisation_name' => fake()->unique()->company(),
                'lead_applicant_email' => fake()->unique()->safeEmail(),
            ]);

        $response->assertStatus(201);

        return Organisation::findOrFail($response->decodeResponseJson()['data']);
    }

    private function invite(User $actor, int $organisationId): TestResponse
    {
        return $this->actingAs($actor)->json('POST', self::TEST_URL . "/{$organisationId}/invite");
    }

    public function test_another_custodians_restricted_organisation_cannot_be_invited(): void
    {
        $this->withSroRequirement(true, function () {
            $organisation = $this->createUnclaimedOrganisationAs($this->custodian_admin);
            $state = $organisation->getState();
            Queue::fake();

            $this->invite($this->otherCustodian, $organisation->id)->assertStatus(404);
            $this->invite($this->researcherWithRegistry(), $organisation->id)->assertStatus(404);

            $this->assertFalse($organisation->hasBeenInvited());
            $this->assertSame($state, $organisation->fresh()->getState());
            Queue::assertNotPushed(SendEmailJob::class);
        });
    }

    public function test_those_who_can_see_a_restricted_organisation_can_invite_it(): void
    {
        $this->withSroRequirement(true, function () {
            $organisation = $this->createUnclaimedOrganisationAs($this->custodian_admin);

            $this->invite($this->custodian_admin, $organisation->id)
                ->assertStatus(201)
                ->assertJsonPath('data.id', $organisation->id);

            $other = $this->createUnclaimedOrganisationAs($this->custodian_admin);

            $this->invite($this->admin, $other->id)
                ->assertStatus(201)
                ->assertJsonPath('data.id', $other->id);
        });
    }

    // A researcher's "Organisation not listed?" flow invites before their affiliation exists
    public function test_a_researcher_can_invite_the_restricted_organisation_they_created_once(): void
    {
        $this->withSroRequirement(true, function () {
            $organisation = $this->createUnclaimedOrganisationAs($this->researcher);
            $this->assertSame($this->researcher->id, $organisation->created_by_user_id);

            // Only the ID, as the researcher can't otherwise see the Organisation
            $this->invite($this->researcher, $organisation->id)
                ->assertStatus(201)
                ->assertJsonPath('data', $organisation->id);

            $this->assertTrue($organisation->hasBeenInvited());

            $this->invite($this->researcher, $organisation->id)->assertStatus(404);
        });
    }

    public function test_inviting_an_unknown_organisation_looks_the_same_as_a_restricted_one(): void
    {
        $this->withSroRequirement(true, function () {
            $this->invite($this->custodian_admin, Organisation::max('id') + 1000)->assertStatus(404);
        });
    }

    public function test_users_cannot_be_invited_into_another_custodians_restricted_organisation(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);
        Queue::fake();

        $inviteUser = fn (User $actor) => $this->actingAs($actor)
            ->json('POST', self::TEST_URL . "/{$organisation->id}/custodian_invite_user", [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
            ]);

        $inviteUser($this->otherCustodian)->assertStatus(404);
        Queue::assertNotPushed(SendEmailJob::class);

        $inviteUser($this->custodian_admin)->assertStatus(201);
    }

    private function requestOrganisation(User $actor, array $payload): TestResponse
    {
        return $this->actingAs($actor)
            ->json('POST', self::TEST_URL . '/unclaimed_before_superadmin_invitation', $payload);
    }

    private function assertAdminsToldItIsWanted(User $requester, Organisation $organisation): void
    {
        Notification::assertSentTo(
            $this->admin,
            RestrictedOrganisationRequested::class,
            fn (RestrictedOrganisationRequested $notification) =>
                $notification->toDatabase($this->admin)['details']['organisation_id'] === $organisation->id
                && str_contains($notification->generateMessage(), $requester->first_name)
        );
    }

    public static function hiddenDuplicatePayloads(): array
    {
        return [
            'matched by email' => [true],
            'matched by name, without email' => [false],
        ];
    }

    // The custodian can't see, open or attach the match, so they're told it's awaiting
    // approval and admins are told it's wanted, rather than leaving them stuck
    #[DataProvider('hiddenDuplicatePayloads')]
    public function test_requesting_another_custodians_restricted_organisation_notifies_admins(bool $withEmail): void
    {
        $email = fake()->unique()->safeEmail();
        $name = fake()->unique()->company();
        $payload = $withEmail
            ? ['organisation_name' => $name, 'lead_applicant_email' => $email]
            : ['organisation_name' => $name];

        $this->requestOrganisation($this->custodian_admin, $payload)->assertStatus(201);
        $organisation = Organisation::where('organisation_name', $name)->firstOrFail();
        $count = Organisation::count();
        Notification::fake();

        // Same code whatever matched, so nothing more about the hidden Organisation is revealed
        $this->requestOrganisation($this->otherCustodian, $withEmail
            ? ['organisation_name' => fake()->unique()->company(), 'lead_applicant_email' => $email]
            : $payload)
            ->assertStatus(409)
            ->assertJsonPath('data', 'pending_approval');

        $this->assertSame($count, Organisation::count());
        $this->assertAdminsToldItIsWanted($this->otherCustodian, $organisation);
    }

    public function test_a_visible_duplicate_still_gets_the_usual_conflict(): void
    {
        $payload = [
            'organisation_name' => fake()->unique()->company(),
            'lead_applicant_email' => fake()->unique()->safeEmail(),
        ];
        $this->requestOrganisation($this->custodian_admin, $payload)->assertStatus(201);
        $organisation = Organisation::where('lead_applicant_email', $payload['lead_applicant_email'])->firstOrFail();
        Notification::fake();

        $this->requestOrganisation($this->custodian_admin, $payload)
            ->assertStatus(409)
            ->assertJsonPath('data', 'unclaimed_duplicate');
        $this->requestOrganisation($this->admin, $payload)
            ->assertStatus(409)
            ->assertJsonPath('data', 'unclaimed_duplicate');

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}/approved", ['system_approved' => true])
            ->assertSuccessful();

        $this->requestOrganisation($this->otherCustodian, $payload)
            ->assertStatus(409)
            ->assertJsonPath('data', 'unclaimed_duplicate');

        Notification::assertNothingSent();
    }

    public function test_inviting_another_custodians_restricted_organisation_directly_notifies_admins(): void
    {
        $this->withSroRequirement(true, function () {
            $organisation = $this->createUnclaimedOrganisationAs($this->custodian_admin);
            $count = Organisation::count();
            Notification::fake();

            $unclaimed = fn (array $payload) => $this->actingAs($this->otherCustodian)
                ->json('POST', self::TEST_URL . '/unclaimed', $payload)
                ->assertStatus(409)
                ->assertJsonPath('data', 'pending_approval');

            // Would otherwise be 'duplicate'
            $unclaimed([
                'organisation_name' => $organisation->organisation_name,
                'lead_applicant_email' => $organisation->lead_applicant_email,
            ]);
            // Would otherwise be 'email_in_use'
            $unclaimed([
                'organisation_name' => fake()->unique()->company(),
                'lead_applicant_email' => $organisation->lead_applicant_email,
            ]);

            $this->assertSame($count, Organisation::count());
            $this->assertAdminsToldItIsWanted($this->otherCustodian, $organisation);
        });
    }

    public function test_a_restricted_organisations_invite_cannot_be_resent_by_those_who_cannot_see_it(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->actingAs($this->otherCustodian)
            ->json('PATCH', self::TEST_URL . "/{$organisation->id}/resendInvite")
            ->assertStatus(404);
    }
}
