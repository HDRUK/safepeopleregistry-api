<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\Issuer;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class IssuerTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/issuers';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_issuers(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_issuers(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL . '/1',
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_issuers(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_issuers(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer',
                'enabled' => true,
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
                'name' => 'Updated Issuer',
                'enabled' => false,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Updated Issuer');
        $this->assertEquals($content['enabled'], false);
    }

    public function test_the_application_can_delete_issuers(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
    }
}