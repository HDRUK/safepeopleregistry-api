<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Queue;
use App\Models\CustodianUserHasPermission;
use App\Models\CustodianUser;
use Database\Seeders\CustodianSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\EmailTemplatesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianUserTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodian_users';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            CustodianSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_list_custodian_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_show_custodian_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1'
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_show_custodian_users_by_email(): void
    {
        $custodianUser = CustodianUser::where('id', 1)->first();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/email/' . $custodianUser->email
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_custodian_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'first_name' => fake()->firstname(),
                    'last_name' => fake()->lastname(),
                    'email' => fake()->email(),
                    'password' => Str::random(12),
                    'provider' => fake()->word(),
                    'keycloak_id' => '',
                    'custodian_id' => 1,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_custodian_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'custodian_id' => 1,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();
        $this->assertGreaterThan(0, $content['data']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'],
                [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => fake()->email(),
            ]
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['first_name'], 'Updated');
        $this->assertEquals($content['last_name'], 'Name');
    }

    public function test_the_application_can_delete_custodian_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'custodian_id' => 1,
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

    public function test_the_application_can_invite_a_user_for_custodian(): void
    {
        Queue::fake();
        Queue::assertNothingPushed();

        $email = fake()->email();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        $user = CustodianUser::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'provider' => '',
            'keycloak_id' => '',
            'custodian_id' => 1,
        ]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/invite/1',
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'identifier' => 'custodian_user_invite',
                    'custodian_id' => 1,
                ],
            );

        $response->assertStatus(201);

        $this->assertDatabaseHas('custodian_users', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        Queue::assertPushed(SendEmailJob::class);
    }

    public function test_the_application_can_assign_permissions_to_custodian_users(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'custodian_id' => 1,
                'permissions' => [
                    1, 3, 5,
                ],
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $perms = CustodianUserHasPermission::where('custodian_user_id', $content)->get()->pluck('custodian_user_id');
        $this->assertTrue(count($perms) > 0);
    }
}
