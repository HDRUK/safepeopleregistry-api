<?php

namespace App\Observers;

use App\Models\RegistryHasTraining;
use App\Models\ActionLog;
use App\Models\User;
use Carbon\Carbon;

class RegistryHasTrainingObserver
{
    /**
     * Handle the RegistryHasTraining "created" event.
     */
    public function created(RegistryHasTraining $registryHasTraining): void
    {
        $this->updateActionLog($registryHasTraining);
    }

    /**
     * Handle the RegistryHasTraining "updated" event.
     */
    public function updated(RegistryHasTraining $registryHasTraining): void
    {
        $this->updateActionLog($registryHasTraining);
    }

    /**
     * Handle the RegistryHasTraining "deleted" event.
     */
    public function deleted(RegistryHasTraining $registryHasTraining): void
    {
        $this->updateActionLog($registryHasTraining);
    }

    /**
     * Handle the RegistryHasTraining "restored" event.
     */
    public function restored(RegistryHasTraining $registryHasTraining): void
    {
        $this->updateActionLog($registryHasTraining);
    }

    /**
     * Handle the RegistryHasTraining "force deleted" event.
     */
    public function forceDeleted(RegistryHasTraining $registryHasTraining): void
    {
        $this->updateActionLog($registryHasTraining);
    }

    /**
     * Updates the action log based on the user's Trainings.
     */
    private function updateActionLog(RegistryHasTraining $registryHasTraining): void
    {
        $registryId = $registryHasTraining->registry_id;
        $user = User::where('registry_id', $registryId)->first();
        if (!$user) {
            return;
        }
        $hasTrainings = RegistryHasTraining::where('registry_id', $registryId)->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'action' => User::ACTION_TRAINING_COMPLETE,
            ],
            ['completed_at' => $hasTrainings ? Carbon::now() : null]
        );
    }

}
