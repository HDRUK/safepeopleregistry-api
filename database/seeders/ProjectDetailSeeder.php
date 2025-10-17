<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProjectDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ProjectDetail::truncate();
        Schema::enableForeignKeyConstraints();

        $projects = Project::all();
        foreach ($projects as $project) {
            ProjectDetail::create([
                'project_id' => $project->id,
                'datasets' => json_encode([
                    'https://doesnot.exist/datasets/1',
                    'https://doesnot.exist/datasets/2',
                    'https://doesnot.exist/datasets/3',
                ]),
                'other_approval_committees' => json_encode([
                    'Approval Committee 1',
                    'Approval Committee 2',
                    'Approval Committee 3',
                ]),
                'data_sensitivity_level' => fake()->randomElement(['De-Personalised', 'Personally Identifiable', 'Anonymous']),
                'legal_basis_for_data_article6' => '(b) processing is necessary for the performance of a contract to which the data subject is party or in order to take steps at the request of the data subject prior to entering into a contract;',
                'duty_of_confidentiality' => fake()->randomElement([0, 1]),
                'national_data_optout' => fake()->randomElement([0, 1]),
                'request_frequency' => fake()->randomElement(['ONE-OFF', 'RECURRING']),
                'dataset_linkage_description' => 'Patient records linked via NHS number with pseudonymized clinical outcome data from tertiary care facilities, cross-referenced against national mortality registry using deterministic matching algorithms.',
                'data_minimisation' => 'Limited to diagnosis codes, treatment dates, and age groups; names and full addresses excluded; only relevant comorbidities retained.',
                'data_use_description' => 'Data will be used to evaluate treatment efficacy and patient outcomes in cardiovascular disease management, identify risk factors for adverse events, and develop predictive models for clinical decision support systems.',
                'access_date' => now(),
                'access_type' => 1,
                'data_privacy' => 'Data stored in encrypted ISO 27001-compliant servers with role-based access controls; personally identifiable information pseudonymized using irreversible hashing; access logged and audited quarterly; compliant with GDPR and NHS Data Security and Protection Toolkit requirements.',
                'research_outputs' => '{"research_outputs": [ "https://mydomain.com/research1", "https://mydomain.com/research2"] }',
                'data_assets' => 'Our data assets are...',
            ]);
        }

        $this->command?->info('Project details seeded successfully.');
    }
}
