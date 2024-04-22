<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Hdruk\LaravelMjml\Email;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Tests\TestCase;

use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Bus;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\EmailTemplateException;
use Database\Seeders\EmailTemplatesSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            EmailTemplatesSeeder::class,
        ]);

        Bus::fake();
    }

    public function tearDown(): void
    {
        Bus::fake();
    }

    public function test_dispatch_email_job()
    {
        Mail::fake();

        Http::fake([
            config('mjml.default.access.mjmlRenderUrl') => Http::response(
                ["html"=>"<html>content</html>"], 
                201,
                ['application/json']
            )
        ]);

        $to = [
            'to' => [
                'email' => 'loki.sinclair@hdruk.ac.uk',
                'name' => 'Loki Sinclair',
            ],
        ];

        $template = EmailTemplate::where('identifier', '=', 'example_template')->first();

        $replacements = [
            '[[header_text]]' => 'SPeeDI-AS Registry',
            '[[button_text]]' => 'Click me!',
            '[[subheading_text]]' => 'Sub Heading Something or other',
        ];

        Bus::assertNothingDispatched();

        SendEmailJob::dispatch($to, $template, $replacements);

        Bus::assertDispatched(SendEmailJob::class);
    }

}
