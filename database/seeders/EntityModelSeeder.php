<?php

namespace Database\Seeders;

use App\Models\EntityModel;
use Illuminate\Database\Seeder;

class EntityModelSeeder extends Seeder
{
    // /**
    //  * Run the database seeds.
    //  */
    // public function run(): void
    // {
    //     EntityModel::truncate();

    //     foreach (config('entity_models.entities') as $ent) {
    //         foreach ($ent as $e) {
    //             EntityModel::create([
    //                 'name' => $e['name'],
    //                 'description' => $e['description'],
    //                 'entity_model_type_id' => $e['entity_model_type_id'],
    //                 'calls_file' => $e['calls_file'],
    //                 'file_path' => $e['file_path'],
    //                 'calls_operation' => $e['calls_operation'],
    //                 'operation' => $e['operation'],
    //             ]);
    //         }
    //     }
    // }
}
