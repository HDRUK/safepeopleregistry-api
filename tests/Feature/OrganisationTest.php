<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Tests\TestCase;

use App\Models\Organisation;

use Database\Seeders\UserSeeder;

use Illuminate\Support\Str;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\Authorisation;

class OrganisationTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/organisations';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_list_organisations(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_organisations(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'organisation_name' => 'Test Organisation',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'lead_applicant_organisation_name' => 'Some One',
                'lead_applicant_email' => fake()->email(),
                'password' => 'tempP4ssword',
                'organisation_unique_id' => Str::random(40),
                'applicant_names' => 'Some One, Some Two, Some Three',
                'funders_and_sponsors' => 'UKRI, MRC',
                'sub_license_arrangements' => 'N/A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'GET',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_organisations(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'organisation_name' => 'Test Organisation',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'lead_applicant_organisation_name' => 'Some One',
                'lead_applicant_email' => fake()->email(),
                'password' => 'tempP4ssword',
                'organisation_unique_id' => Str::random(40),
                'applicant_names' => 'Some One, Some Two, Some Three',
                'funders_and_sponsors' => 'UKRI, MRC',
                'sub_license_arrangements' => 'N/A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_organisations(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'organisation_name' => 'Test Organisation',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'lead_applicant_organisation_name' => 'Some One',
                'lead_applicant_email' => fake()->email(),
                'password' => 'tempP4ssword',
                'organisation_unique_id' => Str::random(40),
                'applicant_names' => 'Some One, Some Two, Some Three',
                'funders_and_sponsors' => 'UKRI, MRC',
                'sub_license_arrangements' => 'N/A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $newDate = Carbon::now()->subYears(2);

        $response = $this->json(
            'PUT',
            self::TEST_URL . '/' . $content,
            [
                'organisation_name' => 'Test Organisation',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'lead_applicant_organisation_name' => 'Some One',
                'lead_applicant_email' => fake()->email(),
                'password' => 'tempP4ssword',
                'organisation_unique_id' => Str::random(40),
                'applicant_names' => 'Some One, Some Two, Some Three',
                'funders_and_sponsors' => 'UKRI, MRC',
                'sub_license_arrangements' => 'N/A',
                'verified' => true,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertDatabaseHas('organisations', [
            'verified' => true,
        ]);
    }

    public function test_the_application_can_delete_organisations(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'organisation_name' => 'Test Organisation',
                'address_1' => '123 Blah blah',
                'address_2' => '',
                'town' => 'Town',
                'county' => 'County',
                'country' => 'Country',
                'postcode' => 'BLA4 4HH',
                'lead_applicant_organisation_name' => 'Some One',
                'lead_applicant_email' => fake()->email(),
                'password' => 'tempP4ssword',
                'organisation_unique_id' => Str::random(40),
                'applicant_names' => 'Some One, Some Two, Some Three',
                'funders_and_sponsors' => 'UKRI, MRC',
                'sub_license_arrangements' => 'N/A',
                'verified' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content,
            $this->headers
        );

        $response->assertStatus(200);
    }
}