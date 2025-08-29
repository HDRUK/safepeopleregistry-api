<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Affiliation;
use App\Models\Project;
use App\Models\Custodian;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityOrganisation extends Notification
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Custodian $custodian, Project $project, Affiliation $affiliation)
    {
        $message = $custodian->name . " added your Organisation and User " . $affiliation->email . " to " . $project->title;

        $this->buildNotification($message, []);
    }
}
