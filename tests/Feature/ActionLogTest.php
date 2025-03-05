<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Affiliation;
use App\Models\Registry;
use App\Models\RegistryHasAffiliation;
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

        $response->assertJson(['data' => $expectedResponse]);

    }

    public function test_it_can_log_user_profile_complete()
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
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROFILE_COMPLETED);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

    }

    public function test_it_can_log_user_affiliations_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $user = User::factory()->create();
        $registry = Registry::factory()->create();
        $user->update([
            'registry_id' => $registry->id,
        ]);
        $user->refresh();

        $affiliation = Affiliation::factory()->create();
        RegistryHasAffiliation::create([
            'registry_id' => $user->registry_id,
            'affiliation_id' => $affiliation->id,
        ]);

        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "users/{$user->id}/action_log",
        );

        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_AFFILIATIONS_COMPLETE);

        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );
    }

    /*
    - waiting for RegistryHasTraining to be implemented in another task
    public function test_it_can_log_user_training_complete(){

    }
    */

    public function test_it_can_log_user_project_review_complete()
    {
        Carbon::setTestNow(Carbon::now());
        $user = User::factory()->create();


        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "users/{$user->id}/action_log",
        );

        $response->assertStatus(200);
        $responseData = $response['data'];

        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROJECTS_REVIEW);

        $actionLogId = $actionLog['id'];

        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "action_log/{$actionLogId}?complete",
        );
        $response->assertStatus(200);


        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "users/{$user->id}/action_log",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];


        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROJECTS_REVIEW);


        $this->assertEquals(
            Carbon::now()->format('Y-m-d H:i:s'),
            $actionLog['completed_at']
        );

        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . "action_log/{$actionLogId}?incomplete",
        );
        $response->assertStatus(200);


        $response = $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . "users/{$user->id}/action_log",
        );
        $response->assertStatus(200);
        $responseData = $response['data'];


        $actionLog = collect($responseData)
            ->firstWhere('action', User::ACTION_PROJECTS_REVIEW);

        $this->assertNull(
            $actionLog['completed_at']
        );
    }




}
