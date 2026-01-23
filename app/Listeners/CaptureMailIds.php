<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CaptureMailIds
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        if (config('mail.default') !== 'smtp') {
            return;
        }

        $email = $event->sent->getOriginalMessage(); // Symfony\Component\Mime\Email

        $threadId = $email->getHeaders()
            ->get('X-Thread-Id')
            ?->getBodyAsString();

        $messageId = $email->getHeaders()
            ->get('Message-ID')
            ?->getBodyAsString();

        \Log::info('CaptureMailIds', [
            'class' => __CLASS__,
            'log' => json_encode([
                // 'email' => $email->toString(),
                'thread_id'  => $threadId,
                'message_id' => $event->sent?->getMessageId(),
            ]),
        ]);

        // save to DB/log
        // \App\Models\DebugLog::create([
        //     'class' => __CLASS__,
        //     'log' => json_encode([
        //         'thread_id'  => $threadId,
        //         'message_id' => $messageId,
        //     ]),
        // ]);
    }
}
