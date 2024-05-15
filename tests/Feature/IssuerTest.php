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
                'contact_email' => 'test@test.com',
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
                'contact_email' => 'test@test.com',
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
                'contact_email' => 'test@test.com',
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

    public function test_the_application_can_get_issuers_by_unique_identifier(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABC123',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuerCreated = Issuer::where('id', $content)->first();
        
        $response = $this->json(
            'GET',
            self::TEST_URL . '/identifier/' . $issuerCreated->unique_identifier,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Test Issuer ABC123');
    }

    public function test_the_application_can_receive_issuer_pushes_with_valid_key(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuer = Issuer::where('id', $content)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/push',
            [
                'researchers' => [],
                'projects' => [],
                'organisations' => [],
            ],
            [
                'x-issuer-key' => $issuer->unique_identifier,
            ]
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['researchers'], []);
        $this->assertEquals($content['projects'], []);
        $this->assertEquals($content['organisations'], []);
    }

    public function test_the_application_can_refuse_pushes_with_missing_key(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuer = Issuer::where('id', $content)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/push',
            [
                'researchers' => [],
                'projects' => [],
                'organisations' => [],
            ],
            [
            ]
        );

        $response->assertStatus(401);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'you must provide your Issuer key');
    }

    public function test_the_application_can_refuse_pushes_when_key_is_invalid(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuer = Issuer::where('id', $content)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL . '/push',
            [
                'researchers' => [],
                'projects' => [],
                'organisations' => [],
            ],
            [
                'x-issuer-key' => $issuer->unique_identifier . 'broken_key',
            ]
        );

        $response->assertStatus(401);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['message'], 'no known issuer matches the credentials provided');
    }
}