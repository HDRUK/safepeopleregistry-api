<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianProjectDetailsUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $project;
    protected $changes;
    protected $for;

    public function __construct($user, $project, $changes, $for)
    {
        $this->user = $user;
        $this->project = $project;
        $this->changes = $changes;
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
            'details' => $this->changes,
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'user':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} changed the project details for {$this->project->title}.";

            case 'organisation':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} has changed the project details for {$this->project->title}.";

            case 'current_custodian':
                return "You changed the project details for {$this->project->title}.";

            case 'custodian':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} has changed the project details for {$this->project->title}.";

            default:
                break;
        }
    }
}