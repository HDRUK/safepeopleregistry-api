<?php

namespace App\Observers;

use App\Models\CustodianUser;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;

class CustodianUserObserver
{
    /**
     * Handle the CustodianUser "created" event.
     */
    public function created(CustodianUser $custodianUser): void
    {
        $this->updateActionLog($custodianUser);
    }

    /**
     * Handle the CustodianUser "updated" event.
     */
    public function updated(CustodianUser $custodianUser): void
    {
        $this->updateActionLog($custodianUser);
    }

    /**
     * Handle the CustodianUser "deleted" event.
     */
    public function deleted(CustodianUser $custodianUser): void
    {
        $this->updateActionLog($custodianUser, true);
    }

    /**
     * Handle the CustodianUser "restored" event.
     */
    public function restored(CustodianUser $custodianUser): void
    {
        $this->updateActionLog($custodianUser);
    }

    /**
     * Handle the CustodianUser "force deleted" event.
     */
    public function forceDeleted(CustodianUser $custodianUser): void
    {
        $this->updateActionLog($custodianUser, true);
    }

    private function updateActionLog(CustodianUser $custodianUser, $isDeleting = false)
    {
        $custodian = $custodianUser->custodian;
        $allUsers =  $custodian->custodianUsers();

        $hasCustodianUsers = $allUsers->when($isDeleting, function ($users) use ($custodianUser) {
            return $users->where('id', '!=', $custodianUser->id);
        })->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $custodian->id,
                'entity_type' => Custodian::class,
                'action' => Custodian::ACTION_ADD_USERS,
            ],
            ['completed_at' => $hasCustodianUsers ? Carbon::now() : null]
        );
    }
}
