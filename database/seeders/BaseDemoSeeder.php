<?php

namespace Database\Seeders;

use Str;
use App\Models\Project;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class BaseDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Probably need a migrate fresh here to fully clear
        // the database before hand.

        $this->call([
            SystemConfigSeeder::class,
            ProjectRoleSeeder::class,
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
            'country' => 'UNITED KINGDOM',
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
            'companies_house_no' => '09349495',
        ]);

        Project::create([
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
            'affiliate_id' => $org1->id,
        ]);

        Project::create([
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
            'affiliate_id' => $org1->id,
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
            'companies_house_no' => '15765271',
        ]);

        Project::create([
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
            'affiliate_id' => $org2->id,
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
            'companies_house_no' => '07563555',
        ]);

        Project::create([
            'unique_id' => Str::random(20),
            'title' => 'Analyzing Health Data to Identify Resilient Smokers: A Study on Tolerance to Tobacco Use',
            'lay_summary' => 'This study aims to explore patterns in health data to identify factors that may indicate resilience to smoking-related illnesses, focusing on smokers who exhibit fewer negative health outcomes. By collecting and analyzing detailed health records, we aim to create profiles of individuals who can tolerate smoking with minimal health consequences, potentially informing tailored marketing strategies for tobacco products.',
            'public_benefit' => 'The research findings could benefit society by helping to identify the physiological and lifestyle factors that make some individuals less susceptible to smoking-related diseases. By understanding these factors, we could inform tobacco consumers about "safer" smoking habits and promote responsible use among those less affected by health risks.',
            'request_category_type' => 'Commercial Health Data Analysis for Marketing Purposes',
            'technical_summary' => 'This study will collect detailed health records, including diagnoses, prescription histories, demographic information, and genetic data, from a large sample of current and former smokers. Personal identifiers will be retained to allow for in-depth profiling and follow-up with individuals who meet the criteria for “smoking resilience.” By using advanced data mining and machine learning, the analysis will identify correlations between health markers, genetics, and reduced susceptibility to smoking-related illnesses. Findings will support targeted marketing campaigns for tobacco products and help to refine tobacco advertising strategies based on user profiles.',
            'other_approval_committees' => 'N/A',
            'start_date' => '2025-06-01',
            'end_date' => '2025-12-31',
            'affiliate_id' => $org3->id,
        ]);

        // --------------------------------------------------------------------------------
        // End
        // --------------------------------------------------------------------------------
    }
}
