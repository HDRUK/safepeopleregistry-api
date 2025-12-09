<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianEndProject extends Notification implements ShouldQueue
{
    use Queueable;

    protected $project;
    protected $for;

    public function __construct($project, $for)
    {
        $this->project = $project;
        $this->for= $for;
    }

    /**
     * Specify the delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Store the notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->generateMessage(),
            'details' => [
                'project_title' => $this->project->title,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        $urlProjectCustodian = config('speedi.system.portal_url') . '/en/data-custodian/profile/projects/' . $this->project->id . '/safe-project';
        $urlProjectOrganisation = config('speedi.system.portal_url') . '/en/organisation/profile/projects/' . $this->project->id . '/safe-project';
        
        switch ($this->for) {
            case 'user':
                return "Project {$this->project->title} has ended.";

            case 'organisation':
                return "Project {$this->project->title} has ended. [<a href=\"{$urlProjectOrganisation}\">Go to project</a>]";

            case 'custodian':
                return "Project {$this->project->title} has ended. [<a href=\"{$urlProjectCustodian}\">Go to project</a>]";

            default:
                break;
        }
    }
}