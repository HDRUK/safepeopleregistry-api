<?php

namespace App\Observers;

use TriggerEmail;
use App\Models\User;
use App\Models\Affiliation;
use App\Models\RegistryHasAffiliation;
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
    }

    public function updated(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);

        $user = $affiliation->registry->user;
        $orgAdmins = $this->getOrgAdmins($affiliation);
        $oldAffiliation = new Affiliation($affiliation->getOriginal());

        Notification::send($orgAdmins, new AffiliationChanged($user, $oldAffiliation, $affiliation));

        // note - need to handle if the affiliation organisation has been changed?
        // - this would need to send a create notification to do the new organisation?
        // - should they be allowed to change the affiliation anyway?!
        // - it would be much better for them to just create a new one 

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
        $registryIds = RegistryHasAffiliation::where('affiliation_id', $affiliation->id)
            ->distinct()
            ->select('registry_id')
            ->pluck('registry_id');

        foreach ($registryIds as $registryId) {
            $this->updateActionLog($registryId);
        }

        $this->emailDelegates($affiliation);
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

        $firstRha = $affiliation->registryHasAffiliations()->first();
        $userId = optional($firstRha)?->registry?->user?->id;
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

            TriggerEmail::spawnEmail($input);
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
