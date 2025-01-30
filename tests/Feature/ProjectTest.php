<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\ProjectHasUser;
use App\Models\ProjectHasCustodian;
use Database\Seeders\UserSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\BaseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Spatie\WebhookServer\CallWebhookJob;
use Illuminate\Support\Facades\Queue;

class ProjectTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/projects';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            PermissionSeeder::class,
            BaseDemoSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_search_on_project_name(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '?title[]=social',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) === 1);
        $this->assertTrue($content['data'][0]['title'] === 'Social Media Influence on Mental Health Trends Among Teenagers');

    }

    public function test_the_application_can_list_projects(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_projects(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'unique_id' => Str::random(30),
                'title' => 'This is a Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $content['data']
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_projects(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'unique_id' => Str::random(30),
                'title' => 'Test Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_projects(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'unique_id' => Str::random(30),
                'title' => 'Test Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $newDate = Carbon::now()->addYears(2);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'],
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
            ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['title'], 'This is an Old Project');
        $this->assertEquals(Carbon::parse($content['end_date'])->toDateTimeString(), $newDate->toDateTimeString());
    }

    public function test_the_application_can_delete_projects(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'unique_id' => Str::random(30),
                'title' => 'Test Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content['data']
            );

        $response->assertStatus(200);
    }

    public function test_the_application_can_show_users_in_project(): void
    {
        $registry = Registry::first();
        $digi_ident = $registry->digi_ident;

        $project = Project::first();
        $projectId = $project->id;

        ProjectHasUser::create(['project_id' => $projectId, 'user_digital_ident' => $digi_ident, 'project_role_id' => 1]);


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1/users',
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertCount(5, $response['data']['data']);
        $this->assertArrayHasKey('registry', $response['data']['data'][0]);
    }

    public function test_the_application_can_show_user_approved_projects(): void
    {

        ProjectHasUser::truncate();
        ProjectHasCustodian::truncate();

        $registry = Registry::first();
        $digi_ident = $registry->digi_ident;

        $project = Project::first();
        $projectId = $project->id;
        $custodianId = Custodian::first()->id;

        ProjectHasUser::create(['project_id' => $projectId, 'user_digital_ident' => $digi_ident, 'project_role_id' => 1, 'approved' => true]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/user/' . $registry->id . '/approved'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);

        ProjectHasCustodian::create(['project_id' => $projectId,'custodian_id' => $custodianId, 'approved' => true]);
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/user/' . $registry->id . '/approved'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertCount(1, $response['data']);
        $this->assertArrayHasKey('title', $response['data'][0]);
        $this->assertEquals($project->title, $response['data'][0]['title']);
    }

    public function test_the_application_can_create_webhooks_on_user_leaving_project(): void
    {
        Queue::fake();

        // Flush and create anew
        ProjectHasUser::truncate();
        ProjectHasCustodian::truncate();

        $registry = Registry::first();
        $project = Project::first();
        $custodian = Custodian::first();

        ProjectHasCustodian::create([
            'project_id' => $project->id,
            'custodian_id' => 1,
            'approved' => true
        ]);

        ProjectHasUser::create([
            'project_id' => $project->id,
            'user_digital_ident' => $registry->digi_ident,
            'project_role_id' => 7,
        ]);

        // Now remove said user from project
        $phu = ProjectHasUser::where([
            'project_id' => $project->id,
            'user_digital_ident' => $registry->digi_ident,
            'project_role_id' => 7,
        ])->first();

        if ($phu) {
            $phu->delete();
        }

        // Observer should have kicked in, so let's ensure the
        // job for the webhook was created
        Queue::assertPushed(CallWebhookJob::class);
    }
}
