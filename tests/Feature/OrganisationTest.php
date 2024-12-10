<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Sector;
use App\Models\Project;
use App\Models\ProjectHasOrganisation;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\BaseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class OrganisationTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/organisations';

    private $user = null;
    private $testOrg = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            PermissionSeeder::class,
            IssuerSeeder::class,
            BaseDemoSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();

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
            'dsptk_certified' => 1,
            'dsptk_certification_num' => '12345Z',
            'iso_27001_certified' => 0,
            'iso_27001_certification_num' => '',
            'ce_certified' => 1,
            'ce_certification_num' => 'A1234',
            'sector_id' => fake()->randomElement([0, count(Sector::SECTORS)]),
        ];
    }

    public function test_the_application_can_search_on_org_name(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '?organisation_name[]=health'
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) === 1);
        $this->assertTrue($content['data'][0]['organisation_name'] === 'Health Pathways (UK) Limited');
    }

    public function test_the_application_can_list_an_organisations_past_present_and_future_projects(): void
    {
        // Grab all projects and manually set dates to fit our need,
        // otherwise these tests would effectively timeout eventually.
        $orgProjects = ProjectHasOrganisation::where('organisation_id', '1')->get();
        for ($i = 0; $i < 1; $i++) {
            $project = Project::where('id', $orgProjects[$i]->project_id)->first();
            $project->start_date = Carbon::now();
            $project->end_date = Carbon::now()->addYears(1);
            $project->save();
        }


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1/projects/present',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue($content['data'][0]['start_date'] <= Carbon::now());
        $this->assertTrue($content['data'][0]['end_date'] >= Carbon::now());

        for ($i = 0; $i < 1; $i++) {
            $project = Project::where('id', $orgProjects[$i]->project_id)->first();
            $project->start_date = Carbon::now()->subYears(2);
            $project->end_date = Carbon::now()->subYears(1);
            $project->save();
        }

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1/projects/past',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue($content['data'][0]['start_date'] < Carbon::now());
        $this->assertTrue($content['data'][0]['end_date'] < Carbon::now());


        for ($i = 0; $i < 1; $i++) {
            $project = Project::where('id', $orgProjects[$i]->project_id)->first();
            $project->start_date = Carbon::now()->addYears(2);
            $project->end_date = Carbon::now()->addYears(3);
            $project->save();
        }

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1/projects/future',
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue($content['data'][0]['start_date'] > Carbon::now());
        $this->assertTrue($content['data'][0]['end_date'] > Carbon::now());
    }

    public function test_the_application_can_list_organisations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $response->assertJsonStructure([
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
        ]);
    }

    public function test_the_application_can_show_organisations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->testOrg
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $content['data']
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

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->testOrg
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_update_organisations(): void
    {
        $isoCertified = fake()->randomElement([1, 0]);
        $ceCertified = fake()->randomElement([1, 0]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->testOrg
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
                ]
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $this->assertDatabaseHas('organisations', [
            'verified' => true,
        ]);
    }

    public function test_the_application_can_delete_organisations(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->testOrg
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->json(
            'DELETE',
            self::TEST_URL . '/' . $content['data']
        );

        $response->assertStatus(200);
    }

    public function test_the_application_can_show_idvt(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1/idvt'
            );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'idvt_result',
                'idvt_result_perc',
                'idvt_completed_at',
                'idvt_errors',
            ],
        ]);
    }

    public function test_the_application_can_sort_returned_data(): void
    {
        $this->testOrg['organisation_name'] = 'ZYX Org';

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->testOrg
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $this->testOrg['organisation_name'] = 'ABC Org';

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->testOrg
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '?sort=organisation_name:desc'
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) > 0);
        $this->assertTrue($content['data'][0]['organisation_name'] === 'ZYX Org');

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '?sort=organisation_name:asc'
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) > 0);
        $this->assertTrue($content['data'][0]['organisation_name'] === 'ABC Org');
    }
}
