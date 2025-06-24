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
            [
                'name' => 'user-accepted-read-request',
                'description' => 'Event trigger for when a user accepts a read request',
            ],
            [
                'name' => 'user-rejected-read-request',
                'description' => 'Event trigger for when a user rejects a read request',
            ],
            [
                'name' => 'organisation-left-project',
                'description' => 'Event trigger for when an organisation is removed or leaves a Project',
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
