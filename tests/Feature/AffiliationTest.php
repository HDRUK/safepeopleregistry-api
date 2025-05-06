<?php

namespace Tests\Feature;

use Carbon\Carbon;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use App\Traits\CommonFunctions;

class AffiliationTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CommonFunctions;

    public const TEST_URL = '/api/v1/affiliations';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
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
                    'organisation_id' => 1,
                    'member_id' => fake()->uuid(),
                    'relationship' => 'employee',
                    'from' => Carbon::now()->subYears(5)->toDateString(),
                    'to' => '',
                    'department' => 'Research',
                    'role' => 'Researcher',
                    'email' => fake()->email(),
                    'ror' => $this->generateRorID(),
                    'registry_id' => 1,
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

    public function test_the_application_can_update_registry_affiliations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/1/affiliation/1'
            );
        $response->assertStatus(500);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/1/affiliation/1?status=approved'
            );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/1/affiliation/1?status=rejected'
        );
        $response->assertStatus(200);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/1/affiliation/1?status=rejected'
        );
        $response->assertStatus(500);


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/1/affiliation/999?status=rejected'
        );
        $response->assertStatus(404);

    }
}
