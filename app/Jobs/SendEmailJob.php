<?php

namespace App\Jobs;

use Hdruk\LaravelMjml\Email;
use Hdruk\LaravelMjml\Models\EmailTemplate;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to = [];
    public $by = null;
    private $template = null;
    private $replacements = [];

    /**
     * Create a new job instance.
     */
    public function __construct(array $to, EmailTemplate $template, array $replacements, array $by = null)
    {
        $this->to = $to;
        $this->by = ($by !== null ? $by : null);
        $this->template = $template;
        $this->replacements = $replacements;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $by = !empty($this->by) ? $this->by['id'] : null;

        Mail::to($this->to['email'])
            ->send(new Email($this->to['id'], $this->template, $this->replacements, $by));
    }
}
