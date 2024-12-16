<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::truncate();

        $departments = [
            'Health-Focused Departments' => [
                'Epidemiology and Public Health',
                'Clinical Research',
                'Biostatistics and Data Science',
                'Molecular Biology and Genetics',
                'Biomedical Engineering',
                'Health Informatics',
                'Immunology and Infectious Diseases',
                'Pharmacology and Drug Development',
                'Environmental and Occupational Health',
                'Health Policy and Systems Research',
            ],
            'Industry-Focused Departments' => [
                'Research and Development (R&D)',
                'Quality Assurance and Control',
                'Regulatory Affairs',
                'Manufacturing and Process Development',
                'Supply Chain and Logistics',
                'Market Research and Analysis',
                'Industrial Safety and Compliance',
            ],
            'Cross-Cutting Departments' => [
                'Innovation and Technology Transfer',
                'Bioinformatics and Computational Biology',
                'Education and Training',
                'Finance and Grants Management',
                'Communications and Public Engagement',
                'Ethics and Compliance',
            ],
        ];

        foreach ($departments as $category => $depts) {
            foreach ($depts as $value) {
                Department::create([
                    'name' => $value,
                    'category' => $category,
                ]);
            }
        }
    }
}
