<?php

namespace Tests\Feature;

use App\Models\Issuer;
use Carbon\Carbon;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class IssuerTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;

    public const TEST_URL = '/api/v1/issuers';

    private $headers = [];

    private $projectUniqueId = '';

    private $organisationUniqueId = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            UserSeeder::class,
            IssuerSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$this->getAuthToken(),
        ];

        $this->projectUniqueId = Str::random(40);
        $this->organisationUniqueId = Str::random(40);
    }

    public function test_the_application_can_list_issuers(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_issuers(): void
    {
        $response = $this->json(
            'GET',
            self::TEST_URL.'/1',
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_create_issuers(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer',
                'contact_email' => 'test@test.com',
                'enabled' => true,
                'idvt_required' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_issuers(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer',
                'contact_email' => 'test@test.com',
                'enabled' => true,
                'idvt_required' => false,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];
        $this->assertGreaterThan(0, $content);

        $response = $this->json(
            'PUT',
            self::TEST_URL.'/'.$content,
            [
                'name' => 'Updated Issuer',
                'enabled' => false,
                'idvt_required' => true,
            ],
            $this->headers
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Updated Issuer');
        $this->assertEquals($content['enabled'], false);
        $this->assertEquals($content['idvt_required'], true);
    }

    public function test_the_application_can_delete_issuers(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
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

    public function test_the_application_can_get_issuers_by_unique_identifier(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABC123',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuerCreated = Issuer::where('id', $content)->first();

        $response = $this->json(
            'GET',
            self::TEST_URL.'/identifier/'.$issuerCreated->unique_identifier,
            $this->headers
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Test Issuer ABC123');
    }

    public function test_the_application_can_receive_issuer_pushes_with_valid_key(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuer = Issuer::where('id', $content)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL.'/push',
            [
                'researchers' => [],
                'projects' => [
                    [
                        'unique_id' => $this->projectUniqueId,
                        'title' => 'This is a Test Project',
                        'lay_summary' => 'Test Lay Summary',
                        'public_benefit' => 'No one dies, ever.',
                        'request_category_type' => 'category type',
                        'technical_summary' => 'Technical Summary',
                        'other_approval_committees' => 'Does anyone actually know what this means?',
                        'start_date' => Carbon::now()->addMonths(6),
                        'end_date' => Carbon::now()->addYears(2),
                        'affiliate_id' => 1,
                    ],
                ],
                'organisations' => [
                    [
                        'organisation_name' => 'TRE Approved Org',
                        'address_1' => '123 Road',
                        'address_2' => '',
                        'town' => 'Town',
                        'county' => 'County',
                        'country' => 'Country',
                        'postcode' => 'AB12 3CD',
                        'lead_applicant_organisation_name' => fake()->name(),
                        'organisation_unique_id' => $this->organisationUniqueId,
                        'applicant_names' => 'Fred, Barney, Wilma and Betty',
                        'funders_and_sponsors' => 'UKRI, MRC',
                        'sub_license_arrangements' => fake()->sentence(5),
                        'verified' => false,
                        'dsptk_ods_code' => null,
                        'companies_house_no' => '928572',
                    ],
                ],
            ],
            [
                'x-issuer-key' => $issuer->unique_identifier,
            ]
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals('0', $content['researchers_created']);
        $this->assertEquals('1', $content['projects_created']);
        $this->assertEquals('1', $content['organisations_created']);
    }

    public function test_the_application_can_refuse_pushes_with_missing_key(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuer = Issuer::where('id', $content)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL.'/push',
            [
                'researchers' => [],
                'projects' => [
                    [
                        'unique_id' => $this->projectUniqueId,
                        'title' => 'This is a Test Project',
                        'lay_summary' => 'Test Lay Summary',
                        'public_benefit' => 'No one dies, ever.',
                        'request_category_type' => 'category type',
                        'technical_summary' => 'Technical Summary',
                        'other_approval_committees' => 'Does anyone actually know what this means?',
                        'start_date' => Carbon::now()->addMonths(6),
                        'end_date' => Carbon::now()->addYears(2),
                        'affiliate_id' => 1,
                    ],
                ],
                'organisations' => [
                    [
                        'organisation_name' => 'TRE Approved Org',
                        'address_1' => '123 Road',
                        'address_2' => '',
                        'town' => 'Town',
                        'county' => 'County',
                        'country' => 'Country',
                        'postcode' => 'AB12 3CD',
                        'lead_applicant_organisation_name' => fake()->name(),
                        'organisation_unique_id' => $this->organisationUniqueId,
                        'applicant_names' => 'Fred, Barney, Wilma and Betty',
                        'funders_and_sponsors' => 'UKRI, MRC',
                        'sub_license_arrangements' => fake()->sentence(5),
                        'verified' => false,
                        'dsptk_ods_code' => null,
                        'companies_house_no' => '287652',
                    ],
                ],
            ],
            [
            ]
        );

        $response->assertStatus(401);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'you must provide your Issuer key');
    }

    public function test_the_application_can_refuse_pushes_when_key_is_invalid(): void
    {
        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'name' => 'Test Issuer ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ],
            $this->headers
        );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $issuer = Issuer::where('id', $content)->first();

        $response = $this->json(
            'POST',
            self::TEST_URL.'/push',
            [
                'researchers' => [],
                'projects' => [
                    [
                        'unique_id' => $this->projectUniqueId,
                        'title' => 'This is a Test Project',
                        'lay_summary' => 'Test Lay Summary',
                        'public_benefit' => 'No one dies, ever.',
                        'request_category_type' => 'category type',
                        'technical_summary' => 'Technical Summary',
                        'other_approval_committees' => 'Does anyone actually know what this means?',
                        'start_date' => Carbon::now()->addMonths(6),
                        'end_date' => Carbon::now()->addYears(2),
                        'affiliate_id' => 1,
                    ],
                ],
                'organisations' => [
                    [
                        'organisation_name' => 'TRE Approved Org',
                        'address_1' => '123 Road',
                        'address_2' => '',
                        'town' => 'Town',
                        'county' => 'County',
                        'country' => 'Country',
                        'postcode' => 'AB12 3CD',
                        'lead_applicant_organisation_name' => fake()->name(),
                        'organisation_unique_id' => $this->organisationUniqueId,
                        'applicant_names' => 'Fred, Barney, Wilma and Betty',
                        'funders_and_sponsors' => 'UKRI, MRC',
                        'sub_license_arrangements' => fake()->sentence(5),
                        'verified' => false,
                        'dsptk_ods_code' => null,
                        'companies_house_no' => '182736',
                    ],
                ],
            ],
            [
                'x-issuer-key' => $issuer->unique_identifier.'broken_key',
            ]
        );

        $response->assertStatus(401);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['message'], 'no known issuer matches the credentials provided');
    }
}
