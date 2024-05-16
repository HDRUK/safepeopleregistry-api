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
    const TEST_URL_REFRESH = 'refresh';

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
        ]);
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
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
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
        $accessToken = $content['access_token'];

        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_ME,
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $accessToken,
            ]
        );

        $response->assertStatus(200);

        $keys = [
            'name',
            'email',
            'email_verified_at',
            'registry_id',
        ];

        foreach ($keys as $k) {
            $this->assertArrayHasKey($k, $response);
        }
    }

    public function test_the_application_refreshes_a_token(): void
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
        $accessToken = $content['access_token'];

        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_REFRESH,
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $accessToken,
            ]
        );

        $response->assertStatus(200);

        $content = $response->decodeResponseJson();
        $refreshedToken = $content['access_token'];

        $this->assertNotEquals($accessToken, $refreshedToken);
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
        $accessToken = $content['access_token'];

        $response = $this->json(
            'POST',
            self::TEST_URL . self::TEST_URL_LOGOUT,
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $accessToken,
            ]
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'success');
    }
}