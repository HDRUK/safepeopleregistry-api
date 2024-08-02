<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ProjectTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/projects';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
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
                'unique_id' => Str::random(30),
                'title' => 'This is a Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'GET',
            self::TEST_URL.'/'.$content,
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
                'unique_id' => Str::random(30),
                'title' => 'Test Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
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
                'unique_id' => Str::random(30),
                'title' => 'Test Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $newDate = Carbon::now()->addYears(2);

        $response = $this->json(
            'PUT',
            self::TEST_URL.'/'.$content,
            [
                'unique_id' => Str::random(30),
                'title' => 'This is an Old Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['title'], 'This is an Old Project');
        $this->assertEquals(Carbon::parse($content['end_date'])->toDateTimeString(), $newDate->toDateTimeString());
    }

    public function test_the_application_can_delete_projects(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'unique_id' => Str::random(30),
                'title' => 'Test Project',
                'lay_summary' => 'Sample lay summary',
                'public_benefit' => 'This will benefit the public',
                'request_category_type' => 'Category type',
                'technical_summary' => 'Sample technical summary',
                'other_approval_committees' => 'Bodies on a board panel',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYears(2),
                'affiliate_id' => 1,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'DELETE',
            self::TEST_URL.'/'.$content,
            $this->headers
        );

        $response->assertStatus(200);
    }
}
