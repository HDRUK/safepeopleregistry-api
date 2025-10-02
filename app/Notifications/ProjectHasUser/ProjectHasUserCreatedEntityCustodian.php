<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Affiliation;
use App\Models\User;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityCustodian extends Notification
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Custodian $custodian, Project $project, Organisation $organisation, Affiliation $affiliation)
    {
        $message = $custodian->name . " added User " . $affiliation->email . " with Organisation " . $organisation->organisation_name . " to " . $project->title;

        $this->buildNotification($message, []);
    }
}
