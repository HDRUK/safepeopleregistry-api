<?php

namespace App\Observers;

use App\Models\DecisionModelLog;
use App\Traits\Notifications\NotificationUserManager;
use App\Traits\Notifications\NotificationOrganisationManager;

class DecisionModelLogObserver
{
    use NotificationUserManager;
    use NotificationOrganisationManager;

    public function updated(DecisionModelLog $decisionModelLog)
    {
        if ((bool)$decisionModelLog->getOriginal('status') !== (bool)$decisionModelLog->status) {
            if ($decisionModelLog->model_type === DecisionModelLog::DECISION_MODEL_USERS) {
                $this->notifyOnUserChangeAutomatedFlags($decisionModelLog);
            }

            if ($decisionModelLog->model_type === DecisionModelLog::DECISION_MODEL_ORGANISATIONS) {
                $this->notifyOnOrganisationChangeAutomatedFlags($decisionModelLog);
            }
        }
    }
}
