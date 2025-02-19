<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use App\Models\User;
use Database\Seeders\TrainingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class TrainingTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/training';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            TrainingSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
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
                    'registry_id' => 1,
                    'provider' => 'Fake Training Provider',
                    'awarded_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addYears(5),
                    'expires_in_years' => 5,
                    'training_name' => 'Completely made up Researcher Training',
                    'certification_id' => null,
                    'pro_registration' => fake()->randomElement([0, 1]),
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $response->decodeResponseJson()['data']);
    }

    public function test_the_application_can_update_training(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            [
                'registry_id' => 1,
                'provider' => 'Fake Training Provider',
                'awarded_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addYears(5),
                'expires_in_years' => 5,
                'training_name' => 'Completely made up Researcher Training',
                'certification_id' => 1,
                'pro_registration' => 0,
            ]
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $content);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PATCH',
                self::TEST_URL . '/' . $content,
                [
                    'registry_id' => 1,
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
                    'registry_id' => 1,
                    'provider' => 'Fake Training Provider',
                    'awarded_at' => Carbon::now(),
                    'expires_at' => Carbon::now()->addYears(5),
                    'expires_in_years' => 5,
                    'training_name' => 'Pointless Training that will be Deleted',
                    'certification_id' => null,
                    'pro_registration' => 0,
                ]
            );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThan(0, $content);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content
            );

        $response->assertStatus(200);
    }
}
