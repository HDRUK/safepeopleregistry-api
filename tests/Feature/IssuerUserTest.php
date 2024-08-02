<?php

namespace Tests\Feature;

use App\Models\IssuerUserHasPermission;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class IssuerUserTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/issuer_users';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            IssuerSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_issuer_users(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_show_issuer_users(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL.'/1',
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_issuer_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'issuer_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_issuer_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'issuer_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $content);

        $response = $this->json(
            'PUT',
            self::TEST_URL.'/'.$content,
            [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => fake()->email(),
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['first_name'], 'Updated');
        $this->assertEquals($content['last_name'], 'Name');
    }

    public function test_the_application_can_delete_issuer_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'issuer_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $content);

        $response = $this->json(
            'DELETE',
            self::TEST_URL.'/'.$content,
            $this->headers
        );

        $response->assertStatus(200);
    }

    public function test_the_application_can_assign_permissions_to_issuer_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'password' => Str::random(12),
                'provider' => fake()->word(),
                'keycloak_id' => '',
                'issuer_id' => 1,
                'permissions' => [
                    1, 3, 5,
                ],
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $perms = IssuerUserHasPermission::where('issuer_user_id', $content)->get()->pluck('issuer_user_id');
        $this->assertTrue(count($perms) > 0);
    }
}
