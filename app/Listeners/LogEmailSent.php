<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;
use App\Events\EmailSentSuccessfully;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogEmailSent
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
    public function handle(EmailSentSuccessfully $event): void
    {
        Log::info('Email sent successfully', [
            'job_uuid' => $event->jobUuid,
            'message_id' => $event->messageId,
            'timestamp' => now()->toIso8601String(),
        ]);

        $checkEmailLog = EmailLog::where('job_uuid', $event->jobUuid)->first();
        if (is_null($checkEmailLog)) {
            return;
        }

        EmailLog::where('job_uuid', $event->jobUuid)
            ->update([
                'job_status' => 1,
                'message_id' => $event->messageId,
                'error_message' => null,
            ]);
    }
}
