<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustodianProjectStateChange;

trait NotificationCustodianManager
{
    // custodian change project state
    // send notification to users & organisations & custodians
    public function notifyOnProjectStateChange($loggedInUserId, $projectId, $oldStatus, $newStatus)
    {
        $loggedInUser = User::where([
            'id' => $loggedInUserId,
        ])->first();

        if (is_null($loggedInUser) || is_null($loggedInUser->custodian_user_id)) {
            return;
        }

        $custodianId = $loggedInUser->custodian_user_id;

        $project = Project::where('id', $projectId)->first();
        



        // users

        // organisation

        // custodian
        Notification::send($loggedInUser, new CustodianProjectStateChange($loggedInUser, $project, $oldStatus, $newStatus, 'custodian'));



    }

    // protected function getOneUser(array $filter)
    // {
    //     return User::where($filter)->first();
    // }

    // protected function getAllUser(array $filter)
    // {
    //     return User::where($filter)->get();
    // }
}