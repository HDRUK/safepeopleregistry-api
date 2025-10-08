<?php

namespace Tests\Feature;

use App\Models\ProjectHasUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use KeycloakGuard\ActingAsKeycloakUser;

class ProjectHasUserTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/project_users';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_project_users_by_id(): void
    {
        $projectUserId = 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$projectUserId}",
            );

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_list_project_users_by_id(): void
    {
        $lastestProjectHasUser = ProjectHasUser::query()->orderBy('id', 'desc')->first();
        $projectHasUserIdTest = $lastestProjectHasUser ? $lastestProjectHasUser->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . "/{$projectHasUserIdTest}",
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_delete_project_has_users_by_id(): void
    {
        $lastestProjectHasUser = ProjectHasUser::query()->orderBy('id', 'desc')->first();
        $projectHasUserIdTest = $lastestProjectHasUser ? $lastestProjectHasUser->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'DELETE',
            self::TEST_URL . "/{$projectHasUserIdTest}",
            []
        );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
