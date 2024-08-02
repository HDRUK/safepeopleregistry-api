<?php

namespace Tests\Feature;

use Database\Seeders\EmailTemplatesSeeder;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class UserTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/users';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            UserSeeder::class,
            IssuerSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
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
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    0 => [
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
                    ],
                ],
            ],
        ]);

        $content = $response->decodeResponseJson();
        $this->assertTrue($content['data']['data'][0]['registry']['files'][0]['path'] === '1234_doesntexist.doc');
    }

    public function test_the_application_can_show_users(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL.'/1',
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
                'public_opt_in' => fake()->randomElement([0, 1]),
                'declaration_signed' => fake()->randomElement([0, 1]),
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
                'consent_scrape' => true,
                'public_opt_in' => false,
                'declaration_signed' => false,
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
                'declaration_signed' => true,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['first_name'], 'Updated');
        $this->assertEquals($content['last_name'], 'Name');
        $this->assertEquals($content['consent_scrape'], true);
        $this->assertEquals($content['declaration_signed'], true);
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
                'public_opt_in' => fake()->randomElement([0, 1]),
                'declaration_signed' => fake()->randomElement([0, 1]),
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
}
