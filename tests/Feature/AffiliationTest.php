<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Database\Seeders\AffiliationSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class AffiliationTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/affiliations';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            AffiliationSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_show_affiliations_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/1'
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_an_affiliation_by_registry_id(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/1',
                [
                    'member_id' => fake()->uuid(),
                    'organisation_id' => 1,
                    'current_employer' => 1,
                    'relationship' => 'employee'
                ]
            );

        $response->assertStatus(201);

        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_an_affiliation(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/1',
            [
                'member_id' => 'A1234567',
                'organisation_id' => 1,
                'current_employer' => 1,
                'relationship' => 'employee'
            ]
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_edit_an_affiliation(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PATCH',
            self::TEST_URL . '/1',
            [
                'member_id' => 'A1234567',
            ]
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['member_id'], 'A1234567');
        $this->assertEquals($content['current_employer'], 0);
    }

    public function test_the_application_can_delete_an_affiliation(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/1'
            );

        $response->assertStatus(200);
    }
}
