<?php

namespace Database\Seeders;

use App\Models\EntityModelType;
use Illuminate\Database\Seeder;

class EntityModelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EntityModelType::truncate();

        foreach (EntityModelType::ENTITY_TYPES as $type) {
            EntityModelType::create([
                'name' => $type,
            ]);
        }
    }
}
