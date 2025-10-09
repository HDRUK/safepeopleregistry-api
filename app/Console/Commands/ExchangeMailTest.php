<?php

namespace App\Console\Commands;

use Hdruk\LaravelMjml\Email;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use App\Services\MicrosoftGraphService;
use Illuminate\Console\Command;

class ExchangeMailTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:exchange-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug command for testing exchange mail integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mgs = new MicrosoftGraphService();
        $to = [
            'id' => 10,
            'email' => 'loki.sinclair@hdruk.ac.uk',
            'name' => 'Loki Sinclair',
        ];

        $template = EmailTemplate::where('identifier', '=', 'example_template')->first();
        $replacements = [
            '[[header_text]]' => 'Safe People Registry',
            '[[button_text]]' => 'Click me!',
            '[[subheading_text]]' => 'Some Subheading',
        ];

        $mgs->sendMail($to, new Email($to['id'], $template, $replacements, null));
    }
}
