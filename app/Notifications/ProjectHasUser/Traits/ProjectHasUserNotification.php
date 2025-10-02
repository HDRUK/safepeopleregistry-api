<?php

namespace App\Notifications\ProjectHasUser\Traits;

use App\Jobs\SendEmailJob;
use App\Models\Affiliation;
use App\Models\User;
use Hdruk\LaravelMjml\Models\EmailTemplate;

trait ProjectHasUserNotification
{
    protected $payload;

    protected function buildNotification(string $message, $details)
    {
        $this->payload = [
            'message' => $message,
            'details' => $details ?? [],
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

    public function sendEmail(Affiliation $affiliation, string $email, int $userId, array $message = [])
    {
        $template = EmailTemplate::where('identifier', 'notification')->first();

        $newRecipients = [
            'id' => $userId,
            'email' => $email,
        ];

        $replacements = [
            '[[project_name]]' => $message['[[project.title]]'],
            '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
            '[[link_project]]' => config('speedi.system.portal_url') . 'user/profile/projects/' . $message['[[project.id]]'] . '/safe-project',
            '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
        ];

        SendEmailJob::dispatch($newRecipients, $template, $replacements, $newRecipients['email']);
    }
}
