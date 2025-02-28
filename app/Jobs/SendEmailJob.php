<?php

namespace App\Jobs;

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

    /**
     * Create a new job instance.
     */
    public function __construct(array $to, EmailTemplate $template, array $replacements)
    {
        $this->to = $to;
        $this->template = $template;
        $this->replacements = $replacements;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->to['email'])
            ->send(new Email($this->to['id'], $this->template, $this->replacements));
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
