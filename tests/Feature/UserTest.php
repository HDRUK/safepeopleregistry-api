<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserHasIssuerApproval;
use App\Models\UserHasIssuerPermission;

use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\EmailTemplatesSeeder;

use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class UserTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/users';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            IssuerSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_users(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_show_users(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL . '/1',
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'provider' => fake()->word(),
                'provider_sub' => Str::random(10),
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'provider' => fake()->word(),
                'provider_sub' => Str::random(10),
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $content);

        $response = $this->json(
            'PUT',
            self::TEST_URL . '/' . $content,
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

    public function test_the_application_can_delete_users(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'first_name' => fake()->firstname(),
                'last_name' => fake()->lastname(),
                'email' => fake()->email(),
                'provider' => fake()->word(),
                'provider_sub' => Str::random(10),
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $content);

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
    }
}