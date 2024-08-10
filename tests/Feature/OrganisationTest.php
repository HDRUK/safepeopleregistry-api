<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/organisations';

    private $headers = [];
    private $testOrg = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            PermissionSeeder::class,
            IssuerSeeder::class,
            OrganisationSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];

        $this->testOrg = [
            'organisation_name' => 'HEALTH DATA RESEARCH UK',
            'address_1' => '215 Euston Road',
            'address_2' => '',
            'town' => 'Blah',
            'county' => 'London',
            'country' => 'United Kingdom',
            'postcode' => 'NW1 2BE',
            'lead_applicant_organisation_name' => 'Some One',
            'lead_applicant_email' => fake()->email(),
            'password' => 'tempP4ssword',
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => 'Some One, Some Two, Some Three',
            'funders_and_sponsors' => 'UKRI, MRC',
            'sub_license_arrangements' => 'N/A',
            'verified' => false,
            'companies_house_no' => '10887014',
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
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    0 => [
                        'id',
                        'created_at',
                        'updated_at',
                        'organisation_name',
                        'address_1',
                        'address_2',
                        'town',
                        'county',
                        'country',
                        'postcode',
                        'lead_applicant_organisation_name',
                        'lead_applicant_email',
                        'organisation_unique_id',
                        'applicant_names',
                        'funders_and_sponsors',
                        'sub_license_arrangements',
                        'verified',
                        'dsptk_ods_code',
                        'iso_27001_certified',
                        'ce_certified',
                        'ce_certification_num',
                        'approvals',
                        'permissions',
                        'files',
                        'registries',
                    ],
                ],
            ],
        ]);
    }

    public function test_the_application_can_show_organisations(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            $this->testOrg,
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'GET',
            self::TEST_URL.'/'.$content,
            $this->headers
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'created_at',
                'updated_at',
                'organisation_name',
                'address_1',
                'address_2',
                'town',
                'county',
                'country',
                'postcode',
                'lead_applicant_organisation_name',
                'lead_applicant_email',
                'organisation_unique_id',
                'applicant_names',
                'funders_and_sponsors',
                'sub_license_arrangements',
                'verified',
                'dsptk_ods_code',
                'iso_27001_certified',
                'ce_certified',
                'ce_certification_num',
                'approvals',
                'permissions',
                'files',
                'registries',
            ],
        ]);
    }

    public function test_the_application_can_create_organisations(): void
    {
        $isoCertified = fake()->randomElement([1, 0]);
        $ceCertified = fake()->randomElement([1, 0]);

        $response = $this->json(
            'POST',
            self::TEST_URL,
            $this->testOrg,
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_organisations(): void
    {
        $isoCertified = fake()->randomElement([1, 0]);
        $ceCertified = fake()->randomElement([1, 0]);

        $response = $this->json(
            'POST',
            self::TEST_URL,
            $this->testOrg,
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $newDate = Carbon::now()->subYears(2);

        $response = $this->json(
            'PUT',
            self::TEST_URL.'/'.$content,
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
                'companies_house_no' => '10887014',
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
        $isoCertified = fake()->randomElement([1, 0]);
        $ceCertified = fake()->randomElement([1, 0]);

        $response = $this->json(
            'POST',
            self::TEST_URL,
            $this->testOrg,
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $response = $this->json(
            'DELETE',
            self::TEST_URL.'/'.$content,
            $this->headers
        );

        $response->assertStatus(200);
    }

    public function test_the_application_can_show_idvt(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL . '/1/idvt',
            $this->headers
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    'id',
                    'idvt_result',
                    'idvt_result_perc',
                    'idvt_completed_at',
                    'idvt_errors',
                ],

            ],
        ]);
    }
}