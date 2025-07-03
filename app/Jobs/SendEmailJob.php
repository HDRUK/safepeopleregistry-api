<?php

namespace App\Jobs;

use App\Models\DebugLog;
use Hdruk\LaravelMjml\Email;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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

    /**
     * Create a new job instance.
     */
    public function __construct(array $to, EmailTemplate $template, array $replacements, ?string $address)
    {
        $this->to = $to;
        $this->template = $template;
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
        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob started for: ' . json_encode($this->to),
        ]);

        $retVal = Mail::to($this->to['email'])
            ->send(new Email($this->to['id'], $this->template, $this->replacements, $this->address));

        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendEmailJob completed for: ' . json_encode($this->to) . ' with result: ' . json_encode($retVal),
        ]);
    }

    public function tags(): array
    {
        return [
            'name' => 'send_email',
            'to' => json_encode($this->to),
            'template' => json_encode($this->template),
            'replacements' => json_encode($this->replacements),
        ];
    }
}
