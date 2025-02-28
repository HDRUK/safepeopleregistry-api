<?php

namespace Database\Seeders;

use App\Models\History;
use App\Models\RegistryHasHistory;
use Illuminate\Database\Seeder;

class HistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        History::truncate();

        History::factory(10)->create();

        $histories = History::all();
        foreach ($histories as $h) {
            RegistryHasHistory::create([
                'registry_id' => 1,
                'history_id' => $h->id,
            ]);
        }
    }
}
