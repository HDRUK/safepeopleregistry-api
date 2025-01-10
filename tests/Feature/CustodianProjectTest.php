<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Project;
use App\Models\Custodian;
use App\Models\ProjectHasCustodian;
use Database\Seeders\CustodianSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianProjectTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodians';

    private $user = null;
    private $projectUniqueId = '';
    private $organisationUniqueId = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            UserSeeder::class,
            CustodianSeeder::class,
            ProjectSeeder::class
        ]);

        $this->user = User::where('id', 1)->first();

        $this->custodian = Custodian::first();

        $projects = Project::all();
        foreach ($projects as $project) {
            ProjectHasCustodian::create(
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
        $nApproved = ProjectHasCustodian::where("approved", true)->count();
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
        $nUnapproved = ProjectHasCustodian::where("approved", false)->count();
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
