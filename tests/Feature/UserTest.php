<?php

namespace Tests\Feature;

use Http;
use RegistryManagementController as RMC;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\State;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Carbon\Carbon;

class UserTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/users';

    private $rulesStr = '{"performance":"1.079917ms","result":{"rule_alert":"ok"},"trace":{"f0f95b67-1c93-45d2-ba72-b113827c8613":{"id":"f0f95b67-1c93-45d2-ba72-b113827c8613","name":"request","input":null,"output":null},"974d8379-3471-4e74-8929-f3bd3b1f3faf":{"id":"974d8379-3471-4e74-8929-f3bd3b1f3faf","name":"Country Sanction","input":{"consent_scrape":false,"created_at":"2024-12-23T13:45:07.000000Z","declaration_signed":0,"departments":[],"email":"dan.ackroyd@ghostbusters.com","first_name":"Dan","id":10,"is_delegate":0,"is_org_admin":0,"last_name":"Ackroyd","orcid_scanning":false,"organisation_id":0,"pending_invites":[],"permissions":[],"public_opt_in":0,"registry":{"created_at":"2024-12-23T13:45:07.000000Z","deleted_at":null,"education":[{"created_at":"2024-12-23T13:45:07.000000Z","from":"2014-12-23","id":1,"institute_address":"Keppel Street, London, WC1E 7HT","institute_identifier":"00a0jsq62","institute_name":"London School of Hygiene &amp; Tropical Medicine","registry_id":1,"source":"user","title":"Infectious Disease &#039;Omics","to":"2018-12-23","updated_at":"2024-12-23T13:45:07.000000Z"},{"created_at":"2024-12-23T13:45:07.000000Z","from":"2019-12-23","id":2,"institute_address":"Stocker Road, Exeter, Devon EX4 4SZ","institute_identifier":"03yghzc09","institute_name":"University of Exeter","registry_id":1,"source":"user","title":"MSc Health Data Science","to":"2020-12-23","updated_at":"2024-12-23T13:45:07.000000Z"}],"files":[],"id":1,"identity":{"address_1":"123 Road name","address_2":"","country":"USA","county":"Illinois","created_at":"2024-12-23T13:45:09.000000Z","deleted_at":null,"dob":"1962-01-01","drivers_license_path":"\/path\/to\/non\/existent\/license\/","id":1,"idvt_completed_at":"2024-12-23 13:45:07","idvt_errors":null,"idvt_result":1,"idvt_result_perc":100,"passport_path":"\/path\/to\/non\/existent\/passport\/","postcode":"62629","registry_id":1,"selfie_path":"\/path\/to\/non\/existent\/selfie\/","town":"Springfield","updated_at":"2024-12-23T13:45:09.000000Z"},"training":[{"awarded_at":"2022-12-23 00:00:00","created_at":"2024-12-23T13:45:07.000000Z","expires_at":"2027-12-23 00:00:00","expires_in_years":5,"id":1,"provider":"UK Data Service","registry_id":1,"training_name":"Safe Researcher Training","updated_at":"2024-12-23T13:45:07.000000Z"},{"awarded_at":"2022-12-23 00:00:00","created_at":"2024-12-23T13:45:07.000000Z","expires_at":"2027-12-23 00:00:00","expires_in_years":5,"id":2,"provider":"Medical Research Council (MRC)","registry_id":1,"training_name":"Research, GDPR, and Confidentiality","updated_at":"2024-12-23T13:45:07.000000Z"}],"updated_at":"2024-12-23T13:45:07.000000Z","verified":false},"registry_id":1,"unclaimed":0,"updated_at":"2024-12-23T13:45:07.000000Z","user_group":"USERS"},"output":{"rule_alert":"ok"},"performance":"252.042\u00b5s","traceData":{"index":0,"reference_map":[],"rule":{"_description":"Defines country cleared on sanctions list","_id":"a19572dc-7dea-4be4-94cc-95e0c05d14a1","registry.identity.country[7e128890-760a-473d-adaa-266c2c4c3b1f]":"== \"USA\""}}},"60af625b-0162-4a45-8ff0-224e22da162d":{"id":"60af625b-0162-4a45-8ff0-224e22da162d","name":"Country Sanction Switch","input":{"consent_scrape":false,"created_at":"2024-12-23T13:45:07.000000Z","declaration_signed":0,"departments":[],"email":"dan.ackroyd@ghostbusters.com","first_name":"Dan","id":10,"is_delegate":0,"is_org_admin":0,"last_name":"Ackroyd","orcid_scanning":false,"organisation_id":0,"pending_invites":[],"permissions":[],"public_opt_in":0,"registry":{"created_at":"2024-12-23T13:45:07.000000Z","deleted_at":null,"education":[{"created_at":"2024-12-23T13:45:07.000000Z","from":"2014-12-23","id":1,"institute_address":"Keppel Street, London, WC1E 7HT","institute_identifier":"00a0jsq62","institute_name":"London School of Hygiene &amp; Tropical Medicine","registry_id":1,"source":"user","title":"Infectious Disease &#039;Omics","to":"2018-12-23","updated_at":"2024-12-23T13:45:07.000000Z"},{"created_at":"2024-12-23T13:45:07.000000Z","from":"2019-12-23","id":2,"institute_address":"Stocker Road, Exeter, Devon EX4 4SZ","institute_identifier":"03yghzc09","institute_name":"University of Exeter","registry_id":1,"source":"user","title":"MSc Health Data Science","to":"2020-12-23","updated_at":"2024-12-23T13:45:07.000000Z"}],"files":[],"id":1,"identity":{"address_1":"123 Road name","address_2":"","country":"USA","county":"Illinois","created_at":"2024-12-23T13:45:09.000000Z","deleted_at":null,"dob":"1962-01-01","drivers_license_path":"\/path\/to\/non\/existent\/license\/","id":1,"idvt_completed_at":"2024-12-23 13:45:07","idvt_errors":null,"idvt_result":1,"idvt_result_perc":100,"passport_path":"\/path\/to\/non\/existent\/passport\/","postcode":"62629","registry_id":1,"selfie_path":"\/path\/to\/non\/existent\/selfie\/","town":"Springfield","updated_at":"2024-12-23T13:45:09.000000Z"},"training":[{"awarded_at":"2022-12-23 00:00:00","created_at":"2024-12-23T13:45:07.000000Z","expires_at":"2027-12-23 00:00:00","expires_in_years":5,"id":1,"provider":"UK Data Service","registry_id":1,"training_name":"Safe Researcher Training","updated_at":"2024-12-23T13:45:07.000000Z"},{"awarded_at":"2022-12-23 00:00:00","created_at":"2024-12-23T13:45:07.000000Z","expires_at":"2027-12-23 00:00:00","expires_in_years":5,"id":2,"provider":"Medical Research Council (MRC)","registry_id":1,"training_name":"Research, GDPR, and Confidentiality","updated_at":"2024-12-23T13:45:07.000000Z"}],"updated_at":"2024-12-23T13:45:07.000000Z","verified":false},"registry_id":1,"unclaimed":0,"updated_at":"2024-12-23T13:45:07.000000Z","user_group":"USERS"},"output":{"consent_scrape":false,"created_at":"2024-12-23T13:45:07.000000Z","declaration_signed":0,"departments":[],"email":"dan.ackroyd@ghostbusters.com","first_name":"Dan","id":10,"is_delegate":0,"is_org_admin":0,"last_name":"Ackroyd","orcid_scanning":false,"organisation_id":0,"pending_invites":[],"permissions":[],"public_opt_in":0,"registry":{"created_at":"2024-12-23T13:45:07.000000Z","deleted_at":null,"education":[{"created_at":"2024-12-23T13:45:07.000000Z","from":"2014-12-23","id":1,"institute_address":"Keppel Street, London, WC1E 7HT","institute_identifier":"00a0jsq62","institute_name":"London School of Hygiene &amp; Tropical Medicine","registry_id":1,"source":"user","title":"Infectious Disease &#039;Omics","to":"2018-12-23","updated_at":"2024-12-23T13:45:07.000000Z"},{"created_at":"2024-12-23T13:45:07.000000Z","from":"2019-12-23","id":2,"institute_address":"Stocker Road, Exeter, Devon EX4 4SZ","institute_identifier":"03yghzc09","institute_name":"University of Exeter","registry_id":1,"source":"user","title":"MSc Health Data Science","to":"2020-12-23","updated_at":"2024-12-23T13:45:07.000000Z"}],"files":[],"id":1,"identity":{"address_1":"123 Road name","address_2":"","country":"USA","county":"Illinois","created_at":"2024-12-23T13:45:09.000000Z","deleted_at":null,"dob":"1962-01-01","drivers_license_path":"\/path\/to\/non\/existent\/license\/","id":1,"idvt_completed_at":"2024-12-23 13:45:07","idvt_errors":null,"idvt_result":1,"idvt_result_perc":100,"passport_path":"\/path\/to\/non\/existent\/passport\/","postcode":"62629","registry_id":1,"selfie_path":"\/path\/to\/non\/existent\/selfie\/","town":"Springfield","updated_at":"2024-12-23T13:45:09.000000Z"},"training":[{"awarded_at":"2022-12-23 00:00:00","created_at":"2024-12-23T13:45:07.000000Z","expires_at":"2027-12-23 00:00:00","expires_in_years":5,"id":1,"provider":"UK Data Service","registry_id":1,"training_name":"Safe Researcher Training","updated_at":"2024-12-23T13:45:07.000000Z"},{"awarded_at":"2022-12-23 00:00:00","created_at":"2024-12-23T13:45:07.000000Z","expires_at":"2027-12-23 00:00:00","expires_in_years":5,"id":2,"provider":"Medical Research Council (MRC)","registry_id":1,"training_name":"Research, GDPR, and Confidentiality","updated_at":"2024-12-23T13:45:07.000000Z"}],"updated_at":"2024-12-23T13:45:07.000000Z","verified":false},"registry_id":1,"unclaimed":0,"updated_at":"2024-12-23T13:45:07.000000Z","user_group":"USERS"},"performance":"70.25\u00b5s","traceData":null},"a6fe621c-a071-4da5-85c8-4ab6b662681d":{"id":"a6fe621c-a071-4da5-85c8-4ab6b662681d","name":"response","input":null,"output":null}}}';
    protected $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_search_users_by_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?email[]=bill.murray@ghostbusters.com',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']['data']) === 1);
        $this->assertTrue($content['data']['data'][0]['first_name'] === 'Bill');
        $this->assertTrue($content['data']['data'][0]['last_name'] === 'Murray');
    }

    public function test_the_application_can_search_by_user_group(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?user_group[]=USERS',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        foreach ($content['data']['data'] as $data) {
            $this->assertTrue($data['user_group'] === 'USERS');
        }

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?user_group[]=ORGANISATIONS',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        foreach ($content['data']['data'] as $data) {
            $this->assertTrue($data['user_group'] === 'ORGANISATIONS');
        }
    }

    public function test_the_application_can_search_users_by_first_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?first_name[]=bill',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']['data']) === 1);
        $this->assertTrue($content['data']['data'][0]['first_name'] === 'Bill');
        $this->assertTrue($content['data']['data'][0]['last_name'] === 'Murray');
    }

    public function test_the_application_can_search_users_by_last_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?last_name[]=murray',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']['data']) === 1);
        $this->assertTrue($content['data']['data'][0]['first_name'] === 'Bill');
        $this->assertTrue($content['data']['data'][0]['last_name'] === 'Murray');
    }

    /*
    // LS - Doesn't work with base demo seeder - TODO
    public function test_the_application_can_call_me_with_valid_token(): void
    {
        $user = User::where('id', 1)->first();

        $payload = $this->getMockedKeycloakPayload();
        $payload['sub'] = $this->user->keycloak_id;

        $response = $this->actingAsKeycloakUser($this->user, $payload)
            ->json(
                'GET',
                '/api/auth/me'
            );

        $response->assertStatus(200);

        $content = $response->decodeResponseJson();
        $this->assertEquals($content['data']['email'], 'loki.sinclair@hdruk.ac.uk');
    }
    */

    public function test_the_application_returns_404_when_call_to_me_with_non_existent_user(): void
    {
        putenv('KEYCLOAK_LOAD_USER_FROM_DATABASE=false');

        $payload = $this->getMockedKeycloakPayload();
        $payload['sub'] = $this->user->keycloak_id;

        $this->user->delete();

        $response = $this->actingAsKeycloakUser($this->user, $payload)
            ->json(
                'GET',
                '/api/auth/me'
            );

        $response->assertStatus(404);

        putenv('KEYCLOAK_LOAD_USER_FROM_DATABASE=true');
    }

    public function test_the_application_returns_error_when_call_to_me_with_invalid_token(): void
    {
        putenv('KEYCLOAK_LOAD_USER_FROM_DATABASE=false');

        $payload = $this->getMockedKeycloakPayload();
        $payload['exp'] = 1632805681; // 3 years ago (currently..!)

        $response = $this->actingAsKeycloakUser($this->user, $payload)
            ->json(
                'GET',
                '/api/auth/me'
            );

        $response->assertStatus(500);

        putenv('KEYCLOAK_LOAD_USER_FROM_DATABASE=true');
    }

    public function test_the_application_responds_correctly_to_allowed_permissions(): void
    {
        $payload = $this->getMockedKeycloakPayload();
        $payload['realm_access'] = [
            'roles' => [
                'admin',
            ],
        ];

        $response = $this->actingAsKeycloakUser($this->user, $payload)
            ->json(
                'GET',
                self::TEST_URL . '/test'
            );

        $response->assertStatus(200);
    }

    public function test_the_application_responds_correctly_to_not_allowed_permissions(): void
    {
        $payload = $this->getMockedKeycloakPayload();
        $payload['realm_access'] = [
            'roles' => [
                'users',
            ],
        ];

        $response = $this->actingAsKeycloakUser($this->user, $payload)
            ->json(
                'GET',
                self::TEST_URL . '/test'
            );

        $response->assertStatus(401);
    }

    public function test_the_application_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'first_name',
                        'last_name',
                        'email',
                        'registry_id',
                        'user_group',
                        'consent_scrape',
                        'orc_id',
                        'unclaimed',
                        'feed_source',
                        'permissions',
                        'registry',
                        'pending_invites',
                        'organisation_id',
                        'departments',
                        'model_state',
                    ],
                ]
            ],
        ]);

        $content = $response->decodeResponseJson();
        $this->assertTrue(count($content['data']['data']) > 1);
        $this->assertTrue($content['data']['data'][0]['email'] == 'organisation.owner@healthdataorganisation.com');
    }

    public function test_the_application_can_show_users(): void
    {
        $fakeUrl = env('RULES_ENGINE_SERVICE', 'https://rules-engine.test') .
            env('RULES_ENGINE_PROJECT_ID', '298357293857') . '/evaluate/' .
            env('RULES_ENGINE_EVAL_MODEL', 'something.json');

        Http::fake([
            $fakeUrl => Http::response($this->rulesStr, 200),
        ]);

        $user = User::where('user_group', RMC::KC_GROUP_USERS)->first();
        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '/' . $user->id // One of the researchers
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);

        if (env('RULES_ENGINE_ACTIVE', true) && isset($content['rules'])) {
            $this->assertArrayHasKey('rules', $response);
            $this->assertTrue($content['rules']['result']['rule_alert'] === 'ok');
            $this->assertArrayHasKey('result', $content['rules']);
            $this->assertArrayHasKey('trace', $content['rules']);
        }
    }

    public function test_the_application_can_create_users(): void
    {
        $this->withTemporaryObservers(function () {
            $response = $this->actingAs($this->admin)
                ->json(
                    'POST',
                    self::TEST_URL,
                    [
                        'first_name' => fake()->firstname(),
                        'last_name' => fake()->lastname(),
                        'email' => fake()->email(),
                        'provider' => fake()->word(),
                        'provider_sub' => Str::random(10),
                        'public_opt_in' => fake()->randomElement([0, 1]),
                        'declaration_signed' => fake()->randomElement([0, 1]),
                    ]
                );
            $response->assertStatus(201);
            $this->assertArrayHasKey('data', $response);

            // Test that when a user is created, our observer sets the initial
            // entity status for workflows
            $user = User::where('id', $response['data'])->first();
            $this->assertTrue($user->isInState(State::STATE_REGISTERED));
        });
    }

    public function test_the_application_fails_when_unable_to_create_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => '',
                    'provider' => fake()->word(),
                    'provider_sub' => Str::random(10),
                    'public_opt_in' => fake()->randomElement([0, 1]),
                    'declaration_signed' => fake()->randomElement([0, 1]),
                ]
            );

        // Assert bad request, knowing that we've not sent an email address
        $response->assertStatus(400);

        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => 'myemail.com',
                    'provider' => fake()->word(),
                    'provider_sub' => Str::random(10),
                    'public_opt_in' => fake()->randomElement([0, 1]),
                    'declaration_signed' => fake()->randomElement([0, 1]),
                ]
            );

        // Assert bad request, knowing that we've not sent an invalid email address
        $response->assertStatus(400);
    }

    public function test_the_application_can_update_users(): void
    {
        $this->withTemporaryObservers(function () {

            Carbon::setTestNow(Carbon::now());
            $response = $this->actingAs($this->admin)
                ->json(
                    'POST',
                    self::TEST_URL,
                    [
                        'first_name' => fake()->firstname(),
                        'last_name' => fake()->lastname(),
                        'email' => fake()->email(),
                        'provider' => fake()->word(),
                        'provider_sub' => Str::random(10),
                        'consent_scrape' => true,
                        'public_opt_in' => false,
                        'declaration_signed' => false,
                        'organisation_id' => 1,
                        'orc_id' => fake()->numerify('####-####-####-####'),
                    ]
                );

            $response->assertStatus(201);
            $this->assertArrayHasKey('data', $response);

            $content = $response->decodeResponseJson()['data'];
            $this->assertGreaterThan(0, $content);

            $response = $this->actingAs($this->admin)
                ->json(
                    'GET',
                    self::TEST_URL . '/' . $content . '/action_log'
                );

            $response->assertStatus(200);
            $responseData = $response['data'];
            $actionLog = collect($responseData)
                ->firstWhere('action', User::ACTION_PROFILE_COMPLETED);

            $this->assertNull($actionLog['completed_at']);


            $response = $this->actingAs($this->admin)
                ->json(
                    'PUT',
                    self::TEST_URL . '/' . $content,
                    [
                        'first_name' => 'Updated',
                        'last_name' => 'Name',
                        'email' => fake()->email(),
                        'declaration_signed' => true,
                        'organisation_id' => 2,
                        'location' => 1
                    ]
                );

            $response->assertStatus(200);
            $content = $response->decodeResponseJson()['data'];

            $this->assertEquals($content['first_name'], 'Updated');
            $this->assertEquals($content['last_name'], 'Name');
            $this->assertEquals($content['consent_scrape'], true);
            $this->assertEquals($content['declaration_signed'], true);
            $this->assertEquals($content['organisation_id'], 2);

            $response = $this->actingAs($this->admin)
                ->json(
                    'GET',
                    self::TEST_URL . '/' . $content['id'] . '/action_log'
                );

            $response->assertStatus(200);
            $responseData = $response['data'];
            $actionLog = collect($responseData)
                ->firstWhere('action', User::ACTION_PROFILE_COMPLETED);

            $this->assertEquals(
                Carbon::now()->format('Y-m-d H:i:s'),
                $actionLog['completed_at']
            );
        });
    }


    public function test_the_application_can_complete_action_log_for_profile(): void
    {
        $this->withTemporaryObservers(function () {

            $response = $this->actingAs($this->admin)
                ->json(
                    'POST',
                    self::TEST_URL,
                    [
                        'first_name' => fake()->firstname(),
                        'last_name' => fake()->lastname(),
                        'email' => fake()->email(),
                        'provider' => fake()->word(),
                        'provider_sub' => Str::random(10),
                        'consent_scrape' => true,
                        'public_opt_in' => false,
                        'declaration_signed' => false,
                        'organisation_id' => 1,
                        'orc_id' => fake()->numerify('####-####-####-####'),
                    ]
                );

            $response->assertStatus(201);
            $this->assertArrayHasKey('data', $response);

            $id = $response->decodeResponseJson()['data'];

            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $id,
                'entity_type' => User::class,
                'action' => User::ACTION_PROFILE_COMPLETED,
                'completed_at' => null,
            ]);

            $response = $this->actingAs($this->admin)
                ->json(
                    'PUT',
                    self::TEST_URL . '/' . $id,
                    [
                        'first_name' => 'Updated',
                        'last_name' => 'Name',
                        'email' => fake()->email(),
                    ]
                );

            $response->assertStatus(200);
            $content = $response->decodeResponseJson()['data'];

            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $id,
                'entity_type' => User::class,
                'action' => User::ACTION_PROFILE_COMPLETED,
                'completed_at' => null,
            ]);

            // Changed to save this, to avoid race condition of ms/s moving on before assertion runs
            $testTime = Carbon::now();
            Carbon::setTestNow($testTime);

            $response = $this->actingAs($this->admin)
                ->json(
                    'PUT',
                    self::TEST_URL . '/' . $id,
                    [
                        'location' => fake()->country(),
                    ]
                );

            $response->assertStatus(200);
            $content = $response->decodeResponseJson()['data'];

            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $id,
                'entity_type' => User::class,
                'action' => User::ACTION_PROFILE_COMPLETED,
                'completed_at' => $testTime,
            ]);
        });
    }


    public function test_the_application_can_delete_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => fake()->email(),
                    'provider' => fake()->word(),
                    'provider_sub' => Str::random(10),
                    'public_opt_in' => fake()->randomElement([0, 1]),
                    'declaration_signed' => fake()->randomElement([0, 1]),
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $content);

        $response = $this->actingAs($this->admin)
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content
            );

        $response->assertStatus(200);
    }

    public function test_the_application_can_search_across_affiliations_by_name_and_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'POST',
                self::TEST_URL . '/search_affiliations',
                [
                    'first_name' => 'dan',
                    'last_name' => 'spencer',
                    'email' => 'dan.ackroyd@healthpathwaysukltd.com',
                ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertNotNull($content);
        $this->assertTrue(count($content) > 0);
        $this->assertEquals($content[0]['first_name'], 'Dan');
        $this->assertEquals($content[0]['last_name'], 'Ackroyd');
        $this->assertNotNull($content[0]['email']);
        $this->assertNotNull($content[0]['organisation_id']);
        $this->assertTrue($content[0]['organisation_id'] !== null && $content[0]['organisation_id'] > 0);
    }

    public function test_the_application_can_filter_users_based_on_state(): void
    {
        // First set a user to a pending state
        $user = User::where('user_group', 'USERS')->first();
        $user->setState(State::STATE_PENDING);

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . '?filter=pending'
            );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'first_name',
                        'last_name',
                        'email',
                        'registry_id',
                        'user_group',
                        'consent_scrape',
                        'orc_id',
                        'unclaimed',
                        'feed_source',
                        'permissions',
                        'registry',
                        'pending_invites',
                        'organisation_id',
                        'departments',
                        'model_state',
                    ],
                ]
            ],
        ]);

        $content = $response->decodeResponseJson();
        $this->assertTrue($content['data']['data'][0]['email'] === $user->email);
        $this->assertTrue($user->getState() === State::STATE_PENDING);
    }
}
