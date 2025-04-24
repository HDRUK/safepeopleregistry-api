<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\Authorisation;

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

    public function test_the_application_can_edit_an_professional_registration(): void
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
            'PATCH',
            self::TEST_URL . '/' . $content['id'],
            [
                'member_id' =>  'A1234567',
            ]
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['member_id'], 'A1234567');
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
}
