<?php

namespace Tests\Feature;

use App\Models\Identity;
use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class IdentityTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/identities';

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_the_application_can_list_identities(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL);

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_identities(): void
    {
        $passed = fake()->randomElement([0, 1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'registry_id' => 1,
                    'address_1' => '123 Blah blah',
                    'address_2' => '',
                    'town' => 'Town',
                    'county' => 'County',
                    'country' => 'Country',
                    'postcode' => 'BLA4 4HH',
                    'dob' => '1988-01-01',
                    'idvt_success' => $passed,
                    'idvt_result' => ($passed === 1 ? 'approved' : 'declined'),
                    'idvt_completed_at' => Carbon::now(),
                    'idvt_identification_number' => null,
                    'idvt_document_type' => null,
                    'idvt_document_number' => null,
                    'idvt_document_country' => null,
                    'idvt_document_valid_until' => null,
                    'idvt_attempt_id' => null,
                    'idvt_context_id' => null,
                    'idvt_document_dob' => null,
                    'idvt_context' => null,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('GET', self::TEST_URL . '/' . $content['data']);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_identities(): void
    {
        $passed = fake()->randomElement([0, 1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'registry_id' => 1,
                    'address_1' => '123 Blah blah',
                    'address_2' => '',
                    'town' => 'Town',
                    'county' => 'County',
                    'country' => 'Country',
                    'postcode' => 'BLA4 4HH',
                    'dob' => '1988-01-01',
                    'idvt_success' => $passed,
                    'idvt_result' => ($passed === 1 ? 'approved' : 'declined'),
                    'idvt_completed_at' => Carbon::now(),
                    'idvt_identification_number' => null,
                    'idvt_document_type' => null,
                    'idvt_document_number' => null,
                    'idvt_document_country' => null,
                    'idvt_document_valid_until' => null,
                    'idvt_attempt_id' => null,
                    'idvt_context_id' => null,
                    'idvt_document_dob' => null,
                    'idvt_context' => null,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_identities(): void
    {
        $passed = fake()->randomElement([0, 1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'registry_id' => 1,
                    'address_1' => '123 Blah blah',
                    'address_2' => '',
                    'town' => 'Town',
                    'county' => 'County',
                    'country' => 'Country',
                    'postcode' => 'BLA4 4HH',
                    'dob' => '1988-01-01',
                    'idvt_success' => $passed,
                    'idvt_result' => ($passed === 1 ? 'approved' : 'declined'),
                    'idvt_completed_at' => Carbon::now(),
                    'idvt_identification_number' => null,
                    'idvt_document_type' => null,
                    'idvt_document_number' => null,
                    'idvt_document_country' => null,
                    'idvt_document_valid_until' => null,
                    'idvt_attempt_id' => null,
                    'idvt_context_id' => null,
                    'idvt_document_dob' => null,
                    'idvt_context' => null,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $newDate = Carbon::now()->subYears(2);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'],
                [
                    'registry_id' => 1,
                    'address_1' => '123 Blah blah',
                    'address_2' => '',
                    'town' => 'Town',
                    'county' => 'County',
                    'country' => 'Country',
                    'postcode' => 'BLA4 4HH',
                    'dob' => '1988-01-01',
                    'idvt_success' => $passed,
                    'idvt_result' => ($passed === 1 ? 'approved' : 'declined'),
                    'idvt_completed_at' => Carbon::now(),
                    'idvt_identification_number' => null,
                    'idvt_document_type' => 'PASSPORT',
                    'idvt_document_number' => null,
                    'idvt_document_country' => null,
                    'idvt_document_valid_until' => null,
                    'idvt_attempt_id' => null,
                    'idvt_context_id' => null,
                    'idvt_document_dob' => null,
                    'idvt_context' => null,
                ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $this->assertDatabaseHas('identities', [
            'id' => $content['data']['id'],
            'dob' => '1988-01-01',
            'idvt_document_type' => 'PASSPORT',
        ]);
    }

    public function test_the_application_can_delete_identities(): void
    {
        $passed = fake()->randomElement([0, 1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'registry_id' => 1,
                    'address_1' => '123 Blah blah',
                    'address_2' => '',
                    'town' => 'Town',
                    'county' => 'County',
                    'country' => 'Country',
                    'postcode' => 'BLA4 4HH',
                    'dob' => '1988-01-01',
                    'idvt_success' => $passed,
                    'idvt_result' => ($passed === 1 ? 'approved' : 'declined'),
                    'idvt_completed_at' => Carbon::now(),
                    'idvt_identification_number' => null,
                    'idvt_document_type' => 'PASSPORT',
                    'idvt_document_number' => null,
                    'idvt_document_country' => null,
                    'idvt_document_valid_until' => null,
                    'idvt_attempt_id' => null,
                    'idvt_context_id' => null,
                    'idvt_document_dob' => null,
                    'idvt_context' => null,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('DELETE', self::TEST_URL . '/' . $content['data']);

        $response->assertStatus(200);
    }

    public function test_the_application_cannot_get_identities(): void
    {
        $latestIdentity = Identity::query()->orderBy('id', 'desc')->first();
        $identityIdTest = $latestIdentity ? $latestIdentity->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'GET',
                self::TEST_URL . "/{$identityIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_update_identities(): void
    {
         $latestIdentity = Identity::query()->orderBy('id', 'desc')->first();
        $identityIdTest = $latestIdentity ? $latestIdentity->id + 1 : 1;

        $passed = fake()->randomElement([0, 1]);
        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "/{$identityIdTest}",
                [
                    'registry_id' => 1,
                    'address_1' => '123 Blah blah',
                    'address_2' => '',
                    'town' => 'Town',
                    'county' => 'County',
                    'country' => 'Country',
                    'postcode' => 'BLA4 4HH',
                    'dob' => '1988-01-01',
                    'idvt_success' => $passed,
                    'idvt_result' => ($passed === 1 ? 'approved' : 'declined'),
                    'idvt_completed_at' => Carbon::now(),
                    'idvt_identification_number' => null,
                    'idvt_document_type' => 'PASSPORT',
                    'idvt_document_number' => null,
                    'idvt_document_country' => null,
                    'idvt_document_valid_until' => null,
                    'idvt_attempt_id' => null,
                    'idvt_context_id' => null,
                    'idvt_document_dob' => null,
                    'idvt_context' => null,
                ]
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_cannot_delete_identities(): void
    {
        $latestIdentity = Identity::query()->orderBy('id', 'desc')->first();
        $identityIdTest = $latestIdentity ? $latestIdentity->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'DELETE',
                self::TEST_URL . "/{$identityIdTest}"
            );

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }
}
