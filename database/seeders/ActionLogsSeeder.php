<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\ActionLogs;

class ActionLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ActionLogs::truncate();
        Schema::enableForeignKeyConstraints();

        $users = User::all();

        $actions = ['Logged in', 'Updated profile', 'Changed password', 'Logged out'];

        $actionLogs = [];

        foreach ($users as $user) {
            foreach ($actions as $action) {
                $actionLogs[] = [
                    'user_id' => $user->id,
                    'action' => $action,
                    'completed_at' => null,
                ];
            }
        }

        ActionLogs::insert($actionLogs);
    }
}
