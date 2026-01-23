<?php

namespace App\Jobs;

use App\Models\DebugLog;
use Illuminate\Support\Str;
use Hdruk\LaravelMjml\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
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
    public $threadId;

    public $tries = 3;

    private MicrosoftGraphService $mgs;

    /**
     * Create a new job instance.
     */
    public function __construct(array $to, EmailTemplate $template, array $replacements, ?string $address)
    {
        $this->to = $to;
        $this->template = $template;
        $this->replacements = $replacements;
        $this->address = $address;

        $this->threadId = Str::uuid()->toString();

        \Log::info('SendEmailJob', [
            'thread_id' => $this->threadId
        ]);

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
        $retVal = null;

        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob started for: ' . json_encode($this->to),
        ]);

        switch (config('mail.default')) {
            case 'exchange':
                $this->mgs = new MicrosoftGraphService();
                $retVal = $this->mgs->sendMail($this->to, new Email($this->to['id'], $this->template, $this->replacements, $this->address));
                break;
            case 'smtp':
                $retVal = Mail::to($this->to['email'])
                    ->send(new Email($this->to['id'], $this->template, $this->replacements, $this->address, $this->threadId));
                break;
            default:
                $retVal = null;
                break;
        }


        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob completed for: ' . json_encode($this->to) . ' with result: ' . json_encode($retVal),
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
