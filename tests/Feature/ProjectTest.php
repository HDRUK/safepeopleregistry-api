<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\Project;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class ProjectTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/projects';

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

    public function test_the_application_can_list_projects(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_projects(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'registry_id' => 1,
                'name' => 'This is a Project',
                'public_benefit' => 'This will benefit the public',
                'runs_to' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
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

    public function test_the_application_can_create_projects(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'registry_id' => 1,
                'name' => 'This is a Project',
                'public_benefit' => 'This will benefit the public',
                'runs_to' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_projects(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'registry_id' => 1,
                'name' => 'This is a New Project',
                'public_benefit' => 'This will benefit the public',
                'runs_to' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
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
                'registry_id' => 1,
                'name' => 'This is an Old Project',
                'public_benefit' => 'This will benefit the public',
                'runs_to' => $newDate,
                'affiliate_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'This is an Old Project');
        $this->assertEquals(Carbon::parse($content['runs_to'])->toDateTimeString(), $newDate->toDateTimeString());
    }

    public function test_the_application_can_delete_projects(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'registry_id' => 1,
                'name' => 'This is a New Project',
                'public_benefit' => 'This will benefit the public',
                'runs_to' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
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