<?php

namespace App\Observers;

use TriggerEmail;
use App\Models\User;
use App\Models\Affiliation;
use App\Models\State;
use App\Traits\AffiliationCompletionManager;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Affiliations\AffiliationCreated;
use App\Notifications\Affiliations\AffiliationDeleted;
use App\Notifications\Affiliations\AffiliationChanged;

class AffiliationObserver
{
    use AffiliationCompletionManager;

    public function created(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
        $user = $affiliation->registry->user;
        $orgAdmins = $this->getOrgAdmins($affiliation);

        Notification::send($orgAdmins, new AffiliationCreated($user, $affiliation));

        $unclaimed = $affiliation->organisation->unclaimed;
        $initialState = $unclaimed ? State::STATE_AFFILIATION_INVITED : State::STATE_AFFILIATION_PENDING;
        $affiliation->setState($initialState);
    }

    public function updated(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);

        $user = $affiliation->registry->user;
        $orgAdmins = $this->getOrgAdmins($affiliation);
        $oldAffiliation = new Affiliation($affiliation->getOriginal());

        Notification::send($orgAdmins, new AffiliationChanged($user, $oldAffiliation, $affiliation));
    }

    public function deleted(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);

        $user = $affiliation->registry->user;
        $orgAdmins = $this->getOrgAdmins($affiliation);

        Notification::send($orgAdmins, new AffiliationDeleted($user, $affiliation));
    }

    protected function handleChange(Affiliation $affiliation): void
    {
        $this->emailDelegates($affiliation);
        $this->updateActionLog($affiliation->registry_id);
        $this->updateOrganisationActionLog($affiliation);
    }

    protected function emailDelegates(Affiliation $affiliation)
    {
        $isComplete = $this->checkComplete($affiliation);

        $originalAttributes = $affiliation->getOriginal();
        $originalAffiliation = new Affiliation($originalAttributes);
        $wasIncomplete = !$this->checkComplete($originalAffiliation);

        if (!($isComplete && $wasIncomplete)) {
            return;
        }

        $orgId = $affiliation->organisation_id;

        $delegateIds = User::where([
            'organisation_id' => $orgId,
            'is_delegate' => 1
        ])->select('id')->pluck('id');

        $userId = $affiliation->registry?->user?->id;
        if (is_null($userId)) {
            return;
        }

        foreach ($delegateIds as $delegateId) {
            $input = [
                'type' => 'USER_DELEGATE',
                'to' => $delegateId,
                'by' => $orgId,
                'for' => $userId,
                'identifier' => 'delegate_sponsor'
            ];

            // dont start emailing delegates when seeding
            if (!(app()->bound('seeding') && app()->make('seeding') === true)) {
                TriggerEmail::spawnEmail($input);
            }
        }
    }

    protected function checkComplete(Affiliation $affiliation)
    {
        return !empty($affiliation->member_id) &&
            !empty($affiliation->relationship) &&
            !empty($affiliation->from);
    }

    private function getOrgAdmins(Affiliation $affiliation)
    {
        $organisation = $affiliation->organisation;
        return User::where([
            'organisation_id' => $organisation->id,
            'user_group' => User::GROUP_ORGANISATIONS
        ])->get();
    }
}
