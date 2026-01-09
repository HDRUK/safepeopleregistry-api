<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrganisationChangeAutomatedFlags extends Notification implements ShouldQueue
{
    use Queueable;

    protected $organisation;
    protected $custodian;
    protected $decisionModel;
    protected $for;

    public function __construct($organisation, $custodian, $decisionModel)
    {
        $this->organisation = $organisation;
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
            'causer_id' => $this->organisation->id,
            'causer_action' => 'automated_flags',
            'message' => $this->generateMessage(),
            'details' => [
                'custodian_name' => $this->custodian->name,
                'organisation_name' => $this->organisation->organisation_name,
                'decision_model_name' => $this->decisionModel->name,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        return "{$this->organisation->organisation_name} hanged their profile, which has changed the result of your automated: {$this->decisionModel->name} flag.";
    }
}
