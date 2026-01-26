<?php

namespace App\Jobs;

use App\Models\DebugLog;
use App\Models\EmailLog;
use Hdruk\LaravelMjml\Email;
use Illuminate\Bus\Queueable;
use App\Events\EmailSendFailed;
use Illuminate\Support\Facades\Mail;
use App\Events\EmailSentSuccessfully;
use Illuminate\Queue\SerializesModels;
use App\Services\MicrosoftGraphService;
use Illuminate\Queue\InteractsWithQueue;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $to = [];
    public $by = null;
    private $template = null;
    private $replacements = [];
    private $address = null;

    public $tries = 3;

    private MicrosoftGraphService $mgs;

    /**
     * Create a new job instance.
     */
    public function __construct(array $to, string $identifier, array $replacements, ?string $address)
    {
        $this->to = $to;
        $this->template = EmailTemplate::where('identifier', $identifier)->first();
        $this->replacements = $replacements;
        $this->address = $address;

        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob created with parameters: ' . json_encode([
                'to' => $this->to,
                'template' => $this->template->identifier,
                'replacements' => $this->replacements,
                'address' => $this->address,
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

        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob started for: ' . json_encode($this->to),
        ]);
            
        try {
            switch (config('mail.default')) {
                case 'exchange':
                    $this->mgs = new MicrosoftGraphService();
                    $sentMessage = $this->mgs->sendMail($this->to, new Email($this->to['id'], $this->template, $this->replacements, $this->address));
                    break;
                case 'smtp':
                    $email = new Email($this->to['id'], $this->template, $this->replacements, $this->address);
                    $html = $email->getRenderedHtml();

                    $checkEmailLog = EmailLog::where('job_uuid', $jobUuid)->first();
                    if (is_null($checkEmailLog)) {
                        EmailLog::create([
                            'to' => $this->to['email'],
                            'subject' => $this->template['subject'],
                            'template' => $this->template['identifier'],
                            'body' => $html,
                            'job_uuid' => $jobUuid,
                        ]);
                    }

                    $sentMessage = Mail::to($this->to['email'])->send($email);

                    $messageId = $sentMessage?->getSymfonySentMessage()?->getMessageId();

                    // event
                    event(new EmailSentSuccessfully($jobUuid , $messageId));

                    break;
                default:
                    $sentMessage = null;
                    break;
            }
        } catch (\Throwable $e) {
            DebugLog::create([
                'class' => __CLASS__,
                'log' => 'SendEmailJob failed for: ' . json_encode($this->to),
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
            
            throw $e;
        }


        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob completed for: ' . json_encode($this->to) . ' with result: ' . json_encode($sentMessage),
        ]);
    }

    public function tags(): array
    {
        return [
            'name' => 'send_email',
            'mailer' => config('mail.default'),
            'to' => json_encode($this->to),
            'template' => json_encode($this->template),
            'replacements' => json_encode($this->replacements),
        ];
    }
}
