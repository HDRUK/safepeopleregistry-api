<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use App\Models\Registry;
use Tests\TestCase;
use Tests\Traits\Authorisation;

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
                self::TEST_URL . '/' . $this->registry->id
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
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

    public function test_the_application_can_edit_educations(): void
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
                'PATCH',
                self::TEST_URL . '/' . $content['data'] . '/' . $this->registry->id,
                $this->getEditedEducationPayload()
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['title'], $this->getEditedEducationPayload()['title']);
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
