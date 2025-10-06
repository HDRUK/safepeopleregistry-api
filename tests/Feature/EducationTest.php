<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Registry;
use App\Models\Education;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class EducationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/educations';

    private $registry = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        $this->registry = Registry::where('id', $this->user->registry_id)->first();
    }

    public function test_the_application_can_list_educations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$this->registry->id}"
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_list_educations_by_registry(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$registryIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_list_educations_by_id_and_by_registry(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $latestEducation = Education::query()->orderBy('id', 'desc')->first();
        $educationIdTest = $latestEducation ? $latestEducation->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$educationIdTest}/{$registryIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_educations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/' . $this->registry->id,
                $this->getEducationPayload()
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_create_educations(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . "/{$registryIdTest}",
                $this->getEducationPayload()
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_show_educations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/' . $this->registry->id,
                $this->getEducationPayload()
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertTrue($content['registry_id'] === $this->registry->id);
    }

    public function test_the_application_can_update_educations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/' . $this->registry->id,
                $this->getEducationPayload()
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
                $this->getUpdatedEducationPayload()
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['title'], $this->getUpdatedEducationPayload()['title']);
        $this->assertEquals($content['institute_name'], $this->getUpdatedEducationPayload()['institute_name']);
        $this->assertEquals($content['registry_id'], $this->registry->id);
    }


    public function test_the_application_cannot_update_educations(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $latestEducation = Education::query()->orderBy('id', 'desc')->first();
        $educationIdTest = $latestEducation ? $latestEducation->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . "/{$educationIdTest}/{$registryIdTest}",
                $this->getUpdatedEducationPayload()
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_delete_educations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/' . $this->registry->id,
                $this->getEducationPayload()
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id
            );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_delete_educations(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $latestEducation = Education::query()->orderBy('id', 'desc')->first();
        $educationIdTest = $latestEducation ? $latestEducation->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . "/{$educationIdTest}/{$registryIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    private function getEducationPayload(): array
    {
        return [
            'title' => fake()->sentence(6),
            'from' => Carbon::now()->toDateString(),
            'to' => Carbon::now()->toDateString(),
            'institute_name' => fake()->company(),
            'institute_address' => fake()->address(),
            'institute_identifier' => fake()->sentence(5),
            'source' => '',
            'registry_id' => $this->registry->id,
        ];
    }

    private function getUpdatedEducationPayload(): array
    {
        return [
            'title' => 'My Education Title',
            'from' => Carbon::now()->toDateString(),
            'to' => Carbon::now()->toDateString(),
            'institute_name' => 'Fake Institution',
            'institute_address' => fake()->address(),
            'institute_identifier' => fake()->sentence(5),
            'source' => '',
            'registry_id' => $this->registry->id,
        ];
    }

    private function getEditedEducationPayload(): array
    {
        return [
            'title' => 'My Actual Education Title',
        ];
    }
}
