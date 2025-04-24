<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\DecisionModel;
use App\Models\CustodianModelConfig;
use App\Models\CustodianHasRule;
use App\Models\User;
use App\Models\Custodian;
use App\Models\Sector;
use Carbon\Carbon;
use App\Models\PendingInvite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class CustodianTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/custodians';

    private $user = null;
    private $projectUniqueId = '';
    private $organisationUniqueId = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('id', 1)->first();

        $this->projectUniqueId = Str::random(40);
        $this->organisationUniqueId = Str::random(40);
    }

    public function test_the_application_can_list_custodians(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200);
        $this->assertArrayHaskey('data', $response);
    }

    public function test_the_application_can_show_custodians(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/1'
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_can_invite_a_custodian(): void
    {

        Queue::fake();
        Queue::assertNothingPushed();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/1/invite/',
            );

        $response->assertStatus(201);

        $invites = PendingInvite::all();

        $this->assertTrue(count($invites) === 1);
    }

    public function test_the_application_can_create_custodians(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Custodian',
                    'contact_email' => 'test@test.com',
                    'enabled' => true,
                    'idvt_required' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_the_application_adds_entity_models_to_newly_created_custodians(): void
    {
        $this->enableObservers();
        CustodianModelConfig::truncate();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Custodian',
                    'contact_email' => 'test@test.com',
                    'enabled' => true,
                    'idvt_required' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $entities = DecisionModel::all();
        $conf = CustodianModelConfig::where([
            'custodian_id' => $content['data'],
        ])->get()->toArray();

        $this->assertTrue(count($conf) === count($entities));
    }

    public function test_the_application_can_update_custodians(): void
    {
        $this->enableObservers();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Custodian',
                    'contact_email' => 'test@test.com',
                    'enabled' => true,
                    'idvt_required' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();
        $this->assertGreaterThan(0, $content['data']);


        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '/' . $content['data'] . '/action_log'
        );

        $response->assertStatus(200);
        $responseData = $response['data'];
        $actionLog = collect($responseData)
            ->firstWhere('action', Custodian::ACTION_COMPLETE_CONFIGURATION);

        $this->assertNull($actionLog['completed_at']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PUT',
                self::TEST_URL . '/' . $content['data'],
                [
                    'name' => 'Updated Custodian',
                    'enabled' => false,
                    'idvt_required' => true,
                ]
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Updated Custodian');
        $this->assertEquals($content['enabled'], false);
        $this->assertEquals($content['idvt_required'], true);
    }

    public function test_the_application_can_delete_custodians(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'name' => 'Test Custodian',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content['data']
            );

        $response->assertStatus(200);
    }

    public function test_the_application_can_get_custodians_by_unique_identifier(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'name' => 'Test Custodian ABC123',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $custodianCreated = Custodian::where('id', $content['data'])->first();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/identifier/' . $custodianCreated->unique_identifier
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertEquals($content['name'], 'Test Custodian ABC123');
    }

    public function test_the_application_can_receive_custodian_pushes_with_valid_key(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'name' => 'Test Custodian ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $custodian = Custodian::where('id', $content['data'])->first();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/push',
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
                        'dsptk_ods_code' => '12345Z',
                        'iso_27001_certified' => 0,
                        'iso_27001_certification_num' => '',
                        'ce_certified' => 1,
                        'ce_certification_num' => 'A1234',
                        'sector_id' => fake()->randomElement([0, count(Sector::SECTORS)]),
                        'charities' => [
                            'registration_id' => '1186569',
                        ],
                        'ror_id' => '02wnqcb97',
                        'smb_status' => false,
                        'organisation_size' => 2,
                        'website' => 'https://www.website.com/',
                    ],
                ],
            ],
                [
                'x-custodian-key' => $custodian->unique_identifier,
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
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'Test Custodian ABCDEF',
                    'contact_email' => 'test@test.com',
                    'enabled' => true,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $custodian = Custodian::where('id', $content['data'])->first();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
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
                        'dsptk_ods_code' => '12345Z',
                        'iso_27001_certified' => 0,
                        'iso_27001_certification_num' => '',
                        'ce_certified' => 1,
                        'ce_certification_num' => 'A1234',
                        'sector_id' => fake()->randomElement([0, count(Sector::SECTORS)]),
                        'charities' => [
                            'registration_id' => '1186569',
                        ],
                        'ror_id' => '02wnqcb97',
                        'smb_status' => false,
                        'organisation_size' => 2,
                        'website' => 'https://www.website.com/',
                    ],
                ],
            ]
            );

        $response->assertStatus(401);
        $content = $response->decodeResponseJson();

        $this->assertEquals($content['message'], 'you must be a trusted custodian and provide your custodian-key within the request headers');
    }

    public function test_the_application_can_refuse_pushes_when_key_is_invalid(): void
    {

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                'name' => 'Test Custodian ABCDEF',
                'contact_email' => 'test@test.com',
                'enabled' => true,
            ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson();

        $custodian = Custodian::where('id', $content['data'])->first();

        $this->enableMiddleware();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
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
                            'dsptk_ods_code' => '12345Z',
                            'iso_27001_certified' => 0,
                            'iso_27001_certification_num' => '',
                            'ce_certified' => 1,
                            'ce_certification_num' => 'A1234',
                            'sector_id' => fake()->randomElement([0, count(Sector::SECTORS)]),
                            'charities' => [
                                'registration_id' => '1186569',
                            ],
                            'ror_id' => '02wnqcb97',
                            'smb_status' => false,
                            'organisation_size' => 2,
                            'website' => 'https://www.website.com/',
                        ],
                    ],
                ],
                [
                    'x-custodian-key' => $custodian->unique_identifier . 'broken_key',
                ],
            );

        $response->assertStatus(401);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['message'], 'you must provide your Custodian key');
    }


    public function test_the_application_can_sort_returned_data(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'ZYX Custodian',
                    'contact_email' => 'test@test.com',
                    'enabled' => true,
                    'idvt_required' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'name' => 'ABC Custodian',
                    'contact_email' => 'test@test.com',
                    'enabled' => true,
                    'idvt_required' => false,
                ]
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '?sort=name:desc'
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) > 0);
        $this->assertTrue($content['data']['data'][0]['name'] === 'ZYX Custodian');

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '?sort=name:asc'
            );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();

        $this->assertTrue(count($content['data']) > 0);
        $this->assertTrue($content['data']['data'][0]['name'] === 'ABC Custodian');
    }

    public function test_can_get_rules_for_existing_custodian(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                "/api/v1/custodians/1/rules"
            );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'title',
                    'description'
                ]
            ]
        ]);

        $nonexistentId = 99999;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                "/api/v1/custodians/{$nonexistentId}/rules"
            );

        $response->assertStatus(404);
    }


    public function test_can_update_rules_for_existing_custodian(): void
    {
        CustodianHasRule::truncate();
        $newRuleIds = [1, 3, 4];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'PATCH',
                '/api/v1/custodians/1/rules',
                [
                    'rule_ids' => $newRuleIds,
                ]
            );

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'success',
            'data' => true,
        ]);

        $this->assertEquals(
            count($newRuleIds),
            \DB::table('custodian_has_rules')
                ->where('custodian_id', 1)
                ->count()
        );
        foreach ($newRuleIds as $ruleId) {
            $this->assertDatabaseHas('custodian_has_rules', [
                'custodian_id' => 1,
                'rule_id' => $ruleId
            ]);
        }
    }
}
