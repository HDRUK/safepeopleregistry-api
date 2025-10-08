<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\State;
use App\Models\Project;
use App\Models\Custodian;
use App\Models\Organisation;
use Tests\Traits\Authorisation;
use App\Models\ProjectHasOrganisation;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\CustodianHasProjectOrganisation;

class CustodianProjectOrganisationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodian_approvals';

    protected Custodian $custodian;
    protected Project $project;
    protected Organisation $organisation;
    protected ProjectHasOrganisation $projectOrganisation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withUsers();

        $this->project = Project::first();
        $this->organisation = Organisation::first();
        $this->projectOrganisation = ProjectHasOrganisation::create([
            'project_id' => $this->project->id,
            'organisation_id' =>  $this->organisation->id,
        ]);

        CustodianHasProjectOrganisation::create([
            'custodian_id' => Custodian::first()->id,
            'project_has_organisation_id' => $this->projectOrganisation->id,
        ]);
    }

    public function test_index_returns_all_project_organisations_for_custodian(): void
    {
        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/1/projectOrganisations");

        $response->assertStatus(200);
    }

    public function test_index_cannot_show_all_project_organisations_for_custodian(): void
    {
        $lastestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $lastestCustodian ? $lastestCustodian->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$custodianIdTest}/projectOrganisations");

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_show_returns_specific_project_organisations_for_custodian(): void
    {
        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/1/projectOrganisations/{$this->projectOrganisation->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_show_returns_specific_project_user_for_custodian(): void
    {
        $lastestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $lastestCustodian ? $lastestCustodian->id + 1 : 1;

        $lastestProjectHasOrganisation = ProjectHasOrganisation::query()->orderBy('id', 'desc')->first();
        $projectHasOrganisationIdTest = $lastestProjectHasOrganisation ? $lastestProjectHasOrganisation->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json('GET', self::TEST_URL . "/{$custodianIdTest}/projectOrganisations/{$projectHasOrganisationIdTest}");

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_update_project_organisations_approval_for_custodian(): void
    {
        $payload = [
            'status' => State::STATE_FORM_RECEIVED,
        ];

        $response = $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/1/projectOrganisations/{$this->projectOrganisation->id}", $payload);

        $response->assertStatus(200);
    }

    public function test_cannot_update_project_organisations_approval_for_custodian(): void
    {
        $lastestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $lastestCustodian ? $lastestCustodian->id + 1 : 1;

        $lastestProjectHasOrganisation = ProjectHasOrganisation::query()->orderBy('id', 'desc')->first();
        $projectHasOrganisationIdTest = $lastestProjectHasOrganisation ? $lastestProjectHasOrganisation->id + 1 : 1;

        $payload = [
            'status' => State::STATE_FORM_RECEIVED,
        ];

        $response = $this->actingAs($this->admin)
            ->json('PUT', self::TEST_URL . "/{$custodianIdTest}/projectOrganisations/{$projectHasOrganisationIdTest}", $payload);

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
