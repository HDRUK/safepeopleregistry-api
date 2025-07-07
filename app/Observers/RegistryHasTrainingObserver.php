<?php

namespace App\Observers;

use App\Models\RegistryHasTraining;
use App\Models\ActionLog;
use App\Models\Registry;
use App\Models\User;
use Carbon\Carbon;

class RegistryHasTrainingObserver
{
<<<<<<< HEAD

=======
<<<<<<< HEAD
=======

>>>>>>> efb9393 (add training check observer)
>>>>>>> 24c74bb (add training check observer)
    public function created(RegistryHasTraining $model): void
    {
        $this->updateTrainingActionLog($model);
    }

    public function deleted(RegistryHasTraining $model): void
    {
        $this->updateTrainingActionLog($model, true);
    }

    private function updateTrainingActionLog(RegistryHasTraining $model, bool $isDeleting = false): void
    {
        $registry = Registry::with('user')->find($model->registry_id);

<<<<<<< HEAD
        if (!$registry || !$registry->user) {
            return; // Defensive check
        }

        $user = $registry->user;

=======
<<<<<<< HEAD
        $user = $registry->user;
        if (!$registry || !$user) {
            return;
        }

        // note: should check the expiry date of the training?
        // - future improvement to check this too
=======
        if (!$registry || !$registry->user) {
            return; // Defensive check
        }

        $user = $registry->user;

>>>>>>> efb9393 (add training check observer)
>>>>>>> 24c74bb (add training check observer)
        $hasTrainings = $registry->trainings()
            ->when($isDeleting, function ($query) use ($model) {
                return $query->where('trainings.id', '!=', $model->training_id);
            })
            ->exists();

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
