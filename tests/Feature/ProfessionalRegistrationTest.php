<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Registry;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\ProfessionalRegistration;

class ProfessionalRegistrationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/professional_registrations';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_show_professional_registrations_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/registry/1'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_show_professional_registrations_by_registry_id(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "/registry/{$registryIdTest}"
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_an_professional_registration_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/registry/1',
                [
                    'member_id' => fake()->uuid(),
                    'name' => fake()->name(),
                ]
            );

        $response->assertStatus(201);

        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_create_an_professional_registration_by_registry_id(): void
    {
        $latestRegistry = Registry::query()->orderBy('id', 'desc')->first();
        $registryIdTest = $latestRegistry ? $latestRegistry->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . "/registry/{$registryIdTest}",
                [
                    'member_id' => fake()->uuid(),
                    'name' => fake()->name(),
                ]
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_fails_creating_a_professional_registration_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/registry/1',
                [
                ]
            );

        $response->assertStatus(400);
    }

    public function test_the_application_can_update_an_professional_registration(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL . '/registry/1',
            [
                'member_id' => fake()->uuid(),
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/' . $content['id'],
            [
                'member_id' => fake()->uuid(),
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_update_an_professional_registration(): void
    {
        $latestProfessionalRegistration = ProfessionalRegistration::query()->orderBy('id', 'desc')->first();
        $professionalRegistrationIdTest = $latestProfessionalRegistration ? $latestProfessionalRegistration->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "/{$professionalRegistrationIdTest}",
            [
                'member_id' => fake()->uuid(),
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_fails_updating_a_professional_registration(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/1',
                []
            );

        $response->assertStatus(400);
    }

    public function test_the_application_can_delete_an_professional_registration(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL . '/registry/1',
            [
                'member_id' => fake()->uuid(),
                'name' => fake()->name(),
            ]
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content['id']
            );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_delete_an_professional_registration(): void
    {
        $latestProfessionalRegistration = ProfessionalRegistration::query()->orderBy('id', 'desc')->first();
        $professionalRegistrationIdTest = $latestProfessionalRegistration ? $latestProfessionalRegistration->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . "/{$professionalRegistrationIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
