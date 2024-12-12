<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Registry;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\ProjectHasCustodianApproval;
use App\Models\ProjectHasUser;
use Database\Seeders\EmailTemplatesSeeder;
use Database\Seeders\CustodianSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\BaseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class UserTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/users';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            CustodianSeeder::class,
            EmailTemplatesSeeder::class,
            BaseDemoSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_search_users_by_email(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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

    public function test_the_application_can_search_users_by_first_name(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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
                        'profile_steps_completed',
                        'profile_completed_at',
                        'orc_id',
                        'unclaimed',
                        'feed_source',
                        'permissions',
                        'registry',
                        'pending_invites',
                        'organisation_id',
                        'departments',
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
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/1'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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
    }

    public function test_the_application_fails_when_unable_to_create_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content,
                [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => fake()->email(),
                'declaration_signed' => true,
                'organisation_id' => 2,
            ]
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['first_name'], 'Updated');
        $this->assertEquals($content['last_name'], 'Name');
        $this->assertEquals($content['consent_scrape'], true);
        $this->assertEquals($content['declaration_signed'], true);
        $this->assertEquals($content['organisation_id'], 2);
    }

    public function test_the_application_can_delete_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
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

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content
            );

        $response->assertStatus(200);
    }

    public function test_the_application_can_show_user_approved_projects(): void
    {

        $registry = Registry::first();
        $userId = User::where("registry_id", $registry->id)->first()->id;

        $digi_ident = $registry->digi_ident;

        $project = Project::first();
        $projectId = $project->id;
        $orgId = Custodian::first()->id;

        ProjectHasUser::create(['project_id' => $projectId, 'user_digital_ident' => $digi_ident, 'project_role_id' => 1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $userId . '/projects/approved'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);

        ProjectHasCustodianApproval::create(['project_id' => $projectId,'custodian_id' => $orgId]);
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $userId . '/projects/approved'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertCount(1, $response['data']);
        $this->assertArrayHasKey('title', $response['data'][0]);
        $this->assertEquals($project->title, $response['data'][0]['title']);
    }
}
