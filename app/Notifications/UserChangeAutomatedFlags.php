<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserChangeAutomatedFlags extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $custodian;
    protected $decisionModel;
    protected $for;

    public function __construct($user, $custodian, $decisionModel)
    {
        $this->user = $user;
        $this->custodian = $custodian;
        $this->decisionModel = $decisionModel;
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
            'causer_id' => $this->user->id,
            'causer_action' => 'automated_flags',
            'message' => $this->generateMessage(),
            'details' => [
                'custodian_name' => $this->user->first_name . ' ' . $this->user->last_name,
                'decision_model_name' => $this->decisionModel->name,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        return "User {$this->user->first_name} {$this->user->last_name} changed {$this->decisionModel->name} in their user profile which is flagged by your configuration.";
    }
}