<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Project;
use App\Models\Custodian;
use Illuminate\Support\Str;
use App\Models\Organisation;
use Tests\Traits\Authorisation;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasSponsorship;
use KeycloakGuard\ActingAsKeycloakUser;

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

    public function test_the_application_can_update_projects_with_sponsor(): void
    {
        $response = $this->actingAs($this->custodian_admin)
            ->json(
                'POST',
                self::TEST_URL . '/' . $this->custodian->id . '/projects',
                [
                    'title' => 'New project',
                    'request_category_type' => '',
                    'start_date' => '',
                    'end_date' => '',
                    'lay_summary' => '',
                    'public_benefit' => '',
                    'technical_summary' => '',
                    'status' => 'project_pending',
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $content = $response->decodeResponseJson();
        $projectId = $content['data'];
        $newDate = Carbon::now()->addYears(2);
        $organisation = Organisation::query()->orderBy('id', 'desc')->first();

        $responseUpdateProject = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                '/api/v1/projects/' . $projectId,
                [
                    'unique_id' => Str::random(30),
                    'title' => 'This is an Old Project',
                    'lay_summary' => 'Sample lay summary',
                    'public_benefit' => 'This will benefit the public',
                    'request_category_type' => 'Category type',
                    'technical_summary' => 'Sample technical summary',
                    'other_approval_committees' => 'Bodies on a board panel',
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addYears(2),
                    'affiliate_id' => 1,
                    'status' => 'project_approved',
                    'sponsor_id' => $organisation->id,
                ]
            );
        $responseUpdateProject->assertStatus(200);

        $contentUpdateProject = $responseUpdateProject->decodeResponseJson()['data'];
        $this->assertEquals($contentUpdateProject['title'], 'This is an Old Project');
        $this->assertEquals(Carbon::parse($contentUpdateProject['end_date'])->toDateTimeString(), $newDate->toDateTimeString());

        $projectHasSponsor = ProjectHasSponsorship::where('project_id', $projectId)->first();
        $this->assertEquals((int)$projectHasSponsor->sponsor_id, (int)$organisation->id);
    }

}
