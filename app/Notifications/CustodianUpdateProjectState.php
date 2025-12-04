<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianUpdateProjectState extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $project;
    protected $newState;
    protected $oldState;
    protected $for;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $project, $newState, $oldState, $for)
    {
        $this->user = $user;
        $this->project = $project;
        $this->newState = $newState;
        $this->oldState = $oldState;
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
            'details' => [
                'new' => 'new',
                'old' => 'old',
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'user':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} changed the status of your project {$this->project->title} from ($this->oldState) to ($this->newState)";

            case 'organisation':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} has changed the status of project {$this->project->title} from ($this->oldState) to ($this->newState)";

            case 'custodian':
                return "You changed the status of project {$this->project->title} from ($this->oldState) to ($this->newState)";

            default:
                break;
        }
    }
}