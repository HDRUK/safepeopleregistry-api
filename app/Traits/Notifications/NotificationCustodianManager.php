<?php

namespace App\Traits\Notifications;

use App\Models\User;
use App\Models\Project;
use App\Models\CustodianUser;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use App\Models\CustodianHasProjectOrganisation;
use App\Notifications\CustodianProjectStateChange;

trait NotificationCustodianManager
{
    // custodian change project state
    // send notification to users & organisations & custodians
    public function notifyOnProjectStateChange($loggedInUserId, $projectId, $oldStatus, $newStatus)
    {
        $loggedInUser = User::where('id', $loggedInUserId)->first();
        $project = Project::where('id', $projectId)->first();

        if (is_null($loggedInUser) || is_null($loggedInUser->custodian_user_id) || is_null($project)) {
            return;
        }

        $custodianId = $loggedInUser->custodian_user_id;

        // users
        $userIds = CustodianHasProjectUser::query()
            ->where('custodian_id', $custodianId)
            ->with([
                'projectHasUser.registry.user:id,registry_id,first_name,last_name,email',
                'projectHasUser.project',
            ])
            ->whereHas('projectHasUser.project', function ($query) use ($project) {
                    $query->where('id', $project->id);
                })
            ->get()->pluck('projectHasUser.registry.user.id')->toArray();
        if ($userIds) {
            $users = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $userIds)
                ->get();
            foreach ($users as $user) {
                Notification::send($user, new CustodianProjectStateChange($loggedInUser, $project, $oldStatus, $newStatus, 'organisation'));
            }  
        }

        // organisation
        $organisationIds = CustodianHasProjectOrganisation::query()
            ->where('custodian_id', $custodianId)
            ->with([
                'projectOrganisation',
            ])
            ->whereHas('projectOrganisation.project', function ($query) use ($project) {
                    $query->where('id', $project->id);
                })
            ->get()->pluck('projectOrganisation.organisation_id')->toArray();
        if ($organisationIds) {
            $userOrganisations = User::query()
                ->where('user_group', User::GROUP_ORGANISATIONS)
                ->whereIn('organisation_id', $organisationIds)
                ->get();

            foreach ($userOrganisations as $userOrganisation) {
                Notification::send($userOrganisation, new CustodianProjectStateChange($loggedInUser, $project, $oldStatus, $newStatus, 'organisation'));
            }       
        }

        // custodian
        $userCustodians = User::whereIn('custodian_user_id', 
            CustodianUser::where('custodian_id', $custodianId)
                ->pluck('id')
        )->get();

        foreach ($userCustodians as $userCustodian) {
            if ((int)$userCustodian->id === (int)$loggedInUserId) {
                Notification::send($userCustodian, new CustodianProjectStateChange($loggedInUser, $project, $oldStatus, $newStatus, 'current_custodian'));
            } else {
                Notification::send($userCustodian, new CustodianProjectStateChange($loggedInUser, $project, $oldStatus, $newStatus, 'custodian'));
            }
        }

    }
}