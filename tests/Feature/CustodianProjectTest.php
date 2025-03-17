<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Project;
use App\Models\Custodian;
use App\Models\ProjectHasCustodian;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianProjectTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodians';

    private $user = null;
    private $projectUniqueId = '';
    private $organisationUniqueId = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_group', 'CUSTODIAN')->first();
        $this->custodian = Custodian::first();

        $projects = Project::all();
        ProjectHasCustodian::truncate();

        foreach ($projects as $project) {
            ProjectHasCustodian::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'custodian_id' => $this->custodian->id,
                    'approved' => (bool)rand(0, 1),
                ]
            );
        }
    }

    public function test_the_application_can_list_custodian_projects(): void
    {
        $nTotal = ProjectHasCustodian::count();
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $this->custodian->id . '/projects',
            );
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('data', $response['data']);

        $this->assertTrue(count($response['data']['data']) === $nTotal);

    }

    public function test_the_application_can_list_approved_custodian_projects(): void
    {
        $nApproved = ProjectHasCustodian::where("approved", 1)->count();
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $this->custodian->id . '/projects?approved=1',
        );
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertTrue(count($response['data']['data']) === $nApproved);
    }

    public function test_the_application_can_list_unapproved_custodian_projects(): void
    {
        $nUnapproved = ProjectHasCustodian::where("approved", 0)->count();
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $this->custodian->id . '/projects?approved=0',
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertTrue(count($response['data']['data']) === $nUnapproved);
    }

}
