<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ActionLog;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Custodian;
use App\Notifications\ActionPendingNotification;
use Carbon\Carbon;
use InvalidArgumentException;

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
                    $this->processNotifications($user, $group);
                }
            });
    }

    private function processNotifications(User $user, string $group): void
    {
        $entityType = $this->getEntityType($group);
        $entityId = $this->getEntityId($user, $group);

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

    private function getEntityType(string $group): string
    {
        return match ($group) {
            User::GROUP_USERS => User::class,
            User::GROUP_ORGANISATIONS => Organisation::class,
            User::GROUP_CUSTODIANS => Custodian::class,
            default => throw new InvalidArgumentException("Invalid user group: {$group}"),
        };
    }

    private function getEntityId(User $user, string $group): int
    {
        return match ($group) {
            User::GROUP_USERS => $user->id,
            User::GROUP_ORGANISATIONS => $user->organisation_id,
            User::GROUP_CUSTODIANS => $user->custodian_id ?? $user->custodian_user->custodian_id,
            default => throw new InvalidArgumentException("Invalid user group: {$group}"),
        };
    }

}
