<?php

namespace Tests\Feature;

use Http;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Queue;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\PendingInvite;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianUserTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodian_users';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();

        Http::fake([
            env('KEYCLOAK_BASE_URL') . '/*' => Http::response([
                'access_token' => 'fake-token-123',
                'success' => true,
                'error' => null,
            ], 200, [
                'Content-Type' => 'application/json',
            ])
        ]);
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

        CustodianUser::truncate();

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

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/invite/1'
            );

        Queue::assertPushed(SendEmailJob::class);

        $invites = PendingInvite::all();

        $this->assertTrue(count($invites) === 1);
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
