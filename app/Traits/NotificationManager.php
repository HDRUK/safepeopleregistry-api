<?php

namespace App\Traits;

use App\Models\User;

trait NotificationManager
{
    // custodian change project state
    // send notification to users & organisations & custodians
    public function notifyOnProjectStateChange($custodianId, $oldStatus, $newStatus)
    {
        $user = User::where([
            'id' => $custodianId,
        ])->first();

        // users

        // organisation

        // custodian



        // CustodianUpdateProjectState($custodian, $project, $newState, $oldState, $for)
        // Notification::send($custodian, new CustodianUpdateProjectState($user, $affiliation, 'custodian', $sendAffiliationNotification));
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