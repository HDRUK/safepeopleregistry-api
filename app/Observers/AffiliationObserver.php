<?php

namespace App\Observers;

use TriggerEmail;
use App\Models\User;
use App\Models\State;
use App\Models\Affiliation;
use App\Jobs\MergeUserAccounts;
use App\Traits\AffiliationCompletionManager;
use App\Traits\Notifications\NotificationUserManager;

class AffiliationObserver
{
    use AffiliationCompletionManager;
    use NotificationUserManager;

    public function created(Affiliation $affiliation): void
    {
        $this->setInitialState($affiliation);
        $this->handleChange($affiliation);
        $this->sendNotificationOnCreate($affiliation);
    }

    public function updated(Affiliation $affiliation): void
    {
        \Log::info('IEnjoySundayRoasts');
        \Log::info('1');

        $old = new Affiliation($affiliation->getOriginal());
        $this->handleChange($affiliation, $old);
       
        $this->sendNotificationOnUpdate($affiliation, $old);
    }

    public function deleted(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
    }

    protected function handleChange(Affiliation $affiliation, Affilation $old): void
    {
        \Log::info('2');
        $this->emailDelegatesIfNowComplete($affiliation, $old));
        $this->updateActionLog($affiliation->registry_id);
        $this->updateOrganisationActionLog($affiliation);

        MergeUserAccounts::dispatch($affiliation);
    }

    private function emailDelegatesIfNowComplete(Affiliation $affiliation, Affilation $old): void
    {
        \Log::info('3');
        if (!$this->isNowComplete($affiliation, $old)) {
            return;
        }
        \Log::info('5');
        if (!(app()->bound('seeding') && app()->make('seeding') === true)) {
            \Log::info('6<<<<<<<<<<<<<<<<');
            $this->sendDelegateEmails($affiliation);
        }
    }

    private function isNowComplete(Affiliation $affiliation, Affilation $old): bool
    {
        \Log::info('4');
        return $this->checkComplete($affiliation, true)
            && !$this->checkComplete(new Affiliation($old, false));
    }

    private function checkComplete(Affiliation $affiliation, bool $isNew): bool
    {
        \Log::info('<<<<<<<<<<<<<< checkComplete called', [
            'isNew' => $isNew,
            'member_id' => $affiliation->member_id,
            'relationship' => $affiliation->relationship,
            'from' => $affiliation->from,
            'is_verified' => $affiliation->is_verified,
        ]);
        




        return filled($affiliation->member_id)
            && filled($affiliation->relationship)
            && filled($affiliation->from) && $affiliation->is_verified === 1;
    }

    private function sendDelegateEmails(Affiliation $affiliation): void
    {
        $userDelegates = User::where([
                'organisation_id' => $affiliation->organisation_id,
                'user_group' => 'ORGANISATIONS',
            ])
            ->where(function ($query) {
                $query->where('is_delegate', 1)
                    ->orWhere('is_sro', 1);
            })
            ->get();

        $userId = $affiliation->registry?->user?->id;
        if (!$userId) {
            return;
        }

        foreach ($userDelegates as $userDelegate) {
            $email = [
                'type' => 'DELEGATE_AFFILIATION_REQUEST',
                'to' => $userDelegate->id,
                'by' => $affiliation->organisation_id,
                'for' => $userId,
                'identifier' => 'delegate_affiliation_request',
                'affiliationId' => $affiliation->id,
            ];

            TriggerEmail::spawnEmail($email);

            $affiliation->setState(State::STATE_AFFILIATION_EMAIL_VERIFY);
        }
    }

    private function setInitialState(Affiliation $affiliation)
    {
        if (!$affiliation->getState()) {
            return $affiliation->setState(State::STATE_AFFILIATION_PENDING);
        }
    }

    public function sendNotificationOnCreate(Affiliation $affiliation): void
    {
        $this->notifyOnUserCreateAffiliation($affiliation);
    }

    public function sendNotificationOnUpdate(Affiliation $affiliation, $old): void
    {
        $this->notifyOnUserChangeAffiliation($affiliation, $old);
    }

}
