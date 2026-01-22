<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\DebugLog;

class CaptureSendGridSmtpResponse
{
    public function handle(MessageSent $event): void
    {
        // only care about SMTP which be SendGrid
        if (config('mail.default') !== 'smtp') {
            return;
        }

        $message = $event->message;
        $headers = $message->getHeaders();

        $sendgridMessageId = $headers->has('X-Message-Id')
            ? $headers->get('X-Message-Id')->getBodyAsString()
            : null;
        $jobId = $headers->has('X-App-Job-Id')
            ? $headers->get('X-App-Job-Id')->getBodyAsString()
            : null;
        $userId = $headers->has('X-App-User-Id')
            ? $headers->get('X-App-User-Id')->getBodyAsString()
            : null;

        DebugLog::create([
            'class' => 'SendGridSMTP',
            'log' => json_encode([
                'event' => 'smtp_sent',
                'job_id' => $jobId,
                'user_id' => $userId,
                'sendgrid_message_id' => $sendgridMessageId,
                'to' => array_map(
                    fn ($addr) => $addr->getAddress(),
                    $message->getTo()
                ),
                'subject' => $message->getSubject(),
            ]),
        ]);
    }
}
