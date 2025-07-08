<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\ActionPendingNotification;

class UpdateActionNotifications implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $chunkSize = 50;

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
        $this->processUsers(User::GROUP_USERS, User::query());
        $this->processUsers(User::GROUP_ORGANISATIONS, User::where("is_org_admin", 1));
        $this->processUsers(User::GROUP_CUSTODIANS, User::query());
    }

    private function processUsers(string $group, $query): void
    {
        $query->where("user_group", $group)
            ->chunk($this->chunkSize, function ($users) use ($group) {
                foreach ($users as $user) {
                    $entityType = $this->getEntityType($group);
                    $entityId = $this->getEntityId($user, $group);

                    if ($entityType === null || $entityId === null) {
                        Log::warning("Skipping user {$user->id} due to missing entity type or ID.");
                        continue;
                    }
                    
                    $this->processNotifications($user, $group);
                    // trying this as it could be causing unboard memory growth
                    // as in the called function, we do both:
                    // $user->notify(new ActionPendingNotification($group, $incompleteActions));
                    // and
                    // $user->notifications()->where...blah
                    //
                    // both aren't light touches against eloquent, and possible
                    // candidates for raw queries, if _this_ works.
                    unset($user);
                }
            });
    }

    private function processNotifications(User $user, string $group): void
    {
        $entityType = $this->getEntityType($group);
        $entityId = $this->getEntityId($user, $group);
        if ($entityType === null || $entityId === null) {
            Log::warning("Skipping notifications for user {$user->id} due to missing entity type or ID.");
            return;
        }

        $incompleteActions = ActionLog::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->whereNull('completed_at')
            ->get();

        $existingNotification = $user->notifications()
            ->where('type', ActionPendingNotification::class)
            ->first();

        if ($incompleteActions->isNotEmpty()) {
            $existingNotification?->delete();
            $user->notify(new ActionPendingNotification($group, $incompleteActions));
        } elseif ($existingNotification) {
            $existingNotification->update(['read_at' => Carbon::now()]);
        }
    }

    private function getEntityType(string $group): ?string
    {
        try {
            return match ($group) {
                User::GROUP_USERS => User::class,
                User::GROUP_ORGANISATIONS => Organisation::class,
                User::GROUP_CUSTODIANS => Custodian::class,
                default => throw new InvalidArgumentException("Invalid user group: {$group}"),
            };
        } catch (Exception $e) {
            Log::error("Failed to get entity type for group {$group}: " . $e->getMessage());
            return null;
        }
    }

    private function getEntityId(User $user, string $group): ?int
    {
        try {
            $return = match ($group) {
                User::GROUP_USERS => $user->id,
                User::GROUP_ORGANISATIONS => $user->organisation_id,
                User::GROUP_CUSTODIANS => $user->custodian_id ? $user->custodian_user->custodian_id : null,
                default => throw new InvalidArgumentException("Invalid user group: {$group}"),
            };

            if (is_null($return)) {
                Log::error("Failed to get entity ID for user {$user->id} and group {$group}: " . $e->getMessage());
                return null;
            }

            return $return;
        } catch (Exception $e) {
            Log::error("Failed to get entity ID for user {$user->id} and group {$group}: " . $e->getMessage());
            return null;
        }
    }
}
