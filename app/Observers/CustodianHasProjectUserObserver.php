<?php

namespace App\Observers;

use App\Models\CustodianHasProjectUser;
use App\Models\Custodian;
use App\Models\State;
use App\Models\User;
use App\Models\ProjectHasUser;
use Illuminate\Database\Eloquent\Model;

class CustodianHasProjectUserObserver
{
    public function hasOneSaved(CustodianHasProjectUser $model, Model $related): void
    {
        dd('******* HAS ONE SAVED');
        $this->updateValidationStatus($model, $related);
    }

    protected function updateValidationStatus(CustodianHasProjectUser $model, Model $related): void
    {
        if ((app()->bound('seeding') && app()->make('seeding') === true)) {
            return;
        }

        // if($related->isDirty()) {
        //     $this->notifyStatusChanged($model);
        // }
    }

    private function getEntityData(CustodianHasProjectUser $model)
    {
        $projectHasUser = ProjectHasUsers::with(['registry.user', 'affiliation.organisation', 'project'])->where('project_has_user_id', $model->project_has_user_id);

        $custodian = Custodian::find($model->id);

        $organisationUsers = User::where([
            'organisation_id' => $projectHasUser->affiliation->organisation_id,
        ])->get();

        return ['user' => $projectHasUser->registry->user, 'custodian' => $custodian, 'organisation' => $projectHasUser->affiliation->organisation, 'organisationUsers' => $organisationUsers, 'project' => $projectHasUser->project];
    }

    private function notifyStatusChanged(CustodianHasProjectUser $model): void
    {
        // $entities = $this->getEntityData($model);

        // $userNotification = new CustodianHasProjectUserStatusUpdateEntityUser(
        //     $entities['custodian'],
        //     $entities['project'],
        //     $entities['organisation'],
        //     $entities['user']
        // );

        // Notification::send($entities['user'], $userNotification);

        // foreach ($entities['organisationUsers'] as $user) {
        //     $organisationNotification = new CustodianHasProjectUserStatusUpdateEntityOrganisation(
        //         $entities['custodian'],
        //         $entities['project'],
        //         $entities['user']
        //     );

        //     Notification::send($user, $organisationNotification);
        // }
    }
}
