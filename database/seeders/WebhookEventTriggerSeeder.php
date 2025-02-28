<?php

namespace Database\Seeders;

use App\Models\WebhookEventTrigger;
use Illuminate\Database\Seeder;

class WebhookEventTriggerSeeder extends Seeder
{
    public function run(): void
    {
        WebhookEventTrigger::truncate();

        $triggers = [
            [
                'name' => 'user-left-project',
                'description' => 'Event trigger for when a user is removed or leaves a Project',
            ],
            [
                'name' => 'user-joined-project',
                'description' => 'Event trigger for when a user joins a Project',
            ],
        ];

        foreach ($triggers as $t) {
            WebhookEventTrigger::create([
                'name' => $t['name'],
                'description' => $t['description'],
            ]);
        }
    }
}
