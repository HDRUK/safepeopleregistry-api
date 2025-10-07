<?php

namespace Tests\Feature;

use App\Models\History;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class HistoryTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/histories';

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_group', 'USERS')->first();
    }

    public function test_the_application_can_list_histories(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL);

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_histories(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('POST', self::TEST_URL, [
                'affiliation_id' => 1,
                'endorsement_id' => 1,
                'infringement_id' => 1,
                'project_id' => 1,
                'access_key_id' => 1,
                'custodian_identifier' => '20895720385sodhfsjkdhfksjfh20935209538',
            ]);

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL . '/' . $content['data']);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_cannot_show_histories_by_id(): void
    {
        $latestHistory = History::query()->orderBy('id', 'desc')->first();
        $historyIdTest = $latestHistory ? $latestHistory->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL . "/{$historyIdTest}");

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_histories(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->Json(
                'POST',
                self::TEST_URL,
                [
                    'affiliation_id' => 1,
                    'endorsement_id' => 1,
                    'infringement_id' => 1,
                    'project_id' => 1,
                    'access_key_id' => 1,
                    'custodian_identifier' => '20895720385sodhfsjkdhfksjfh20935209538',
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }
}
