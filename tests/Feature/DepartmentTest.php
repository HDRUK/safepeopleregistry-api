<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Department;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class DepartmentTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/departments';


    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_departments(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_departments(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1'
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_show_departments(): void
    {
        $latestDepartment = Department::query()->orderBy('id', 'desc')->first();
        $departmentIdTest = $latestDepartment ? $latestDepartment->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$departmentIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_departments(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Department',
                    'category' => 'Test Category',
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_departments(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Department',
                    'category' => 'Test Category',
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();
        $this->assertGreaterThan(0, $content['data']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PATCH',
                self::TEST_URL . '/' . $content['data'],
                [
                    'name' => 'Updated Department',
                ]
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Updated Department');
        $this->assertEquals($content['category'], 'Test Category');
    }

    public function test_the_application_cannot_update_departments(): void
    {
        $latestDepartment = Department::query()->orderBy('id', 'desc')->first();
        $departmentIdTest = $latestDepartment ? $latestDepartment->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PATCH',
                self::TEST_URL . "/{$departmentIdTest}",
                [
                    'name' => 'Updated Department',
                ]
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_delete_departments(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Department',
                    'category' => 'Test Category',
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content['data']
            );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_delete_departments(): void
    {
        $latestDepartment = Department::query()->orderBy('id', 'desc')->first();
        $departmentIdTest = $latestDepartment ? $latestDepartment->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . "/{$departmentIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
