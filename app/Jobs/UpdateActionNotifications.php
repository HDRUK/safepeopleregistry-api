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
        $users = User::all();

        foreach ($users as $user) {
            $pendingActions = [
                'profile_completed' => !ActionLog::where('user_id', $user->id)->where('action', 'profile_completed')->exists(),
                'affiliations_updated' => !ActionLog::where('user_id', $user->id)->where('action', 'affiliations_updated')->exists(),
            ];

            $hasPending = in_array(true, $pendingActions);

            if ($hasPending) {
                $user->notify(new ActionPendingNotification());
            } else {
                $user->unreadNotifications()
                     ->where('type', ActionPendingNotification::class)
                     ->markAsRead();
            }
        }
    }
}
