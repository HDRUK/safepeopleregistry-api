<?php

namespace Database\Seeders;

use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\ProjectHasUser;
use App\Models\ValidationCheck;
use Illuminate\Database\Seeder;

class ValidationCheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $custodians = Custodian::all();

        foreach (ProjectHasUser::defaultValidationChecks() as $check) {
            ValidationCheck::updateOrCreate(
                [
                    'name' => $check['name'],
                    'applies_to' => ProjectHasUser::class,
                    'custodian_id' => null,
                ],
                [
                    'description' => $check['description'],
                    'enabled' => 1
                ]
            );

            foreach ($custodians as $custodian) {
                ValidationCheck::updateOrCreate(
                    [
                        'name' => $check['name'],
                        'applies_to' => ProjectHasUser::class,
                        'custodian_id' => $custodian->id,
                    ],
                    [
                        'description' => $check['description'],
                        'enabled' => 1
                    ]
                );
            }
        }

        foreach (Organisation::defaultValidationChecks() as $check) {
            ValidationCheck::updateOrCreate(
                [
                    'name' => $check['name'],
                    'applies_to' => Organisation::class,
                    'custodian_id' => null,
                ],
                [
                    'description' => $check['description'],
                    'enabled' => 1
                ]
            );

            foreach ($custodians as $custodian) {
                ValidationCheck::updateOrCreate(
                    [
                        'name' => $check['name'],
                        'applies_to' => Organisation::class,
                        'custodian_id' => $custodian->id,
                    ],
                    [
                        'description' => $check['description'],
                        'enabled' => 1
                    ]
                );
            }
        }
    }
}
