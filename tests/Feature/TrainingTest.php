<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class TrainingTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/training';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_list_training_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/registry/' . $this->user->registry_id
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotNull($response->decodeResponseJson()['data']);
    }

    public function test_the_application_can_show_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/1'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'provider' => 'Fake Training Provider',
                    'awarded_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addYears(5),
                    'expires_in_years' => 5,
                    'training_name' => 'Completely made up Researcher Training',
                    'certification_id' => null,
                    'pro_registration' => fake()->randomElement([0, 1]),
                    'registry_id' => $this->user->registry_id,
                ]
            );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $content);

        $this->assertDatabaseHas('registry_has_trainings', [
            'training_id' => $content,
            'registry_id' => $this->user->registry_id,
        ]);
    }

    public function test_the_application_can_update_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            [
                'provider' => 'Fake Training Provider',
                'awarded_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addYears(5),
                'expires_in_years' => 5,
                'training_name' => 'Completely made up Researcher Training',
                'certification_id' => 1,
                'pro_registration' => 0,
                'registry_id' => $this->user->registry_id,
            ]
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $content);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content,
                [
                    'provider' => 'Fake Training Provider 2',
                    'awarded_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addYears(8),
                    'expires_in_years' => 8,
                    'training_name' => 'Completely made up Researcher Training v2',
                    'certification_id' => 1,
                    'pro_registration' => 1,
                ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['provider'], 'Fake Training Provider 2');
        $this->assertEquals($content['expires_in_years'], 8);
        $this->assertEquals($content['training_name'], 'Completely made up Researcher Training v2');
        $this->assertEquals($content['pro_registration'], 1);
    }

    public function test_the_application_can_delete_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'provider' => 'Fake Training Provider',
                    'awarded_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addYears(5),
                    'expires_in_years' => 5,
                    'training_name' => 'Pointless Training that will be Deleted',
                    'certification_id' => null,
                    'pro_registration' => 0,
                    'registry_id' => $this->user->registry_id,
                ]
            );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $content);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content,
            );

        $response->assertStatus(200);
    }

    public function test_the_application_fails_deleting_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'provider' => 'Fake Training Provider',
                    'awarded_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addYears(5),
                    'expires_in_years' => 5,
                    'training_name' => 'Pointless Training that will be Deleted',
                    'certification_id' => null,
                    'pro_registration' => 0,
                    'registry_id' => $this->user->registry_id,
                ]
            );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $content);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . ($content + 1),
            );

        $response->assertStatus(404);
    }
}
