<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Custodian;
use App\Models\Registry;
use App\Models\ProjectHasUser;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\ValidationLog;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Carbon\Carbon;

class ValidationLogTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->registry = Registry::factory()->create();
        $this->user->update(['registry_id' => $this->registry->id]);
        $this->custodian = Custodian::factory()->create();
        $this->project = Project::factory()->create();

        ValidationLog::truncate();

        $this->enableObservers();
    }

    public function test_it_creates_validation_logs_when_a_user_is_added_to_a_project()
    {
        $defaultActions = ProjectHasUser::getDefaultActions();

        $this->assertNotEmpty($defaultActions, 'Expected getDefaultActions to return at least one action.');
        $this->assertDatabaseEmpty('validation_logs');

        ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' => $this->user->registry->digi_ident,
        ]);

        $this->assertDatabaseEmpty('validation_logs');


        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $this->custodian->id ,
        ]);


        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ]);
        }
    }

    public function test_it_adds_more_validation_logs_when_custodian_is_added_to_a_project()
    {
        $defaultActions = ProjectHasUser::getDefaultActions();
        $this->add_user_and_custodian_to_project();

        $this->assertDatabaseCount('validation_logs', count($defaultActions));
        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ]);
        }

        $newCustodian = Custodian::factory()->create();
        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $newCustodian->id ,
        ]);

        $this->assertDatabaseCount('validation_logs', 2 * count($defaultActions));
        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $newCustodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ]);
        }

    }

    public function test_it_removes_validation_logs_when_a_custodian_is_removed_from_a_project()
    {
        $defaultActions = ProjectHasUser::getDefaultActions();
        $this->add_user_and_custodian_to_project();

        $newCustodian = Custodian::factory()->create();
        $phc = ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $newCustodian->id ,
        ]);


        $this->assertDatabaseCount('validation_logs', 2 * count($defaultActions));
        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $newCustodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ]);
        }

        $phc->delete();

        $this->assertDatabaseCount('validation_logs', count($defaultActions));
        foreach ($defaultActions as $action) {
            $this->assertDatabaseMissing('validation_logs', [
                'entity_id' => $newCustodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ]);
        }
    }

    public function test_it_doesnt_find_custodian_project_user_logs_via_api()
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );

        $response->assertStatus(404);

    }

    public function test_it_returns_custodian_project_user_logs_via_api()
    {
        $defaultActions = ProjectHasUser::getDefaultActions();
        $this->add_user_and_custodian_to_project();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ];
        }, $defaultActions);

        $response->assertJson(['data' => $expectedResponse]);

    }

    public function test_it_can_mark_validation_as_complete_via_api()
    {
        Carbon::setTestNow(Carbon::now());

        $this->add_user_and_custodian_to_project();
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertNull($validationLog['completed_at']);

        $validationLogId = $validationLog['id'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "validation_logs/{$validationLogId}?complete",
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $validationLog['completed_at']
        );

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "validation_logs/{$validationLogId}?incomplete",
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertNull($validationLog['completed_at']);

    }

    public function test_it_can_mark_validation_as_pass_or_fail_via_api()
    {
        Carbon::setTestNow(Carbon::now());

        $this->add_user_and_custodian_to_project();
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertNull($validationLog['completed_at']);
        $this->assertEquals(0, $validationLog['manually_confirmed']);

        $validationLogId = $validationLog['id'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "validation_logs/{$validationLogId}?pass",
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $validationLog['completed_at']
        );
        $this->assertEquals(1, $validationLog['manually_confirmed']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "validation_logs/{$validationLogId}?fail",
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $validationLog['completed_at']
        );
        $this->assertEquals(0, $validationLog['manually_confirmed']);

    }

    public function test_it_can_mark_validation_as_enabled_disabled_via_api()
    {
        Carbon::setTestNow(Carbon::now());

        $this->add_user_and_custodian_to_project();
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertNull($validationLog['completed_at']);
        $this->assertEquals(0, $validationLog['manually_confirmed']);

        $validationLogId = $validationLog['id'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "validation_logs/{$validationLogId}?disable",
        );
        $response->assertStatus(200);


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertNull($validationLog);


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs?show_disabled=1",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertEquals(0, $validationLog['enabled']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "validation_logs/{$validationLogId}?enable",
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('name', ProjectHasUser::VALIDATE_COMPLETE_CONFIGURATION);

        $this->assertEquals(1, $validationLog['enabled']);

    }

    public function test_it_can_handle_custodian_project_user_validation_checks_via_api()
    {
        Carbon::setTestNow(Carbon::now());
        $defaultActions = ProjectHasUser::getDefaultActions();
        $this->add_user_and_custodian_to_project();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
            ];
        }, $defaultActions);

        $response->assertJson(['data' => $expectedResponse]);
        $validationLogs = collect($response['data']);

        foreach ($defaultActions as $action) {
            $validationLog = $validationLogs->firstWhere('name', $action);
            $validationLogId = $validationLog['id'];

            $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "validation_logs/{$validationLogId}?pass",
            );
            $response->assertStatus(200);

            $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "validation_logs/{$validationLogId}",
            );
            $response->assertStatus(200);
            $this->assertEquals(1, $response['data']['manually_confirmed']);
            $this->assertEquals(
                Carbon::now()->format('Y-m-d H:i:s'),
                $response['data']['completed_at']
            );
        }
    }

    public function test_it_can_toggle_enabled_validation_logs_via_api()
    {
        Carbon::setTestNow(Carbon::now());
        $defaultActions = ProjectHasUser::getDefaultActions();
        $this->add_user_and_custodian_to_project();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
                'enabled' => 1
            ];
        }, $defaultActions);

        $response->assertJson(['data' => $expectedResponse]);

        $actionToDisable = $defaultActions[0];
        $payload = [
            'enabled' => 0,
            'name' => $actionToDisable
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "custodians/{$this->custodian->id}/validation_logs",
            $payload
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
        );

        $response->assertStatus(200);

        $actionsWithRemoved = array_values(array_filter($defaultActions, fn ($v) => $v !== $actionToDisable));

        $expectedResponse = array_map(function ($action) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'name' => $action,
                'completed_at' => null,
                'enabled' => 1
            ];
        }, $actionsWithRemoved);

        $response->assertJson(['data' => $expectedResponse]);

    }

    private function add_user_and_custodian_to_project()
    {
        ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' => $this->user->registry->digi_ident,
        ]);
        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $this->custodian->id ,
        ]);

    }



}
