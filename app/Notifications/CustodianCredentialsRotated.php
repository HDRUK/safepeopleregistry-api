<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CustodianCredentialsRotated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User $rotatedBy,
        protected string $for,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'message' => $this->generateMessage(),
            'details' => [
                'first_name' => $this->rotatedBy->first_name,
                'last_name' => $this->rotatedBy->last_name,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage(): string
    {
        $action = $this->for === 'current_custodian'
            ? 'You rotated the Safe People Registry API key.'
            : "{$this->rotatedBy->first_name} {$this->rotatedBy->last_name} rotated the Safe People Registry API key.";

        return $action . ' Update any systems that sign requests or verify webhooks with the old key.';
    }
}
