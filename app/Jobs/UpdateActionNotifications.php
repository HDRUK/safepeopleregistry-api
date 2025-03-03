<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ActionLog;
use App\Models\User;
use App\Notifications\ActionPendingNotification;

class UpdateActionNotifications implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                $query = ActionLog::where('entity_type', User::class)
                                  ->where('entity_id', $user->id);

                // Check if actions are missing
                $pendingActions = [
                    'profile_completed' => !$query->clone()
                                   ->where('action', 'profile_completed')
                                   ->exists(),
                    'affiliations_updated' => !$query->clone()
                                   ->where('action', 'affiliations_updated')
                                   ->exists(),
                ];

                $hasPending = in_array(true, $pendingActions, true);

                $existingNotification = $user->notifications()
                                         ->where('type', ActionPendingNotification::class)
                                         ->first();

                if ($hasPending) {
                    if ($existingNotification) {
                        $existingNotification->update([
                            'read_at' => null,
                            'updated_at' => now(),
                        ]);
                    } else {
                        $user->notify(new ActionPendingNotification());
                    }
                } else {
                    if ($existingNotification) {
                        $existingNotification->update([
                            'read_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
