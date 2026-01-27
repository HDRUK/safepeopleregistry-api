<?php

namespace App\Jobs;

use App\Models\DebugLog;
use App\Models\EmailLog;
use App\Events\EmailSendFailed;
use Hdruk\LaravelMjml\HtmlEmail;
use Illuminate\Support\Facades\Mail;
use App\Events\EmailSentSuccessfully;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SentHtmlEmalJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    private $id = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(int $id)
    {
        $this->id = $id;

        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SentHtmlEmalJob created with parameters: ' . json_encode([
                'id' => $id,
            ]),
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sentMessage = null;
        $jobUuid = $this->job->uuid();
        $jobAttempt = $this->attempts();

        $emailLog = EmailLog::find($this->id);
        if (is_null($emailLog)) {
            DebugLog::create([
                'class' => __CLASS__,
                'log' => 'SentHtmlEmalJob No email log found for id ' . $this->id . '. The job will exit without sending email.',
            ]);

            return;
        }

        $to = $emailLog->to;
        $subject = $emailLog->subject;
        $htmlBody = $emailLog->body;
        $template = $emailLog->template;

        try {

            DebugLog::create([
                'class' => __CLASS__,
                'log' => 'SentHtmlEmalJob prepare for sending email : ' . json_encode([
                    'id' => $this->id,
                    'subject' => $subject,
                    'to' => $to,
                    // 'htmlBody' => $htmlBody, // commented out to avoid logging large content
                ]),
            ]);

            $checkEmailLog = EmailLog::where('job_uuid', $jobUuid)->first();
            if (is_null($checkEmailLog)) {
                EmailLog::create([
                    'to' => $to,
                    'subject' => $subject,
                    'template' => $template,
                    'body' => $htmlBody,
                    'job_uuid' => $jobUuid,
                ]);
            }

            $sentMessage = Mail::to($to)->send(
                new HtmlEmail($subject, $htmlBody)
            );

            $messageId = $sentMessage?->getSymfonySentMessage()?->getMessageId();

            // event
            event(new EmailSentSuccessfully($jobUuid, $messageId));

        } catch (\Throwable $e) {
            DebugLog::create([
                'class' => __CLASS__,
                'log' => 'SendEmailJob failed for: ' . json_encode($to),
                'context' => json_encode([
                    'job_uuid' => $jobUuid,
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ]),
            ]);

            // event
            event(new EmailSendFailed($jobUuid, $e->getMessage()));

            $isLastAttempt = $this->attempts() >= $this->tries;

            if ($isLastAttempt) {
                DebugLog::create([
                    'class' => __CLASS__,
                    'log' => 'SentHtmlEmalJob reached max attempts for job UUID ' . $jobUuid . '. No more retries will be made.',
                ]);

                // delete the email log after final failed attempt
                EmailLog::where('id', $this->id)->delete();
            }

            throw $e;
        }

        // delete the email log after sending
        EmailLog::where('id', $this->id)->delete();
        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SentHtmlEmalJob email sent for id ' . $this->id . ' to ' . $to . ' with message ID ' . $messageId,
        ]);
    }
}
