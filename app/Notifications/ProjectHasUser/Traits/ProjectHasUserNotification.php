<?php

namespace App\Notifications\ProjectHasUser\Traits;

use App\Jobs\SendEmailJob;
use App\Models\Affiliation;
use Hdruk\LaravelMjml\Models\EmailTemplate;

trait ProjectHasUserNotification
{
    protected $payload;

    protected function buildNotification(string $message, $details)
    {
        $this->payload = [
            'message' => $message,
            'details' => $details ?? null,
            'time' => now(),
        ];
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return $this->payload;
    }

    public function sendEmail(Affiliation $affiliation, string $message)
    {
        $template = EmailTemplate::where('identifier', 'notification')->first();

        $newRecipients = [
            'id' => $affiliation->id,
            'email' => $affiliation->email,
        ];

        $replacements = [
            '[[message]]' => $message
        ];

        SendEmailJob::dispatch($newRecipients, $template, $replacements, $newRecipients['email']);
    }
}
