<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Affiliation;
use App\Models\User;
use App\Models\Project;
use App\Models\Custodian;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityOrganisation extends Notification
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Custodian $custodian, Project $project, Affiliation $affiliation, User $user)
    {
        $message = $custodian->name . " added your Organisation and User " . $affiliation->email . " to " . $project->title;

        if(config('speedi.system.notifications_enabled')) {
            $this->buildNotification($message, []);

            $this->sendEmail($affiliation, $user, $message);
        }
    }
}
