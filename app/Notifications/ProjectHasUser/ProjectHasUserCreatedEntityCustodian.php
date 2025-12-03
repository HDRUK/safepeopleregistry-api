<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Project;
use App\Models\Custodian;
use App\Models\Affiliation;
use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityCustodian extends Notification implements ShouldQueue
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Custodian $custodian, Project $project, Organisation $organisation, Affiliation $affiliation)
    {
        $message = $custodian->name . " added User " . $affiliation->email . " with Organisation " . $organisation->organisation_name . " to " . $project->title;

        $this->buildNotification($message, []);
    }
}
