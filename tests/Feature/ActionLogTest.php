<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Carbon\Carbon;

class ActionLogTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/';


    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_creates_action_logs_when_a_user_is_created()
    {
        $user = User::factory()->create();
        foreach (User::getDefaultActions() as $action) {
            $this->assertDatabaseHas('action_logs', [
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'action' => $action,
                'completed_at' => null,
            ]);
        }
    }

    public function test_it_returns_user_action_logs_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "users/{$user->id}/action_log",
        );

        $response->assertStatus(200);

        $expectedResponse = array_map(function ($action) use ($user) {
            return [
                'entity_id' => $user->id,
                'action' => $action,
                'completed_at' => null,
            ];
        }, User::getDefaultActions());

        $response->assertJson($expectedResponse);

    }

    public function test_it_returns_user_action_logs_updated_via_api()
    {
        Carbon::setTestNow(Carbon::now());
        $user = User::factory()->create();

        $user->update([
            'first_name' => fake()->firstname(),
            'last_name' => fake()->lastname(),
            'email' => fake()->email(),
            'location' => fake()->country()
        ]);


        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "users/{$user->id}/action_log",
        );

        $response->assertStatus(200);
        $expectedPartialResponse = [
                'entity_id' => $user->id,
                'action' => 'profile_completed',
                'completed_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];

        $response->assertJsonFragment($expectedPartialResponse);
    }

}
