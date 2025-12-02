<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrganisationDelegates extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $delegate;
    private $state;

    public function __construct($user, $delegate, $state)
    {
        $this->user = $user;
        $this->delegate = $delegate;
        $this->state = $state;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
                'delegate' => $this->delegate,
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        if ($this->state === 'add') {
            return "{$this->user->first_name} {$this->user->last_name} added {$this->delegate->first_name} {$this->delegate->last_name} as delegate to your organisation account";
        }

        if ($this->state === 'remove') {
            return "{$this->user->first_name} {$this->user->last_name}  removed {$this->delegate->first_name} {$this->delegate->last_name} as delegate to your organisation account";
        }

    }
}