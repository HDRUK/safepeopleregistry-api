<?php

namespace App\Notifications\ProjectHasUser;

use App\Models\Project;
use App\Models\Affiliation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ProjectHasUser\Traits\ProjectHasUserNotification;

class ProjectHasUserCreatedEntityOrganisation extends Notification implements ShouldQueue
{
    use Queueable;
    use ProjectHasUserNotification;

    public function __construct(Project $project, Affiliation $affiliation)
    {
        $message = "A custodian added your Organisation and a User " . $affiliation->email . " to " . $project->title;

        $this->buildNotification($message, []);
    }
}
