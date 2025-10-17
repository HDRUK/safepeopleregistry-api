<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
                'datasets' => json_encode(['https://healthdatagateway.org/en/dataset/1375']),
                'other_approval_committees' => NULL,
                'data_sensitivity_level' => fake()->randomElement(['De-Personalised', 'Personally Identifiable', 'Anonymous']),
                'legal_basis_for_data_article6' => '(b) processing is necessary for the performance of a contract to which the data subject is party or in order to take steps at the request of the data subject prior to entering into a contract;',
                'duty_of_confidentiality' => fake()->randomElement([0, 1]),
                'national_data_optout' => fake()->randomElement([0, 1]),
                'request_frequency' => fake()->randomElement(['ONE-OFF', 'RECURRING']),
                'dataset_linkage_description' => 'A NOVEL observational longiTudinal studY (NOVELTY) on patients with asthma and\/or COPD (Chronic Obstructive Pulmonary Disease) to describe patient characteristics, treatment patterns and the burden of illness over time and to identify phenotypes and endotypes.',
                'data_minimisation' => 'It is estimated that approximately 7,700 patients with suspected or primary diagnosis of asthma and 7,100 patients with suspected or primary diagnosis of COPD will be enrolled by a diverse set of physicians (e.g. primary care physicians, allergists, pulmonologists) from community and hospital outpatient settings within the countries targeted for NOVELTY.',
                'data_use_description' => 'The NOVELTY study is a multi-country, multicentre, observational, prospective, longitudinal cohort study which will include patients with a physician diagnosis, or suspected diagnosis, of asthma and/or COPD. Patients will undergo clinical assessments and receive standard medical care as determined by the treating physician. All patients enrolled in the NOVELTY study will be followed up yearly by their treating physician for a total duration of three years. In addition, patients are expected to be followed up remotely once every quarter.',
                'access_date' => now(),
                'access_type' => NULL,
                'data_privacy' => NULL,
                'research_outputs' => NULL,
                'data_assets' => NULL,
            ]);
        }

        $this->command?->info('Project details seeded successfully.');
    }
}
