<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\History;

use Database\Seeders\UserSeeder;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class HistoryTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/histories';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_histories(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_histories(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'employment_id' => 1, 
                'endorsement_id' => 1,
                'infringement_id' => 1,
                'project_id' => 1,
                'access_key_id' => 1,
                'issuer_identifier' => '20895720385sodhfsjkdhfksjfh20935209538',
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'GET',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_histories(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'employment_id' => 1, 
                'endorsement_id' => 1,
                'infringement_id' => 1,
                'project_id' => 1,
                'access_key_id' => 1,
                'issuer_identifier' => '20895720385sodhfsjkdhfksjfh20935209538',
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }
}
