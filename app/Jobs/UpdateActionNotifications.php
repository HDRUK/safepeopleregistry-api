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
                $this->processUserNotifications($user);
            }
        });
    }
    private function processUserNotifications(User $user): void
    {
        $incompleteActions = ActionLog::where('entity_type', User::class)
            ->where('entity_id', $user->id)
            ->whereNull('completed_at')
            ->get();

        $existingNotification = $user->notifications()
            ->where('type', ActionPendingNotification::class)
            ->first();

        if ($incompleteActions->isNotEmpty()) {
            if ($existingNotification) {
                $existingNotification->delete();
            }
            $user->notify(new ActionPendingNotification($incompleteActions));
        } elseif ($existingNotification) {
            $existingNotification->update(['read_at' => now()]);
        }
    }
}
