<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class IdentityTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/identities';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
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

        $passRate = fake()->numberBetween(80, 100);
        $failRate = fake()->numberBetween(0, 50);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'registry_id' => 1,
                'selfie_path' => 'path/to/selfie.jpeg',
                'passport_path' => 'path/to/passport.jpeg',
                'drivers_license_path' => 'path/to/drivers_license.jpeg',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'dob' => '1988-01-01',
                'idvt_result' => $passed,
                'idvt_result_perc' => ($passed ? $passRate : $failRate),
                'idvt_errors' => null,
                'idvt_completed_at' => Carbon::now(),
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

        $passRate = fake()->numberBetween(80, 100);
        $failRate = fake()->numberBetween(0, 50);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'registry_id' => 1,
                'selfie_path' => 'path/to/selfie.jpeg',
                'passport_path' => 'path/to/passport.jpeg',
                'drivers_license_path' => 'path/to/drivers_license.jpeg',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'dob' => '1988-01-01',
                'idvt_result' => $passed,
                'idvt_result_perc' => ($passed ? $passRate : $failRate),
                'idvt_errors' => null,
                'idvt_completed_at' => Carbon::now(),
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_identities(): void
    {
        $passed = fake()->randomElement([0, 1]);

        $passRate = fake()->numberBetween(80, 100);
        $failRate = fake()->numberBetween(0, 50);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'registry_id' => 1,
                'selfie_path' => 'path/to/selfie.jpeg',
                'passport_path' => 'path/to/passport.jpeg',
                'drivers_license_path' => 'path/to/drivers_license.jpeg',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'dob' => '1988-01-01',
                'idvt_result' => $passed,
                'idvt_result_perc' => ($passed ? $passRate : $failRate),
                'idvt_errors' => null,
                'idvt_completed_at' => Carbon::now(),
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
                'selfie_path' => 'path/to/selfie1.jpeg',
                'passport_path' => 'path/to/passport.jpeg',
                'drivers_license_path' => 'path/to/drivers_license.jpeg',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'dob' => '1978-01-01',
                'idvt_result' => $passed,
                'idvt_result_perc' => ($passed ? $passRate : $failRate),
                'idvt_errors' => null,
                'idvt_completed_at' => Carbon::now(),
            ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $this->assertDatabaseHas('identities', [
            'id' => $content['data']['id'],
            'dob' => '1978-01-01',
            'selfie_path' => 'path/to/selfie1.jpeg',
        ]);
    }

    public function test_the_application_can_delete_identities(): void
    {
        $passed = fake()->randomElement([0, 1]);

        $passRate = fake()->numberBetween(80, 100);
        $failRate = fake()->numberBetween(0, 50);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'registry_id' => 1,
                'selfie_path' => 'path/to/selfie.jpeg',
                'passport_path' => 'path/to/passport.jpeg',
                'drivers_license_path' => 'path/to/drivers_license.jpeg',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'dob' => '1988-01-01',
                'idvt_result' => $passed,
                'idvt_result_perc' => ($passed ? $passRate : $failRate),
                'idvt_errors' => null,
                'idvt_completed_at' => Carbon::now(),
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('DELETE', self::TEST_URL . '/' . $content['data']);

        $response->assertStatus(200);
    }
}
