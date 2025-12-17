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
        $this->for = $for;
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
            'action_url' => $this->getUrl(),
            'details' => [
                'project_title' => $this->project->title,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        return "Project {$this->project->title} has ended.";
    }

    public function getUrl()
    {
        $urlProjectCustodian = config('speedi.system.portal_url') . '/en/data-custodian/profile/projects/' . $this->project->id . '/safe-project';
        $urlProjectOrganisation = config('speedi.system.portal_url') . '/en/organisation/profile/projects/' . $this->project->id . '/safe-project';
        switch ($this->for) {
            case 'organisation':
                return $urlProjectOrganisation;

            case 'custodian':
                return $urlProjectCustodian;

            default:
                return null;
        }
    }
}
