<?php

namespace Database\Seeders;

use Str;
use Keycloak;
use RegistryManagementController as RMC;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Identity;
use App\Models\Project;
use App\Models\Registry;
use App\Models\Education;
use App\Models\Training;
use App\Models\Organisation;
use App\Models\Affiliation;
use App\Models\OrganisationHasCharity;
use App\Models\Charity;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\ProjectHasOrganisation;
use App\Models\ProjectHasUser;
use App\Models\ProjectRole;
use App\Models\ProjectHasCustodian;
use App\Models\RegistryHasTraining;
use App\Models\RegistryHasAffiliation;
use App\Models\OrganisationHasDepartment;
use App\Models\OrganisationHasCustodianApproval;
use App\Models\State;
use App\Models\UserHasCustodianApproval;
use App\Traits\CommonFunctions;
use Illuminate\Database\Seeder;

class BaseDemoSeeder extends Seeder
{
    use CommonFunctions;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SectorSeeder::class,
            StateSeeder::class,
            EntityModelTypeSeeder::class,
            EntityModelSeeder::class,
            PermissionSeeder::class,
            RulesSeeder::class,
            CustodianSeeder::class,
            SystemConfigSeeder::class,
            ProjectRoleSeeder::class,
            EmailTemplatesSeeder::class,
            DepartmentSeeder::class,
            WebhookEventTriggerSeeder::class,
        ]);

        // --------------------------------------------------------------------------------
        // A demo Organisation which demonstrates safety at every step
        // --------------------------------------------------------------------------------
        $org1 = Organisation::create([
            'organisation_name' => 'Health Pathways (UK) Limited',
            'address_1' => '3 WATERHOUSE SQUARE',
            'address_2' => '138-142 HOLBORN',
            'town' => 'LONDON',
            'county' => 'GREATER LONDON',
            'country' => 'United Kingdom',
            'postcode' => 'EC1N 2SW',
            'lead_applicant_organisation_name' => 'Dr. Organisation Owner',
            'lead_applicant_email' => 'organisation.owner@healthdataorganisation.com',
            'password' => '$2y$12$g.LfOEaJZqjcDyZ51PwGxuKT9ceoAcHE8h6YmQXc5ZKY1a5wyGjPW', // Ask LS "Flood********"
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => 'Dr. Organisation Owner',
            'funders_and_sponsors' => 'Innovate UK',
            'sub_license_arrangements' => '...',
            'verified' => true,
            'dsptk_ods_code' => '8HQ90',
            'iso_27001_certified' => true,
            'ce_certified' => true,
            'ce_plus_certified' => true,
            'companies_house_no' => '09349495',
            'sector_id' => 5, // Charity/Non-profit
            'ror_id' => '02wnqcb97',
            'smb_status' => true,
            'organisation_size' => 2,
            'website' => 'https://www.website1.com/',
        ]);

        OrganisationHasCustodianApproval::create([
            'organisation_id' => $org1->id,
            'custodian_id' => Custodian::first()->id,
        ]);

        $charity = Charity::create([
            'registration_id' => '1186569',
            'name' => 'Health Pathways UK Charity',
            'website' => 'https://www.website1.com/',
            'address_1' => '3 WATERHOUSE SQUARE',
            'address_2' => '138-142 HOLBORN',
            'town' => 'LONDON',
            'county' => 'GREATER LONDON',
            'country' => 'United Kingdom',
            'postcode' => 'EC1N 2SW',
        ]);

        OrganisationHasCharity::create([
            'organisation_id' => $org1->id,
            'charity_id' => $charity->id,
        ]);

        $org1Depts = [
            2, 3, 6, 11, 13, 20, 22, 23,
        ];

        foreach ($org1Depts as $depts) {
            OrganisationHasDepartment::create([
                'organisation_id' => $org1->id,
                'department_id' => $depts,
            ]);
        }

        $org1Proj1 = Project::create([
            'unique_id' => Str::random(20),
            'title' => 'Exploring the Impact of Digital Health Interventions on Mental Health Outcomes in Young Adults',
            'lay_summary' => 'This study aims to evaluate how digital mental health interventions (such as mobile apps for meditation, cognitive behavioral therapy, and mental health tracking) affect the mental health and well-being of young adults aged 18-30. By analyzing data from a large sample of users who have consented to share their anonymized usage information and mental health outcomes, we hope to understand which types of interventions are most effective and identify patterns in user engagement. This information will be essential for designing better digital health tools that support young adult mental health.',
            'public_benefit' => 'The findings from this research could lead to improved digital health interventions tailored to the mental health needs of young adults, particularly those facing increased stress, anxiety, and depression. Better-targeted tools could enhance the mental health support available to this age group and reduce the burden on healthcare systems by providing accessible, preventive care through mobile and digital platforms.',
            'request_category_type' => 'Health and Social Research',
            'technical_summary' => 'This project involves analyzing anonymized, aggregated data from digital health applications used by young adults. The dataset includes app usage metrics, such as frequency and duration of sessions, type of intervention (e.g., mindfulness meditation, journaling), and self-reported mental health outcomes gathered through in-app surveys. The research team will use statistical modeling and machine learning techniques to identify patterns and correlations between app usage and mental health improvements. The analysis will follow strict ethical guidelines, ensuring data security and user privacy, with all personal identifiers removed prior to analysis. The results will be statistically summarized, and individual data points will not be reported.',
            'other_approval_committees' => 'This project requires approval from:

University Institutional Review Board (IRB) to ensure ethical considerations are met.
Data Access Committee (DAC) from the app providers to secure permissions for using anonymized, aggregated data.
Health Research Authority (HRA) Approval as it involves health-related research on human subjects.',
            'start_date' => '2025-01-12',
            'end_date' => '2026-01-12',
        ]);

        $org1Proj1->setState(State::STATE_PROJECT_APPROVED);

        ProjectHasCustodian::create([
            'project_id' => $org1Proj1->id,
            'custodian_id' => Custodian::first()->id,
            'approved' => true
        ]);

        ProjectHasOrganisation::create([
            'project_id' => $org1Proj1->id,
            'organisation_id' => $org1->id,
        ]);

        $org1Proj2 = Project::create([
            'unique_id' => Str::random(20),
            'title' => 'Assessing Air Quality Impact on Respiratory Health in Urban Populations',
            'lay_summary' => 'This research seeks to understand how air quality in densely populated urban areas affects respiratory health, particularly focusing on conditions like asthma and chronic obstructive pulmonary disease (COPD). By analyzing anonymized health data and environmental sensor data, we aim to identify correlations between air pollution levels and the prevalence of respiratory issues. This insight could help guide policies on urban planning and pollution reduction to improve public health outcomes in cities.',
            'public_benefit' => 'The study has the potential to benefit public health by identifying how air pollution directly impacts respiratory health. By linking specific air quality levels with health conditions, the research could guide efforts to improve air quality standards, urban planning, and healthcare services, ultimately reducing respiratory issues in urban populations.',
            'request_category_type' => 'Environmental and Public Health Research',
            'technical_summary' => 'This study will analyze anonymized patient health records from urban hospitals, focusing on respiratory diagnoses, alongside real-time air quality data sourced from environmental monitoring stations within the city. The dataset includes daily pollutant concentrations (e.g., PM2.5, NO2, O3) and corresponding health outcomes in the population. Using statistical methods and regression models, the research will examine the relationship between air pollution levels and respiratory health. Data will be processed and stored in secure, encrypted environments with no identifiable information retained. Findings will be presented in aggregate to inform public health and policy recommendations without revealing individual health data.',
            'other_approval_committees' => 'This project will require approval from:

Institutional Review Board (IRB) to verify ethical standards in handling health data.
Environmental Data Ethics Committee (if available within the environmental data provider organization) for permission to access air quality data.
National Public Health Ethics Committee for authorization to analyze population health data on respiratory conditions.',
            'start_date' => '2025-03-01',
            'end_date' => '2025-09-01',
        ]);

        $org1Proj2->setState(State::STATE_PROJECT_PENDING);

        ProjectHasCustodian::create([
            'project_id' => $org1Proj2->id,
            'custodian_id' => Custodian::first()->id,
            'approved' => true
        ]);

        ProjectHasOrganisation::create([
            'project_id' => $org1Proj2->id,
            'organisation_id' => $org1->id,
        ]);

        // --------------------------------------------------------------------------------
        // End
        // --------------------------------------------------------------------------------

        // --------------------------------------------------------------------------------
        // A demo Organisation which demonstrates questionable safety
        // --------------------------------------------------------------------------------
        $org2 = Organisation::create([
            'organisation_name' => 'TANDY ENERGY LIMITED',
            'address_1' => '818 Whitchurch Lane',
            'address_2' => '',
            'town' => 'Whitchurch',
            'county' => 'Bristol',
            'country' => 'United Kingdom',
            'postcode' => 'BS14 0JP',
            'lead_applicant_organisation_name' => 'Mrs. Organisation Owner',
            'lead_applicant_email' => 'organisation.owner@commercialdataorganisation.co.uk',
            'password' => '$2y$12$g.LfOEaJZqjcDyZ51PwGxuKT9ceoAcHE8h6YmQXc5ZKY1a5wyGjPW', // Ask LS "Flood********"
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => 'Mr. Tony Howell, Mr. John Thomas, Mr. Thomas Chase',
            'funders_and_sponsors' => '',
            'sub_license_arrangements' => '...',
            'verified' => false,
            'dsptk_ods_code' => '',
            'iso_27001_certified' => true,
            'ce_certified' => false,
            'ce_plus_certified' => false,
            'companies_house_no' => '15765271',
            'sector_id' => 4, // Public
            'ror_id' => '02wnqcb97',
            'smb_status' => true,
            'organisation_size' => 2,
            'website' => 'https://www.website2.com/',
        ]);

        OrganisationHasCharity::create([
            'organisation_id' => $org2->id,
            'charity_id' => $charity->id,
        ]);

        $org2Depts = [
            2, 11, 13, 23,
        ];

        foreach ($org2Depts as $depts) {
            OrganisationHasDepartment::create([
                'organisation_id' => $org2->id,
                'department_id' => $depts,
            ]);
        }

        $org2Proj1 = Project::create([
            'unique_id' => Str::random(20),
            'title' => 'Social Media Influence on Mental Health Trends Among Teenagers',
            'lay_summary' => 'This study aims to understand the influence of social media usage patterns on the mental health of teenagers. We will gather and analyze data directly from various social media platforms, alongside survey responses from teenagers, to identify correlations between time spent online and mental health indicators like stress, anxiety, and depression.',
            'public_benefit' => 'The findings from this study may benefit the public by providing insights into the impact of social media on mental health. The data may show how platform usage contributes to increased stress or mental health conditions in teenagers, which could help inform social media policies or interventions aimed at improving user experience and well-being.',
            'request_category_type' => 'Social Media and Mental Health Research',
            'technical_summary' => 'In this study, researchers will access raw social media data, including usernames, post histories, and usage metrics from teenage participants. The data will also be supplemented by survey responses where participants will self-report mental health symptoms. Analysis will focus on identifying patterns between high usage rates and reported mental health symptoms using correlational analysis. Some sensitive data, like usernames and post contents, will be retained to ensure data accuracy, though only researchers directly involved in the project will access it. Findings will be shared with mental health organizations and potentially used to develop targeted social media policies.',
            'other_approval_committees' => 'This project may require approval from:

University Institutional Review Board (IRB) for ethical review.
Social Media Platform’s Data Access Committee to allow access to platform data.',
            'start_date' => '2025-01-01',
            'end_date' => '2026-01-01',
        ]);

        $org2Proj1->setState(State::STATE_PROJECT_PENDING);

        ProjectHasCustodian::create([
            'project_id' => $org2Proj1->id,
            'custodian_id' => Custodian::first()->id,
            'approved' => true
        ]);

        ProjectHasOrganisation::create([
            'project_id' => $org2Proj1->id,
            'organisation_id' => $org2->id,
        ]);

        // Add parallel collaborator of org1 to org2's project
        ProjectHasOrganisation::create([
            'project_id' => $org2Proj1->id,
            'organisation_id' => $org1->id,
        ]);


        // --------------------------------------------------------------------------------
        // End
        // --------------------------------------------------------------------------------

        // --------------------------------------------------------------------------------
        // An organisation that demonstrates nefarious links to tabacco industry
        // --------------------------------------------------------------------------------
        $org3 = Organisation::create([
            'organisation_name' => 'TOBACCO EUROPE, LTD',
            'address_1' => 'Enterprise House',
            'address_2' => '2 Pass Street',
            'town' => 'Oldham',
            'county' => 'Manchester',
            'country' => 'United Kingdom',
            'postcode' => 'OL9 6HZ',
            'lead_applicant_organisation_name' => 'Mr. T Obacco',
            'lead_applicant_email' => 't.obacco@commercialdataorganisation.org',
            'password' => '$2y$12$g.LfOEaJZqjcDyZ51PwGxuKT9ceoAcHE8h6YmQXc5ZKY1a5wyGjPW', // Ask LS "Flood********"
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => 'Petr Lalak, Roman Mixa, Stuart Poppleton',
            'funders_and_sponsors' => 'Big Tobacco Co.',
            'sub_license_arrangements' => '...',
            'verified' => false,
            'dsptk_ods_code' => '',
            'iso_27001_certified' => true,
            'ce_certified' => true,
            'ce_plus_certified' => true,
            'companies_house_no' => '07563555',
            'sector_id' => 6, // Private/Industry
            'ror_id' => null,
            'smb_status' => null,
            'organisation_size' => 2,
            'website' => null,
        ]);

        $org3Depts = [
            11, 16,
        ];

        foreach ($org3Depts as $depts) {
            OrganisationHasDepartment::create([
                'organisation_id' => $org3->id,
                'department_id' => $depts,
            ]);
        }

        $proj = Project::create([
            'unique_id' => Str::random(20),
            'title' => 'Analyzing Health Data to Identify Resilient Smokers: A Study on Tolerance to Tobacco Use',
            'lay_summary' => 'This study aims to explore patterns in health data to identify factors that may indicate resilience to smoking-related illnesses, focusing on smokers who exhibit fewer negative health outcomes. By collecting and analyzing detailed health records, we aim to create profiles of individuals who can tolerate smoking with minimal health consequences, potentially informing tailored marketing strategies for tobacco products.',
            'public_benefit' => 'The research findings could benefit society by helping to identify the physiological and lifestyle factors that make some individuals less susceptible to smoking-related diseases. By understanding these factors, we could inform tobacco consumers about "safer" smoking habits and promote responsible use among those less affected by health risks.',
            'request_category_type' => 'Commercial Health Data Analysis for Marketing Purposes',
            'technical_summary' => 'This study will collect detailed health records, including diagnoses, prescription histories, demographic information, and genetic data, from a large sample of current and former smokers. Personal identifiers will be retained to allow for in-depth profiling and follow-up with individuals who meet the criteria for “smoking resilience.” By using advanced data mining and machine learning, the analysis will identify correlations between health markers, genetics, and reduced susceptibility to smoking-related illnesses. Findings will support targeted marketing campaigns for tobacco products and help to refine tobacco advertising strategies based on user profiles.',
            'other_approval_committees' => 'N/A',
            'start_date' => '2024-06-01',
            'end_date' => '2025-12-31',
        ]);

        $proj->setState(State::STATE_PROJECT_COMPLETED);

        ProjectHasCustodian::create([
            'project_id' => $proj->id,
            'custodian_id' => Custodian::first()->id,
            'approved' => true
        ]);

        ProjectHasOrganisation::create([
            'project_id' => $proj->id,
            'organisation_id' => $org3->id,
        ]);

        $orgHDR = Organisation::create([
            'organisation_name' => 'Health Data Research UK',
            'address_1' => '215 Euston Road',
            'address_2' => '',
            'town' => 'London',
            'county' => '',
            'country' => 'United Kingdom',
            'postcode' => 'NW1 2BE',
            'lead_applicant_organisation_name' => 'Dr Junaid Azmat Bajwa',
            'lead_applicant_email' => 'organisation.owner@hdruk.ac.uk',
            'password' => '$2y$12$g.LfOEaJZqjcDyZ51PwGxuKT9ceoAcHE8h6YmQXc5ZKY1a5wyGjPW', // Ask LS "Flood********"
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => 'Dr Junaid Azmat Bajwa',
            'funders_and_sponsors' => 'UKRI, NIHR, BHF, CRUK, ESRC',
            'sub_license_arrangements' => '...',
            'verified' => true,
            'dsptk_ods_code' => '',
            'iso_27001_certified' => true,
            'ce_certified' => true,
            'ce_plus_certified' => false,
            'companies_house_no' => '10887014',
            'sector_id' => 5, // Charity/Non-profit
            'ror_id' => '04rtjaj74',
            'smb_status' => true,
            'organisation_size' => 2,
            'website' => 'https://www.hdruk.ac.uk/',
        ]);


        // --------------------------------------------------------------------------------
        // End
        // --------------------------------------------------------------------------------

        // --------------------------------------------------------------------------------
        // Org level admin users
        // --------------------------------------------------------------------------------
        $org1Users = [
            [
                'first_name' => 'Organisation',
                'last_name' => 'Owner',
                'email' => 'organisation.owner@healthdataorganisation.com',
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org1->id, // Needed because this is an org admin
                'keycloak_id' => '0a12bc97-87b3-4f10-bb95-a2d8e65752f8', // Dragons ahead - needs to map 1:1 with KC users
                'is_sro' => 1,
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin.user@healthdataorganisation.com',
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org1->id, // Needed because this is an org admin
                'keycloak_id' => 'f636e859-b1a2-4e2d-ad0c-4688989b1f5c', // Dragons ahead - needs to map 1:1 with KC users
            ],
            [
                'first_name' => 'Delegate',
                'last_name' => 'Sponsor',
                'email' => 'delegate.sponsor@healthdataorganisation.com',
                'is_delegate' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org1->id, // Needed because this is an org admin
                'keycloak_id' => '47991933-3cdb-4519-8d3d-e7bb76fdca27', // Dragons ahead - needs to map 1:1 with KC users
            ],
        ];

        $org2Users = [
            [
                'first_name' => 'Organisation',
                'last_name' => 'Owner',
                'email' => 'organisation.owner@tandyenergyltd.com',
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org2->id, // Needed because this is an org admin
                'keycloak_id' => 'dd4eaa64-cea4-4f2d-847c-fba13a04bbb2', // Dragons ahead - needs to map 1:1 with KC users
                'is_sro' => 1,
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin.user@tandyenergyltd.com',
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org2->id, // Needed because this is an org admin
                'keycloak_id' => '18184ade-39f5-40ff-b2a4-e049703af754', // Dragons ahead - needs to map 1:1 with KC users
            ],
            [
                'first_name' => 'Delegate',
                'last_name' => 'Sponsor',
                'email' => 'delegate.sponsor@tandyenergyltd.com',
                'is_delegate' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org2->id, // Needed because this is an org admin
                'keycloak_id' => '6a09ff56-b655-4e28-805e-50de8789bd66', // Dragons ahead - needs to map 1:1 with KC users
            ],
        ];

        $org3Users = [
            [
                'first_name' => 'Tabacco',
                'last_name' => 'Frank',
                'email' => 'tobacco.frank@tobaccoeultd.com',
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org3->id, // Needed because this is an org admin
                'keycloak_id' => 'a8f73e81-f46d-4b3a-844c-ce05ce39c9f0', // Dragons ahead - needs to map 1:1 with KC users
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin.user@tobaccoeultd.com',
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org3->id, // Needed because this is an org admin
                'keycloak_id' => '952172dd-c5bf-4e84-ab15-51ef271e6985', // Dragons ahead - needs to map 1:1 with KC users
            ],
            [
                'first_name' => 'Delegate',
                'last_name' => 'Sponsor',
                'email' => 'delegate.sponsor@tobaccoeultd.com',
                'is_delegate' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $org3->id, // Needed because this is an org admin
                'keycloak_id' => 'd80e516b-f729-43cb-b6bb-936793f3f199', // Dragons ahead - needs to map 1:1 with KC users
            ],
        ];

        $this->createUsers($org1Users);
        $this->createUsers($org2Users);
        $this->createUsers($org3Users);

        // Here, need to add these to Keycloak - somehow...

        // --------------------------------------------------------------------------------
        // End
        // --------------------------------------------------------------------------------

        // --------------------------------------------------------------------------------
        // Org level research users
        // --------------------------------------------------------------------------------

        $org1Researchers = [
            [
                'first_name' => 'Dan',
                'last_name' => 'Ackroyd',
                'email' => 'dan.ackroyd@ghostbusters.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => 'aad586a6-724a-44f0-a270-8e558c5bdf7f',
                'projects' => [
                    $org1Proj1->id,
                    $org1Proj2->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '123 Road name',
                    'address_2' => '',
                    'town' => 'Springfield',
                    'county' => 'Illinois',
                    'country' => 'USA',
                    'postcode' => '62629',
                    'dob' => '1962-01-01',
                    'idvt_result' => 1,
                    'idvt_result_perc' => 100.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => Carbon::now(),
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org1->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Principal Investigator (PI)',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                    [
                        'organisation_id' => $org2->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(10)->toDateString(),
                        'to' => Carbon::now()->subYears(6)->toDateString(),
                        'department' => 'Research',
                        'role' => 'Data Analyst',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                ],
                'custodian_approvals' => [
                    1,
                ],
            ],
            [
                'first_name' => 'Sigourney',
                'last_name' => 'Weaver',
                'email' => 'sigourney.weaver@ghostbusters.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => '303d179a-ccdb-49fa-9bda-276840b07cfa',
                'projects' => [
                    $org1Proj1->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '123 Road name',
                    'address_2' => '',
                    'town' => 'Birmingham',
                    'county' => 'West Midlands',
                    'country' => 'UK',
                    'postcode' => 'B1 8TY',
                    'dob' => '1971-10-01',
                    'idvt_result' => 0,
                    'idvt_result_perc' => 0.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => null,
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org1->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Postdoc',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ]
                ],
            ],
            [
                'first_name' => 'Bill',
                'last_name' => 'Murray',
                'email' => 'bill.murray@ghostbusters.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => '06dbad18-3356-4181-8dad-1ddcf4718b5a',
                'projects' => [
                    $org1Proj2->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '123 Road name',
                    'address_2' => '',
                    'town' => 'Glastonbury',
                    'county' => 'Somerset',
                    'country' => 'UK',
                    'postcode' => 'BA6 9IT',
                    'dob' => '1943-08-12',
                    'idvt_result' => 1,
                    'idvt_result_perc' => 78.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => Carbon::now(),
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org1->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Market Research and Analysis',
                        'role' => 'Data Engineer',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ]
                ],
            ],
            [
                'first_name' => 'Annie',
                'last_name' => 'Potts',
                'email' => 'annie.potts@ghostbusters.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => '6b9af72e-9d05-4df2-ad38-25ba082d9cfc',
                'projects' => [
                    $org1Proj2->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '5 North',
                    'address_2' => 'Xuanxidajie',
                    'town' => 'Xichengqu Beijing',
                    'county' => 'Somerset',
                    'country' => 'CHN',
                    'postcode' => '100053',
                    'dob' => '1964-12-12',
                    'idvt_result' => 1,
                    'idvt_result_perc' => 78.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => Carbon::now(),
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org1->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Supply Chain and Logistics',
                        'role' => 'Student',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ]
                ],
            ],
        ];

        $org2Researchers = [
            [
                'first_name' => 'Harold',
                'last_name' => 'Ramis',
                'email' => 'harold.ramis@ghostbusters.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => '760da050-5b3f-47b3-976c-0d42ea533f94',
                'projects' => [
                    $org2Proj1->id,
                    $org1Proj1->id,
                    $org1Proj2->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '49 Featherstone Street',
                    'address_2' => '',
                    'town' => 'LONDON',
                    'county' => '',
                    'country' => 'UK',
                    'postcode' => 'EC1Y 8SY',
                    'dob' => '1982-06-20',
                    'idvt_result' => 1,
                    'idvt_result_perc' => 94.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => Carbon::now(),
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org2->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(20)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Principal Investigator (PI)',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                ],
            ],
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Runyon',
                'email' => 'jennifer.runyon@ghostbusters.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => 'f770c1cc-9935-4d52-ad76-19e33ca526b1',
                'projects' => [
                    $org2Proj1->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '122 Some Street',
                    'address_2' => '',
                    'town' => 'LONDON',
                    'county' => '',
                    'country' => 'UK',
                    'postcode' => 'NW1 2AY',
                    'dob' => '1984-02-25',
                    'idvt_result' => 1,
                    'idvt_result_perc' => 87.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => Carbon::now(),
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org2->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(10)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Research Fellow',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                ],
            ],
        ];

        $org3Researchers = [
            [
                'first_name' => 'Tobacco',
                'last_name' => 'John',
                'email' => 'tobacco.john@dodgydomain.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => '5fc28eb7-71fc-495c-9d53-1ec7fa9954bd',
                'projects' => [
                    $org2Proj1->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '12 Fake Street',
                    'address_2' => '',
                    'town' => 'LONDON',
                    'county' => '',
                    'country' => 'UK',
                    'postcode' => 'SW3 6JT',
                    'dob' => '1989-02-05',
                    'idvt_result' => 1,
                    'idvt_result_perc' => 87.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => Carbon::now(),
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org3->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Lobbyist',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                ],
            ],
            [
                'first_name' => 'Tobacco',
                'last_name' => 'Dave',
                'email' => 'tobacco.dave@dodgydomain.com',
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => 'de17935a-a79e-4b47-8d92-8041901dec15',
                'projects' => [
                    $org1Proj1->id,
                ],
                'identity' => [
                    'selfie_path' => '/path/to/non/existent/selfie/',
                    'passport_path' => '/path/to/non/existent/passport/',
                    'drivers_license_path' => '/path/to/non/existent/license/',
                    'address_1' => '13 Fake Street',
                    'address_2' => '',
                    'town' => 'LONDON',
                    'county' => '',
                    'country' => 'UK',
                    'postcode' => 'SW3 6JT',
                    'dob' => '1981-04-05',
                    'idvt_result' => 0,
                    'idvt_result_perc' => 0.0,
                    'idvt_errors' => null,
                    'idvt_completed_at' => null,
                ],
                'affiliations' => [
                    [
                        'organisation_id' => $org3->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Lobbying',
                        'role' => 'Lobbyist',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                    [
                        'organisation_id' => $org3->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Research Scientist',
                        'email' => fake()->email(),
                        'ror' => $this->generateRorID(),
                        'registry_id' => -1,
                    ],
                ],
            ],
        ];

        $this->createUsers($org1Researchers);
        $this->createUsers($org2Researchers);
        $this->createUsers($org3Researchers);

        // --------------------------------------------------------------------------------
        // Create Registry ledger for above users
        // --------------------------------------------------------------------------------
        $this->createUserRegistry($org1Researchers);
        $this->createUserRegistry($org2Researchers);
        $this->createUserRegistry($org3Researchers, false);

        // --------------------------------------------------------------------------------
        // Create Identities for the above users
        // --------------------------------------------------------------------------------
        $this->createIdentities($org1Researchers);
        $this->createIdentities($org2Researchers);
        $this->createIdentities($org3Researchers);

        // --------------------------------------------------------------------------------
        // Create Affiliations for the above users
        // --------------------------------------------------------------------------------
        $this->createAffiliations($org1Researchers);
        $this->createAffiliations($org2Researchers);
        $this->createAffiliations($org3Researchers);

        // --------------------------------------------------------------------------------
        // Link Researchers to projects
        // --------------------------------------------------------------------------------
        $this->linkUsersToProjects($org1Researchers);
        $this->linkUsersToProjects($org2Researchers);
        $this->linkUsersToProjects($org3Researchers);


        // --------------------------------------------------------------------------------
        // Create unclaimed users from custodian_admins that have been created
        // --------------------------------------------------------------------------------
        $this->createUnclaimedUsers();

        // --------------------------------------------------------------------------------
        // End
        // --------------------------------------------------------------------------------
    }

    private function createIdentities(array &$input): void
    {
        foreach ($input as $u) {
            $user = User::where('email', $u['email'])->first();

            Identity::create([
                'registry_id' =>            $user->registry_id,
                'selfie_path' =>            $u['identity']['selfie_path'],
                'passport_path' =>          $u['identity']['passport_path'],
                'drivers_license_path' =>   $u['identity']['drivers_license_path'],
                'address_1' =>              $u['identity']['address_1'],
                'address_2' =>              $u['identity']['address_2'],
                'town' =>                   $u['identity']['town'],
                'county' =>                 $u['identity']['county'],
                'country' =>                $u['identity']['country'],
                'postcode' =>               $u['identity']['postcode'],
                'dob' =>                    $u['identity']['dob'],
                'idvt_result' =>            $u['identity']['idvt_result'],
                'idvt_result_perc' =>       $u['identity']['idvt_result_perc'],
                'idvt_errors' =>            $u['identity']['idvt_errors'],
                'idvt_completed_at' =>      $u['identity']['idvt_completed_at'],
            ]);
        }

        unset($input);
    }

    private function createAffiliations(array &$input): void
    {
        foreach ($input as $u) {
            $user = User::where('email', $u['email'])->first();

            if (!isset($u['affiliations'])) {
                continue;
            }

            foreach ($u['affiliations'] as $e) {
                $aff = Affiliation::create([
                    'organisation_id' => $e['organisation_id'],
                    'member_id' => $e['member_id'],
                    'relationship' => $e['relationship'],
                    'from' => $e['from'],
                    'to' => $e['to'],
                    'department' => $e['department'],
                    'role' => $e['role'],
                    'email' => $e['email'],
                    'ror' => $e['ror'],
                    'registry_id' => $user->registry_id,
                ]);

                RegistryHasAffiliation::create([
                    'affiliation_id' => $aff->id,
                    'registry_id' => $user->registry_id,
                ]);

            }
        }

        unset($input);
    }

    private function linkUsersToProjects(array &$input): void
    {
        foreach ($input as $u) {
            $user = User::where('email', $u['email'])->with(['registry.affiliations'])->first();

            foreach ($u['projects'] as $p) {
                ProjectHasUser::create([
                    'project_id' => $p,
                    'user_digital_ident' => Registry::where('id', $user->registry_id)->first()->digi_ident,
                    'project_role_id' => 7,
                    'affiliation_id' => $user->registry->affiliations[0]->id
                ]);
            }
        }

        unset($input);
    }

    private function createUsers(array &$input): void
    {
        foreach ($input as $u) {
            $user = User::create([
                'first_name' =>         $u['first_name'],
                'last_name' =>          $u['last_name'],
                'email' =>              $u['email'],
                'is_org_admin' =>       isset($u['is_org_admin']) ? $u['is_org_admin'] : 0,
                'is_delegate' =>        isset($u['is_delegate']) ? $u['is_delegate'] : 0,
                'user_group' =>         $u['user_group'],
                'organisation_id' =>    isset($u['organisation_id']) ? $u['organisation_id'] : 0,
                'keycloak_id' =>        $u['keycloak_id'],
            ]);
        }

        unset($input);
    }

    private function createUserRegistry(array &$input, bool $legit = true): void
    {
        foreach ($input as $u) {
            $reg = Registry::create([
                'dl_ident' =>       '',
                'pp_ident' =>       '',
                'digi_ident' =>     RMC::generateDigitalIdentifierForRegistry(),
                'verified' =>       0,
            ]);

            $user = User::where('email', $u['email'])->first();

            $user->update([
                'registry_id' => $reg->id,
            ]);

            if (!in_array(env('APP_ENV'), ['testing', 'ci'])) {
                if ($user) {
                    Keycloak::updateSoursdDigitalIdentifier($user);
                }
            }

            if ($user->user_group !== RMC::KC_GROUP_USERS) {
                continue;
            }

            $educations = [];

            // Make up some Education entries for these users
            if ($legit) {
                $educations = [
                    [
                        'title' => 'Infectious Disease \'Omics',
                        'from' => Carbon::now()->subYears(10)->toDateString(),
                        'to' => Carbon::now()->subYears(6)->toDateString(),
                        'institute_name' => 'London School of Hygiene & Tropical Medicine',
                        'institute_address' => 'Keppel Street, London, WC1E 7HT',
                        'institute_identifier' => '00a0jsq62', // ROR
                        'source' => 'user',
                        'registry_id' => $reg->id,
                    ],
                    [
                        'title' => 'MSc Health Data Science',
                        'from' => Carbon::now()->subYears(5)->toDateString(),
                        'to' => Carbon::now()->subYears(4)->toDateString(),
                        'institute_name' => 'University of Exeter',
                        'institute_address' => 'Stocker Road, Exeter, Devon EX4 4SZ',
                        'institute_identifier' => '03yghzc09',
                        'source' => 'user',
                        'registry_id' => $reg->id,
                    ],
                ];
            } else {
                $educations = [
                    [
                        'title' => 'Lobbying and Manipulation tactics for Policy',
                        'from' => Carbon::now()->subYears(10)->toDateString(),
                        'to' => Carbon::now()->subYears(6)->toDateString(),
                        'institute_name' => 'London School of Lobbying',
                        'institute_address' => 'Fake Street, London, WC1E 7HT',
                        'institute_identifier' => '', // ROR
                        'source' => 'user',
                        'registry_id' => $reg->id,
                    ],
                ];
            }

            foreach ($educations as $edu) {
                $ed = Education::create([
                    'title' => $edu['title'],
                    'from' => $edu['from'],
                    'to' => $edu['to'],
                    'institute_name' => $edu['institute_name'],
                    'institute_address' => $edu['institute_address'],
                    'institute_identifier' => $edu['institute_identifier'],
                    'source' => $edu['source'],
                    'registry_id' => $edu['registry_id'],
                ]);
            }

            $trainings = [];

            if ($legit) {
                // Make up some training entries for these users
                $trainings = [
                    [
                        'provider' => 'UK Data Service',
                        'awarded_at' => Carbon::now()->subYears(2)->toDateString(),
                        'expires_at' => Carbon::now()->addYears(3)->toDateString(),
                        'expires_in_years' => 5,
                        'training_name' => 'Safe Researcher Training',
                    ],
                    [
                        'provider' => 'Medical Research Council (MRC)',
                        'awarded_at' => Carbon::now()->subYears(2)->toDateString(),
                        'expires_at' => Carbon::now()->addYears(3)->toDateString(),
                        'expires_in_years' => 5,
                        'training_name' => 'Research, GDPR, and Confidentiality',
                    ],
                ];
            } else {
                $trainings = [
                    [
                        'provider' => 'Office of National Statistics (ONS)',
                        'awarded_at' => Carbon::now()->subYears(10)->toDateString(),
                        'expires_at' => Carbon::now()->subYears(5)->toDateString(),
                        'expires_in_years' => 2,
                        'training_name' => 'Safe Researcher Training',
                    ],
                ];
            }

            foreach ($trainings as $tr) {
                $training = Training::create([
                    'provider' => $tr['provider'],
                    'awarded_at' => $tr['awarded_at'],
                    'expires_at' => $tr['expires_at'],
                    'expires_in_years' => $tr['expires_in_years'],
                    'training_name' => $tr['training_name'],
                ]);

                RegistryHasTraining::create([
                    'registry_id' => $reg->id,
                    'training_id' => $training->id,
                ]);
            }

            if (isset($u['custodian_approvals'])) {
                foreach ($u['custodian_approvals'] as $approval) {
                    $uhca = UserHasCustodianApproval::create([
                        'user_id' => $user->id,
                        'custodian_id' => $approval,
                    ]);
                }
            }
        }

        unset($input);
        unset($trainings);
        unset($educations);
        unset($reg);
        unset($user);
    }

    private function createUnclaimedUsers(): void
    {
        $custodianAdmin = CustodianUser::all();
        foreach ($custodianAdmin as $ca) {
            RMC::createUnclaimedUser([
                'firstname' => $ca['first_name'],
                'lastname' => $ca['last_name'],
                'email' => $ca['email'],
                'user_group' => 'CUSTODIANS',
                'custodian_user_id' => $ca->id
            ]);
        }

        unset($custodianAdmin);
    }

    private function addRandomUsersToProject(int $projectId, int $nUsers = null): void
    {
        $nUsers = $nUsers ?? random_int(1, 10);
        $users = User::whereNotNull('registry_id')->inRandomOrder()->limit($nUsers)->get();
        foreach ($users as $researcher) {
            $ident = Registry::where('id', $researcher->registry_id)->first()->digi_ident;
            $roleId = ProjectRole::inRandomOrder()->first()->id;
            ProjectHasUser::create(
                [
                    'project_id' => $projectId,
                    'user_digital_ident' => $ident,
                    'project_role_id' => $roleId
                ]
            );
        }

        unset($users);
    }
}
