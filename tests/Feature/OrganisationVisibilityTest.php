<?php

namespace Tests\Feature;

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

        $project = Project::factory()->create();
        ProjectHasCustodian::create(['project_id' => $project->id, 'custodian_id' => $otherCustodianId]);
        $projectOrganisation = ProjectHasOrganisation::create([
            'project_id' => $project->id,
            'organisation_id' => $organisation->id,
        ]);
        // Observers are disabled in tests, so create what ProjectHasOrganisationObserver would
        CustodianHasProjectOrganisation::create([
            'project_has_organisation_id' => $projectOrganisation->id,
            'custodian_id' => $otherCustodianId,
        ]);

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

    public function test_restriction_fields_cannot_be_set_by_the_client(): void
    {
        $organisation = $this->createOrganisationAs($this->custodian_admin);

        $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$organisation->id}", [
                'is_restricted' => false,
                'created_by_custodian_id' => null,
            ])
            ->assertSuccessful();

        $organisation->refresh();
        $this->assertTrue($organisation->is_restricted);
        $this->assertSame($this->custodian_admin->custodian_user->custodian_id, $organisation->created_by_custodian_id);
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
}
