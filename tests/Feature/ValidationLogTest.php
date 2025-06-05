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
use App\Models\Organisation;
use App\Models\ValidationCheck;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Carbon\Carbon;
use DB;

class ValidationLogTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/';

    protected $registry;
    protected $custodian;
    protected $project;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->registry = Registry::factory()->create();
        $this->user->update(['registry_id' => $this->registry->id]);
        $this->custodian = Custodian::factory()->create();
        $this->custodian->validationChecks()->syncWithoutDetaching(ValidationCheck::pluck('id')->all());

        $this->project = Project::factory()->create();

        ValidationLog::truncate();

        $this->enableObservers();
    }

    public function test_it_creates_validation_logs_when_a_user_is_added_to_a_project()
    {
        $defaultChecks = ProjectHasUser::defaultValidationChecks();

        $this->assertNotEmpty($defaultChecks, 'Expected defaultValidationChecks to return at least one action.');
        $this->assertDatabaseEmpty('validation_logs');

        ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' => $this->user->registry->digi_ident,
        ]);

        $this->assertDatabaseEmpty('validation_logs');


        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $this->custodian->id,
        ]);


        foreach ($defaultChecks as $check) {
            $id = ValidationCheck::where(['name' => $check['name']])->first()->id;

            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => $id,
                'completed_at' => null,
            ]);
        }
    }

    public function test_it_adds_more_validation_logs_when_custodian_is_added_to_a_project()
    {
        $defaultChecks = ProjectHasUser::defaultValidationChecks();
        $this->add_user_and_custodian_to_project();

        $this->assertDatabaseCount('validation_logs', count($defaultChecks));

        foreach ($defaultChecks as $check) {
            $validationCheckId = ValidationCheck::where('name', $check['name'])->value('id');

            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => $validationCheckId,
                'completed_at' => null,
            ]);
        }

        $newCustodian = Custodian::factory()->create();
        $newCustodian->validationChecks()
            ->syncWithoutDetaching(ValidationCheck::pluck('id')->all());

        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $newCustodian->id,
        ]);

        $this->assertEquals(
            2 * count($defaultChecks),
            DB::table('validation_logs')
                ->where('secondary_entity_type', Project::class)
                ->count()
        );

        foreach ($defaultChecks as $check) {
            $validationCheckId = ValidationCheck::where('name', $check['name'])->value('id');

            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $newCustodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => $validationCheckId,
                'completed_at' => null,
            ]);
        }
    }

    public function test_it_removes_validation_logs_when_a_custodian_is_removed_from_a_project()
    {
        $defaultChecks = ProjectHasUser::defaultValidationChecks();
        $this->add_user_and_custodian_to_project();

        $newCustodian = Custodian::factory()->create();
        $newCustodian->validationChecks()
            ->syncWithoutDetaching(ValidationCheck::pluck('id')->all());

        $phc = ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $newCustodian->id,
        ]);

        $this->assertEquals(
            2 * count($defaultChecks),
            DB::table('validation_logs')
                ->where('secondary_entity_type', Project::class)
                ->count()
        );

        foreach ($defaultChecks as $check) {
            $validationCheckId = ValidationCheck::where('name', $check['name'])->value('id');

            $this->assertDatabaseHas('validation_logs', [
                'entity_id' => $newCustodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => $validationCheckId,
                'completed_at' => null,
            ]);
        }

        $phc->delete();

        $this->assertEquals(
            count($defaultChecks),
            DB::table('validation_logs')
                ->where('secondary_entity_type', Project::class)
                ->count()
        );

        foreach ($defaultChecks as $check) {
            $validationCheckId = ValidationCheck::where('name', $check['name'])->value('id');

            $this->assertDatabaseMissing('validation_logs', [
                'entity_id' => $newCustodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => $validationCheckId,
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
        $defaultChecks = ProjectHasUser::defaultValidationChecks();
        $this->add_user_and_custodian_to_project();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($check) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => ValidationCheck::where('name', $check['name'])->value('id'),
                'completed_at' => null,
            ];
        }, $defaultChecks);

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

        $checkName = 'no_misconduct';
        $validationCheckId = ValidationCheck::where('name', $checkName)->value('id');

        $validationLog = collect($responseData)
            ->firstWhere('validation_check_id', $validationCheckId);

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
            ->firstWhere('validation_check_id', $validationCheckId);

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
            ->firstWhere('validation_check_id', $validationCheckId);

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

        $checkName = 'no_misconduct';
        $validationCheckId = ValidationCheck::where('name', $checkName)->value('id');

        $validationLog = collect($responseData)
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertNull($validationLog['completed_at']);
        $this->assertEquals(0, $validationLog['manually_confirmed']);

        $validationLogId = $validationLog['id'];

        // Mark as pass
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "validation_logs/{$validationLogId}?pass",
            );
        $response->assertStatus(200);

        // Fetch again
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);
        $validationLog = collect($response['data'])
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $validationLog['completed_at']);
        $this->assertEquals(1, $validationLog['manually_confirmed']);

        // Mark as fail
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "validation_logs/{$validationLogId}?fail",
            );
        $response->assertStatus(200);

        // Fetch again
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);
        $validationLog = collect($response['data'])
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $validationLog['completed_at']);
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

        $checkName = 'no_misconduct';
        $validationCheckId = ValidationCheck::where('name', $checkName)->value('id');

        $validationLog = collect($responseData)
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertNull($validationLog['completed_at']);
        $this->assertEquals(0, $validationLog['manually_confirmed']);

        $validationLogId = $validationLog['id'];

        // Disable the validation log
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "validation_logs/{$validationLogId}?disable",
            );
        $response->assertStatus(200);

        // It should now be hidden unless we show disabled
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);

        $this->assertNull(
            collect($response['data'])->firstWhere('validation_check_id', $validationCheckId)
        );

        // Show disabled
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs?show_disabled=1",
            );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertEquals(0, $validationLog['enabled']);

        // Re-enable it
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "validation_logs/{$validationLogId}?enable",
            );
        $response->assertStatus(200);

        // Confirm it's visible again
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $validationLog = collect($responseData)
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertEquals(1, $validationLog['enabled']);
    }

    public function test_it_can_handle_custodian_project_user_validation_checks_via_api()
    {
        Carbon::setTestNow(Carbon::now());

        $defaultChecks = ProjectHasUser::defaultValidationChecks();
        $this->add_user_and_custodian_to_project();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($check) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => ValidationCheck::where('name', $check['name'])->value('id'),
                'completed_at' => null,
            ];
        }, $defaultChecks);

        $response->assertJson(['data' => $expectedResponse]);
        $validationLogs = collect($response['data']);

        foreach ($defaultChecks as $check) {
            $validationCheckId = ValidationCheck::where('name', $check['name'])->value('id');
            $validationLog = $validationLogs->firstWhere('validation_check_id', $validationCheckId);
            $validationLogId = $validationLog['id'];

            // Mark as pass
            $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
                ->json(
                    'PUT',
                    self::TEST_URL . "validation_logs/{$validationLogId}?pass",
                );
            $response->assertStatus(200);

            // Fetch individually and assert
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

        $defaultChecks = ProjectHasUser::defaultValidationChecks();
        $this->add_user_and_custodian_to_project();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);

        $expectedResponse = array_map(function ($check) {
            return [
                'entity_id' => $this->custodian->id,
                'entity_type' => Custodian::class,
                'secondary_entity_id' => $this->project->id,
                'secondary_entity_type' => Project::class,
                'tertiary_entity_id' => $this->registry->id,
                'tertiary_entity_type' => Registry::class,
                'validation_check_id' => ValidationCheck::where('name', $check['name'])->value('id'),
                'completed_at' => null,
                'enabled' => 1,
            ];
        }, $defaultChecks);

        $response->assertJson(['data' => $expectedResponse]);

        // Pick one check to disable
        $checkNameToDisable = 'no_misconduct';
        $validationCheckId = ValidationCheck::where('name', $checkNameToDisable)->value('id');

        $payload = [
            'enabled' => 0,
            'validation_check_id' => $validationCheckId,
        ];

        // Disable
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "custodians/{$this->custodian->id}/validation_logs",
                $payload
            );
        $response->assertStatus(200);

        // Now the disabled log should not appear
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);

        $this->assertNull(
            collect($response['data'])->firstWhere('validation_check_id', $validationCheckId)
        );

        // Confirm it appears when show_disabled is set
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs?show_disabled=1",
            );
        $response->assertStatus(200);

        $validationLog = collect($response['data'])
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertEquals(0, $validationLog['enabled']);

        // Enable again
        $payload['enabled'] = 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "custodians/{$this->custodian->id}/validation_logs",
                $payload
            );
        $response->assertStatus(200);

        // Confirm it's back in normal list
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$this->custodian->id}/projects/{$this->project->id}/registries/{$this->registry->id}/validation_logs",
            );
        $response->assertStatus(200);

        $validationLog = collect($response['data'])
            ->firstWhere('validation_check_id', $validationCheckId);

        $this->assertEquals(1, $validationLog['enabled']);
    }

    public function test_it_adds_custodian_organisation_validation_logs()
    {
        Organisation::truncate();
        Custodian::truncate();
        ValidationLog::truncate();

        $defaultChecks = Organisation::defaultValidationChecks();

        // Create initial custodian + organisation
        $newCustodian = Custodian::factory()->create();
        $newCustodian->validationChecks()
            ->syncWithoutDetaching(ValidationCheck::pluck('id')->all());

        $newOrganisation = Organisation::factory()->create();


        $expectedLogCount = count($defaultChecks) * Custodian::count() * Organisation::count();
        $temp = ValidationLog::where('entity_type', Custodian::class)
            ->where('secondary_entity_type', Organisation::class)
            ->count();

        $this->assertEquals(
            $expectedLogCount,
            ValidationLog::where('entity_type', Custodian::class)
                ->where('secondary_entity_type', Organisation::class)
                ->count()
        );

        // Add a second custodian
        $newCustodian = Custodian::factory()->create();
        $newCustodian->validationChecks()
            ->syncWithoutDetaching(ValidationCheck::pluck('id')->all());
        $newCustodian->update();

        $expectedLogCount = count($defaultChecks) * Custodian::count() * Organisation::count();

        $actualCount = ValidationLog::where('entity_type', Custodian::class)
            ->where('secondary_entity_type', Organisation::class)
            ->count();

        $this->assertEquals(
            $expectedLogCount,
            $actualCount
        );

        // Add a second organisation
        $newOrganisation = Organisation::factory()->create();

        $expectedLogCount = count($defaultChecks) * Custodian::count() * Organisation::count();

        $this->assertEquals(
            $expectedLogCount,
            ValidationLog::where('entity_type', Custodian::class)
                ->where('secondary_entity_type', Organisation::class)
                ->count()
        );
    }


    private function add_user_and_custodian_to_project()
    {
        ProjectHasUser::create([
            'project_id' => $this->project->id,
            'user_digital_ident' => $this->user->registry->digi_ident,
        ]);
        ProjectHasCustodian::create([
            'project_id' => $this->project->id,
            'custodian_id' => $this->custodian->id,
        ]);
    }
}
