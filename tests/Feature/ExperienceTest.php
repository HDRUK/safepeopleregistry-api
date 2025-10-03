<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Experience;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class ExperienceTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/experiences';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_experiences(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL);

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_experiences(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('POST', self::TEST_URL, [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'organisation_id' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL . '/' . $content['data']);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_experiences(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('POST', self::TEST_URL, [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'organisation_id' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_experiences(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('POST', self::TEST_URL, [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'organisation_id' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $newDate = Carbon::now()->subYears(2);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('PUT', self::TEST_URL . '/' . $content['data'], [
                'project_id' => 2,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'organisation_id' => 1,
            ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $this->assertEquals($content['data']['project_id'], 2);
    }

    public function test_the_application_can_delete_experiences(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('POST', self::TEST_URL, [
                'project_id' => 1,
                'from' => Carbon::now(),
                'to' => Carbon::now()->addYears(1),
                'organisation_id' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('DELETE', self::TEST_URL . '/' . $content['data']);

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_get_experiences(): void
    {
        $latestExperience = Experience::query()->orderBy('id', 'desc')->first();
        $experienceIdTest = $latestExperience ? $latestExperience->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "/{$experienceIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_update_experiences(): void
    {
        $latestExperience = Experience::query()->orderBy('id', 'desc')->first();
        $experienceIdTest = $latestExperience ? $latestExperience->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "/{$experienceIdTest}",
                [
                    'project_id' => 1,
                    'from' => Carbon::now(),
                    'to' => Carbon::now()->addYears(1),
                    'organisation_id' => 1,
                ]
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_delete_experiences(): void
    {
        $latestExperience = Experience::query()->orderBy('id', 'desc')->first();
        $experienceIdTest = $latestExperience ? $latestExperience->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'DELETE',
                self::TEST_URL . "/{$experienceIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
