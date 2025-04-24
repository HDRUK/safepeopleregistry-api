<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class ProjectRoleTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/project_roles';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_project_roles(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL,
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) === 8);
        $this->assertTrue($content['data'][0]['name'] === 'Principal Investigator (PI)');
    }

    public function test_the_application_can_show_a_project_role(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertTrue($content['name'] === 'Principal Investigator (PI)');
    }

    public function test_the_application_can_create_project_roles(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Role 123',
                ]
            );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertTrue($response->decodeResponseJson()['data'] > 0);
    }

    public function test_the_application_can_update_project_roles(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Role 123',
            ]
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertTrue($content > 0);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/' . $content,
            [
                'name' => 'Test Role 321',
            ]
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];
        $this->assertTrue($content['name'] === 'Test Role 321');
    }
}
