<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\User;
use Database\Seeders\UserSeeder;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    const TEST_URL = '/api/v1/';
    const TEST_URL_LOGIN = 'login';
    const TEST_URL_LOGOUT = 'logout';
    const TEST_URL_ME = 'me';

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_the_application_returns_jwt_on_successful_login(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_LOGIN,
            [
                'email' => 'loki.sinclair@hdruk.ac.uk',
                'password' => 'tempP4ssword',
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response);
    }

    public function test_the_application_returns_user_object_when_calling_me(): void
    {
        $accessToken = '';

        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_LOGIN,
            [
                'email' => 'loki.sinclair@hdruk.ac.uk',
                'password' => 'tempP4ssword',
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(200);
        
        $content = $response->decodeResponseJson();
        $accessToken = $content['token'];

        $response = $this->json(
            'GET',
            self::TEST_URL . self::TEST_URL_ME,
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $accessToken,
            ]
        );

        $response->assertStatus(200);

        // dd($response->decodeResponseJson());

        $keys = [
            'name',
            'email',
            'registry_id',
        ];

        foreach ($keys as $k) {
            $this->assertArrayHasKey($k, $response['data']);
        }
    }

    public function test_the_application_can_logout_a_user(): void
    {
        $accessToken = '';

        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_LOGIN,
            [
                'email' => 'loki.sinclair@hdruk.ac.uk',
                'password' => 'tempP4ssword',
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(200);
        
        $content = $response->decodeResponseJson();
        $accessToken = $content['token'];

        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_LOGOUT,
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $accessToken,
            ]
        );

        $response->assertStatus(204);
    }
}