<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\Experience;

use Database\Seeders\UserSeeder;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class ExperienceTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/experiences';

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

    public function test_the_application_can_list_experiences(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_experiences(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'affiliation_id' => 1,
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

    public function test_the_application_can_create_experiences(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'affiliation_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_experiences(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'affiliation_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $newDate = Carbon::now()->subYears(2);

        $response = $this->json(
            'PUT',
            self::TEST_URL . '/' . $content,
            [
                'project_id' => 2,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'affiliation_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['project_id'], 2);
    }

    public function test_the_application_can_delete_experiences(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'affiliation_id' => 1,
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