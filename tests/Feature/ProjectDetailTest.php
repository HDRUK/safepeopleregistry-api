<?php

namespace Tests\Feature;

use Gateway;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use App\Models\Registry;
use App\Models\Project;
use App\Models\ProjectDetail;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class ProjectDetailTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/project_details';

    private $registry = null;
    private $project = null;
    private $mockedPayload = [];
    private $mockedGatewayResponse = [
            "id" => 17,
            "created_at" =>  "2022-01-17T14:31:30.000000Z",
            "updated_at" =>  "2024-07-31T02:55:10.000000Z",
            "deleted_at" =>  null,
            "non_gateway_datasets" =>  [
                "Permission to Contact"
            ],
            "non_gateway_applicants" =>  [],
            "funders_and_sponsors" =>  [],
            "other_approval_committees" =>  [],
            "gateway_outputs_tools" =>  [],
            "gateway_outputs_papers" =>  [],
            "non_gateway_outputs" =>  [],
            "project_title" =>  "Phase II/III Study of AZD2816, a Vaccine for the Prevention of COVID-19 in Adults",
            "project_id_text" =>  "DARS-NIC-474218-V7G7L-v0.3",
            "organisation_name" =>  "AstraZeneca UK Limited",
            "organisation_sector" =>  "Commercial",
            "lay_summary" =>  null,
            "technical_summary" =>  null,
            "latest_approval_date" =>  "2021-05-07 00:00:00",
            "manual_upload" =>  true,
            "rejection_reason" =>  null,
            "sublicence_arrangements" =>  null,
            "public_benefit_statement" =>  null,
            "data_sensitivity_level" =>  "De-Personalised",
            "project_start_date" =>  null,
            "project_end_date" =>  null,
            "access_date" =>  null,
            "accredited_researcher_status" =>  null,
            "confidential_data_description" =>  null,
            "dataset_linkage_description" =>  null,
            "duty_of_confidentiality" =>  null,
            "legal_basis_for_data_article6" =>  "Health and Social Care Act 2012 – s261(2)(c)",
            "legal_basis_for_data_article9" =>  null,
            "national_data_optout" =>  null,
            "organisation_id" =>  null,
            "privacy_enhancements" =>  null,
            "request_category_type" =>  null,
            "request_frequency" =>  "One-Off",
            "access_type" =>  "TRE",
            "mongo_object_dar_id" =>  null,
            "enabled" =>  true,
            "last_activity" =>  "2024-07-31 02:55:10",
            "counter" =>  58,
            "mongo_object_id" =>  "61e57dc1ac76c16329c5cafc",
            "mongo_id" =>  "977698358737561",
            "user_id" =>  null,
            "team_id" =>  78,
            "application_id" =>  null,
            "applicant_id" =>  null,
            "sector_id" =>  2,
            "status" =>  "ACTIVE",
            "datasets" =>  [],
            "publications" =>  [],
            "tools" =>  [],
            "keywords" =>  [
                [
                    "id" =>  15,
                    "name" =>  "covid-19",
                    "enabled" =>  true,
                    "created_at" =>  null,
                    "updated_at" =>  "2025-01-09T07:05:35.000000Z",
                    "pivot" =>  [
                        "dur_id" =>  17,
                        "keyword_id" =>  15
                    ]
                ],
                [
                    "id" =>  32,
                    "name" =>  "National Core Study",
                    "enabled" =>  true,
                    "created_at" =>  null,
                    "updated_at" =>  "2024-10-08T12:01:31.000000Z",
                    "pivot" =>  [
                        "dur_id" =>  17,
                        "keyword_id" =>  32
                    ]
                ],
                [
                    "id" =>  55,
                    "name" =>  "Vaccines",
                    "enabled" =>  true,
                    "created_at" =>  "2024-10-08T12:01:31.000000Z",
                    "updated_at" =>  "2024-10-08T12:01:31.000000Z",
                    "pivot" =>  [
                        "dur_id" =>  17,
                        "keyword_id" =>  55
                    ]
                ]
            ],
            "user" =>  null,
            "team" =>  [
                "id" =>  78,
                "pid" =>  "da31bca3-ead1-4cc3-acd9-2bc5de5e409d",
                "created_at" =>  "2024-10-08T11:18:49.000000Z",
                "updated_at" =>  "2024-10-22T15:29:27.000000Z",
                "deleted_at" =>  null,
                "name" =>  "NHS England Secure Data Environment (SDE)",
                "enabled" =>  true,
                "allows_messaging" =>  false,
                "workflow_enabled" =>  false,
                "access_requests_management" =>  false,
                "uses_5_safes" =>  false,
                "is_admin" =>  false,
                "team_logo" =>  "/teams/1728500728_snsdenegland1.png",
                "member_of" =>  "ALLIANCE",
                "contact_point" =>  "ssd.nationalservicedesk@nhs.net",
                "application_form_updated_by" =>  "Qresearch webapp",
                "application_form_updated_on" =>  "0001-01-01 00:00:00",
                "mongo_object_id" =>  "6427fbba72aa1325df67a776",
                "notification_status" =>  false,
                "is_question_bank" =>  false,
                "is_provider" =>  false,
                "url" =>  "https://digital.nhs.uk/services/secure-data-environment-service",
                "introduction" =>  "{\"type\" => \"doc\",\"content\" => [{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"The NHS England Secure Data Environment (SDE) is a secure data and research analysis platform. It gives approved researchers with approved projects secure access to NHS healthcare data for healthcare analysis. All patient information in the SDE is pseudonymised. It is part of the NHS Research SDE Network across England.\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"To access the NHS England Secure Data Environment a valid Data Sharing Agreement is required. Start the process using our Data Access Request Service here: https://digital.nhs.uk/services/data-access-request-service-dars/process\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"Cost to access: We use a cost recovery model to calculate the charge to assess the NHS England SDE, which can be viewed here: https://digital.nhs.uk/services/secure-data-environment-service\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"marks\" => [{\"type\" => \"bold\"}],\"text\" => \"SAFE People - Login & Access\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Applicant Organisations accredited via DSPT\\\" (suggest expanding acronym)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Data Access by Applicant Organisation restricted by Data Sharing Agreement (DSA)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Applicant Organisation responsible for accrediting end users operating under a DSA\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ End User Access Agreement defines acceptable/expected behaviours\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Login: Browser based login using 2FA\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ International Access: At a technology-level yes. There are direction sharing agreement restrictions on the use of (some) data\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"marks\" => [{\"type\" => \"bold\"}],\"text\" => \"SAFE Settings - Compute & Services\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Data hosted securely on NHSE-managed Amazon Web Services (AWS) accounts\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Dedicated analysis environment provisioned specifically for each Data Sharing Agreement (DSA)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Users operating in one analysis environment have no access to other environments or the public internet\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"marks\" => [{\"type\" => \"bold\"}],\"text\" => \"SAFE Settings - Security Certifications and Measures\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Automated scanning & benchmarking against Centre for Internet Security guidelines\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Static code analysis integrated into build pipelines\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Infrastructure monitored 24/7 by NHSE Cyber Security Operations Centre (CSOC)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Dedicated cyber security allocated to support delivery team\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Penetration Testing conducted after every significant architectural change\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Independent Threat Modelling exercise conducted\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Developers with access to production environment are subject to security vetting\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"SAFE Settings - Software access\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Direct access to provisioned data only possible via Databricks platform.\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ All queries executed via Databricks recorded & auditable in Immuta (access control & auditing component/product)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Code packages/libraries made available in the environment are subject to review / approval process\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"marks\" => [{\"type\" => \"bold\"}],\"text\" => \"SAFE Data - Data Access Mechanisms\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Data minimised and provisioned in accordance with the approved Data Sharing Agreement (DSA)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Data only accessible from DSA-specific analysis environment; data cannot be transferred between analysis environments\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Identifiable fields pseudonymised in accordance with the DSA.\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Unique pseudo key used for each DSA (a given value will be represented differently in each agreement)\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Fields containing sensitive data removed from data asset prior to data provisioning process\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"marks\" => [{\"type\" => \"bold\"}],\"text\" => \"SAFE Outputs - Data Output/export\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ SDE service allows research (with small number suppression) results to be safely exported. This is via an escrow function manned by NHS England employees who examine the artefacts prior to release to the user – based upon an approved, principles-based disclosure control output policy. Patient / row level data cannot be exported.\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Export plans: NHSE are currently exploring a range of safe data outputs types and welcome the opportunity of brining NCS projects into this exploration. We anticipate being able to allow user’s code to be exported in the future.\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Data transmit to other SAFE Settings: We do not provide this service at present. However, ad-hoc requests can be considered via a Service Request\"}]},{\"type\" => \"paragraph\",\"content\" => [{\"type\" => \"text\",\"text\" => \"✓ Statistical disclosure control process in place\"}]}]}",
                "dar_modal_header" =>  null,
                "dar_modal_content" =>  null,
                "dar_modal_footer" =>  null,
                "is_dar" =>  false,
                "service" =>  null
            ]  ,
            "application" =>  null,
            "users" =>  [],
            "applications" =>  []
        ];

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        $this->registry = Registry::where('id', $this->user->registry_id)->first();
        $this->project = Project::where('id', 1)->first();

        $this->mockedPayload = [
            'project_id' => $this->project->id,
            'datasets' => [
                'https://doesnot.exist/datasets/1',
                'https://doesnot.exist/datasets/2',
                'https://doesnot.exist/datasets/3',
            ],
            'other_approval_committees' => [
                'Approval Committee 1',
                'Approval Committee 2',
                'Approval Committee 3',
            ],
            'data_sensitivity_level' => 'Anonymous',
            'legal_basis_for_data_article6' => 'Legal basis for data article6 blah blah',
            'duty_of_confidentiality' => 1,
            'national_data_optout' => 0,
            'request_frequency' => 'ONE-OFF',
            'dataset_linkage_description' => 'Datasets are linked by means of...',
            'data_minimisation' => 'Our approach to data minimisation is...',
            'data_use_description' => 'Our description of the data being used is...',
            'access_date' => '2025-12-02',
            'access_type' => 1,
            'data_privacy' => 'Our data privacy methods are...',
            'research_outputs' => '{"research_outputs": [ "https://mydomain.com/research1", "https://mydomain.com/research2"] }',
            'data_assets' => 'Our data assets are...',
        ];
    }

    public function test_the_application_can_create_project_details(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                $this->mockedPayload
            );

        $response->assertStatus(201);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotNull($response->decodeResponseJson()['data']);
        $this->assertTrue($response->decodeResponseJson()['data'] > 0);
    }

    public function test_the_application_can_list_project_detail(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            $this->mockedPayload
        );

        $response->assertStatus(201);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL,
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertNotNull($content);
        $this->assertTrue(count($content['data']) === 1);
        $this->assertEquals($content['data'][0]['project_id'], $this->project->id);
    }

    public function test_the_application_can_show_project_detail(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            $this->mockedPayload
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL . '/' . $content,
            );

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response);

        $content = $response->decodeResponseJson()['data'];

        $this->assertNotNull($content);
        $this->assertEquals($content['project_id'], $this->project->id);
        $this->assertEquals($content['request_frequency'], 'ONE-OFF');
    }

    public function test_the_application_can_delete_project_detail(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            $this->mockedPayload
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'DELETE',
                self::TEST_URL . '/' . $content,
            );

        $response->assertStatus(200);

        $details = ProjectDetail::all();
        $this->assertTrue(count($details) === 0);
    }

    public function test_the_application_can_update_project_details(): void
    {
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'POST',
            self::TEST_URL,
            $this->mockedPayload
        );

        $response->assertStatus(201);
        $content = $response->decodeResponseJson()['data'];

        $this->updatedPayloadPart = [
            'duty_of_confidentiality' => 0,
            'national_data_optout' => 1,
            'request_frequency' => 'RECURRING',
            'access_type' => 0,
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PUT',
            self::TEST_URL . '/' . $content,
            $this->updatedPayloadPart
        );

        $response->assertStatus(200);
        $content = $response->decodeResponseJson()['data'];

        $this->assertTrue($content['access_type'] === 0);
        $this->assertTrue($content['duty_of_confidentiality'] === 0);
        $this->assertTrue($content['national_data_optout'] === 1);
        $this->assertTrue($content['request_frequency'] === 'RECURRING');
    }

    public function test_the_application_can_query_gateway(): void
    {
        Gateway::shouldReceive('getDataUsesByProjectID')
            ->once()
            ->with(1, 1)
            ->andReturn($this->mockedGatewayResponse);

        $response = Gateway::getDataUsesByProjectID(1, 1);
        $this->assertEquals($response, $this->mockedGatewayResponse);
    }

    public function test_the_application_can_query_gateway_via_api(): void
    {
        Gateway::shouldReceive('getDataUsesByProjectID')
        ->once()
        ->with(1, 1)
        ->andReturn($this->mockedGatewayResponse);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL . '/query_gateway_dur',
                [
                    'custodian_id' => 1,
                    'project_id' => 1,
                ]
            );
        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertEquals($content['data']['project_id_text'], $this->mockedGatewayResponse['project_id_text']);
    }
}
