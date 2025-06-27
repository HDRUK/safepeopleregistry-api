<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
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

    private $projectUniqueId = '';
    private $organisationUniqueId = '';
    protected $custodian;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        $this->custodian = Custodian::where('id', $this->custodian_admin->custodian_user->custodian_id)
            ->first();

        $projects = Project::all();
        ProjectHasCustodian::truncate();

        foreach ($projects as $project) {
            ProjectHasCustodian::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'custodian_id' => $this->custodian->id,
                ]
            );
        }
    }

    public function test_the_application_can_list_custodian_projects(): void
    {
        $nTotal = ProjectHasCustodian::count();
        $response = $this->actingAs($this->custodian_admin)
            ->json(
                'GET',
                self::TEST_URL . '/' . $this->custodian->id . '/projects',
            );
        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('data', $response['data']);

        $this->assertTrue(count($response['data']['data']) === $nTotal);
    }
}
