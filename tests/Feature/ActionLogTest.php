<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\ActionLog;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\Department;
use App\Models\UserHasDepartments;
use App\Models\Subsidiary;
use App\Models\OrganisationHasSubsidiary;
use App\Models\Affiliation;
use App\Models\CustodianHasProjectOrganisation;
use App\Models\CustodianModelConfig;
use App\Models\Registry;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\File;
use App\Models\ProjectHasOrganisation;
use App\Models\RegistryHasTraining;
use App\Models\State;
use App\Models\Training;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Carbon\Carbon;

class ActionLogTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/';
    protected $file = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableObservers();
        $this->withUsers();
        $registry = Registry::factory()->create();
        $this->user = User::factory()->create([
            'registry_id' => $registry->id,
        ]);
        $this->file = File::create([
            'name' => 'temp',
            'type' => 'CERTIFICATION',
            'path' => '/nowhere',
            'status' => 'PROCESSED',
        ]);
    }

    public function test_it_creates_action_logs_when_a_user_is_created()
    {
        $defaultActions = User::getDefaultActions();

        $this->assertNotEmpty($defaultActions, 'Expected getDefaultActions to return at least one action.');

        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $this->user->id,
                'entity_type' => User::class,
                'action' => $action,
                'completed_at' => null,
            ]);
        }
    }

    public function test_it_creates_action_logs_when_an_organisation_is_created()
    {
        $org = Organisation::factory()->create();
        $defaultActions = Organisation::getDefaultActions();

        $this->assertNotEmpty($defaultActions, 'Expected getDefaultActions to return at least one action.');

        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $org->id,
                'entity_type' => Organisation::class,
                'action' => $action,
            ]);
        }
    }

    public function test_it_creates_action_logs_when_a_custodian_is_created()
    {
        $org = Custodian::factory()->create();
        $defaultActions = Custodian::getDefaultActions();

        $this->assertNotEmpty($defaultActions, 'Expected getDefaultActions to return at least one action.');

        foreach ($defaultActions as $action) {
            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $org->id,
                'entity_type' => Custodian::class,
                'action' => $action,
                'completed_at' => null,
            ]);
        }
    }


    public function test_it_returns_user_action_logs_via_api()
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) {
            return [
                'entity_id' => $this->user->id,
                'action' => $action,
                'completed_at' => null,
            ];
        }, User::getDefaultActions());

        $response->assertJson(['data' => $expectedResponse]);
    }


    public function test_it_returns_organisation_action_logs_via_api()
    {
        $org = Organisation::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) use ($org) {
            return [
                'entity_id' => $org->id,
                'action' => $action,
                'completed_at' => null,
            ];
        }, Organisation::getDefaultActions());

        $response->assertJson(['data' => $expectedResponse]);
    }


    public function test_it_returns_custodian_action_logs_via_api()
    {
        $custodian = Custodian::factory()->create();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) use ($custodian) {
            return [
                'entity_id' => $custodian->id,
                'action' => $action,
                'completed_at' => null,
            ];
        }, Custodian::getDefaultActions());

        $response->assertJson(['data' => $expectedResponse]);
    }

    public function test_it_fails_to_return_action_logs_via_api_for_unsupported()
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "test/1/action_log",
            );
        $response->assertStatus(400);
    }

    public function test_it_can_log_user_profile_complete()
    {
        Carbon::setTestNow(Carbon::now());

        $this->user->update([
            'first_name' => fake()->firstname(),
            'last_name' => fake()->lastname(),
            'email' => fake()->email(),
            'location' => fake()->country()
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROFILE_COMPLETED);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_user_affiliations_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $registry = Registry::factory()->create();
        $this->user->update([
            'registry_id' => $registry->id,
        ]);
        $this->user->refresh();


        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_AFFILIATIONS_COMPLETE);

        $this->assertNull($actionLog['completed_at']);


        //create an incomplete affiliation
        Affiliation::create([
            'organisation_id' => 1,
            'member_id' => '',
            'relationship' => null,
            'from' => null,
            'to' => null,
            'department' => null,
            'role' => null,
            'ror' => null,
            'registry_id' => $this->user->registry_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_AFFILIATIONS_COMPLETE);

        $this->assertNull(
            $actionLog['completed_at']
        );

        // add a complete affiliation
        Affiliation::factory()->create(['registry_id' => $this->user->registry_id]);


        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_AFFILIATIONS_COMPLETE);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    /*
    - waiting for RegistryHasTraining to be implemented in another task
    public function test_it_can_log_user_training_complete(){

    }
    */

    public function test_it_can_log_user_project_review_complete()
    {
        Carbon::setTestNow(Carbon::now());

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROJECTS_REVIEW);

        $actionLogId = $actionLog['id'];

        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "action_log/{$actionLogId}?complete",
            );
        $response->assertStatus(200);


        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );
        $response->assertStatus(200);
        $responseData = $response['data'];


        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROJECTS_REVIEW);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "action_log/{$actionLogId}?incomplete",
            );
        $response->assertStatus(200);


        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );
        $response->assertStatus(200);
        $responseData = $response['data'];


        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROJECTS_REVIEW);

        $this->assertNull(
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_user_training_complete()
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );
        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_TRAINING_COMPLETE);

        $this->assertNull(
            $actionLog['completed_at']
        );

        Carbon::setTestNow(Carbon::now());


        $training = Training::factory()->create();
        RegistryHasTraining::create([
            'registry_id' => $this->user->registry->id,
            'training_id' => $training->id
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "users/{$this->user->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_TRAINING_COMPLETE);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }


    public function test_it_can_log_organisation_name_addess_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $org = Organisation::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_NAME_ADDRESS_COMPLETED);

        $this->assertNull($actionLog['completed_at']);

        $org->update([
            'organisation_name' => fake()->company(),
            'address_1' => fake()->address(),
            'town' => fake()->city(),
            'country' => fake()->country(),
            'postcode' => fake()->postcode(),
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_NAME_ADDRESS_COMPLETED);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_organisation_digitial_identifiers_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $org = Organisation::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_DIGITAL_ID_COMPLETED);

        $this->assertNull($actionLog['completed_at']);

        $org->update([
            'companies_house_no' => fake()->numberBetween(0, 1000),
            'ror_id' => fake()->numberBetween(1000, 2000),
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_DIGITAL_ID_COMPLETED);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }


    public function test_it_can_log_organisation_sector_size_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $org = Organisation::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_SECTOR_SIZE_COMPLETED);


        $this->assertNull($actionLog['completed_at']);

        $org->update([
            'sector_id' => fake()->numberBetween(1, 100),
            'website' => fake()->url()
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_SECTOR_SIZE_COMPLETED);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_organisation_data_security_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $org = Organisation::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_DATA_SECURITY_COMPLETED);


        $this->assertNull($actionLog['completed_at']);

        $org->update([
            'dsptk_ods_code' => fake()->numberBetween(1000, 2000),
            'dsptk_expiry_date' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'dsptk_expiry_evidence' => $this->file->id,
            'iso_27001_certification_num' => fake()->numberBetween(1000, 2000),
            'iso_expiry_date' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'iso_expiry_evidence' => $this->file->id,
            'ce_certification_num' => fake()->numberBetween(1000, 2000),
            'ce_expiry_date' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'ce_expiry_evidence' => $this->file->id,
            'ce_plus_certification_num' => fake()->numberBetween(1000, 2000),
            'ce_plus_expiry_date' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'ce_plus_expiry_evidence' => $this->file->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_DATA_SECURITY_COMPLETED);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $org->update([
            'dsptk_expiry_date' => fake()->dateTimeBetween('-5 year', '-1 years')->format('Y-m-d'),
        ]);


        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_DATA_SECURITY_COMPLETED);


        $this->assertNull($actionLog['completed_at']);
    }


    public function test_it_can_log_organisation_add_subsidiary_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $org = Organisation::factory()->create();
        $sub = Subsidiary::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_ADD_SUBSIDIARY_COMPLETED);

        $this->assertNull($actionLog['completed_at']);

        OrganisationHasSubsidiary::updateOrCreate(
            [
                'organisation_id' => $org->id,
                'subsidiary_id' => $sub->id,
            ]
        );

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_ADD_SUBSIDIARY_COMPLETED);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $ohs = OrganisationHasSubsidiary::where(
            ['organisation_id' => $org->id]
        )->first();
        $ohs->delete();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_ADD_SUBSIDIARY_COMPLETED);

        $this->assertNull($actionLog['completed_at']);
    }


    public function test_it_can_log_organisation_add_sro_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $org = Organisation::factory()->create();
        $dep = Department::create(['name' => fake()->company()]);
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_ADD_SRO_COMPLETED);

        $this->assertNull($actionLog['completed_at']);

        $this->user->update([
            'organisation_id' => $org->id,
            'user_group' => 'ORGANISATIONS',
            'is_org_admin' => 1,
        ]);

        UserHasDepartments::create([
            'user_id' => $this->user->id,
            'department_id' => $dep->id,
        ]);


        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_ADD_SRO_COMPLETED);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_organisation_adding_users_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $registry = Registry::factory()->create();
        $this->user->update([
            'registry_id' => $registry->id,
        ]);
        $this->user->refresh();

        $org = Organisation::factory()->create();
        ActionLog::query()->update(['completed_at' => null]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_AFFILIATE_EMPLOYEES_COMPLETED);

        $this->assertNull($actionLog['completed_at']);


        $newUser = User::factory()->create();
        $newRegistry = Registry::factory()->create();
        $newUser->update([
            'registry_id' => $newRegistry->id,
        ]);

        Affiliation::factory()->create([
            'organisation_id' => $org->id,
            'registry_id' => $newRegistry->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "organisations/{$org->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Organisation::ACTION_AFFILIATE_EMPLOYEES_COMPLETED);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_custodian_profile_complete()
    {
        Carbon::setTestNow(Carbon::now());

        $custodian = Custodian::factory()->create();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_COMPLETE_CONFIGURATION);

        $this->assertNull($actionLog['completed_at']);

        $conf = CustodianModelConfig::where('custodian_id', $custodian->id)->first();
        $conf->update([
            'active' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_COMPLETE_CONFIGURATION);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    public function test_it_can_log_custodian_add_team_members_complete()
    {
        Carbon::setTestNow(Carbon::now());

        $custodian = Custodian::factory()->create();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_ADD_CONTACTS);

        $this->assertNull($actionLog['completed_at']);

        $cu = CustodianUser::factory()->create(['custodian_id' => $custodian->id]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_ADD_CONTACTS);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $cu->delete();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_ADD_CONTACTS);

        $this->assertNull($actionLog['completed_at']);
    }

    public function test_it_can_log_custodian_add_project_complete()
    {
        Carbon::setTestNow(Carbon::now());

        $custodian = Custodian::factory()->create();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_ADD_PROJECTS);

        $this->assertNull($actionLog['completed_at']);

        $project = Project::factory()->create();
        $phc = ProjectHasCustodian::create(
            [
                'project_id' => $project->id,
                'custodian_id' => $custodian->id
            ]
        );

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_ADD_PROJECTS);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $phc->delete();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_ADD_PROJECTS);

        $this->assertNull($actionLog['completed_at']);
    }


    public function test_it_can_log_custodian_approved_organisations_complete()
    {
        ActionLog::truncate();
        $this->enableObservers();

        Carbon::setTestNow(Carbon::now());
        $custodian = Custodian::factory()->create();
        $organisation = Organisation::factory()->create();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_APPROVE_AN_ORGANISATION);

        $this->assertNull($actionLog['completed_at']);


        $pho = ProjectHasOrganisation::create([
            'project_id' => Project::first()->id,
            'organisation_id' => $organisation->id,
        ]);

        $ohca = CustodianHasProjectOrganisation::create([
            'project_has_organisation_id' => $pho->id,
            'custodian_id' => $custodian->id
        ]);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_APPROVE_AN_ORGANISATION);
        $this->assertNull($actionLog['completed_at']);

        $ohca->setState(State::STATE_VALIDATED);
        $ohca->save();

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_APPROVE_AN_ORGANISATION);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $this->assertTrue($ohca->delete());

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "custodians/{$custodian->id}/action_log",
            );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_APPROVE_AN_ORGANISATION);

        $this->assertNull($actionLog['completed_at']);
    }
}
