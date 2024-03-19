<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\Endorsement;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class EndorsementTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/endorsements';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'beaer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_endorsements(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_endorsements(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'reported_by' => 1,
                'comment' => 'This is an endorsement',
                'raised_against' => 1,
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

    public function test_the_application_can_create_endorsements(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'reported_by' => 1,
                'comment' => 'This is an endorsement',
                'raised_against' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }
}