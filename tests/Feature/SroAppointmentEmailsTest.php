<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\EmailTemplatesSeeder;
use Hdruk\LaravelMjml\Email;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use TriggerEmail;

class SroAppointmentEmailsTest extends TestCase
{
    // Run SendEmailJob synchronously so we can observe the rendered email.
    protected bool $shouldFakeQueue = false;

    // Placeholder only: Http::fake intercepts it, so no request leaves the test.
    private const MJML_RENDER_URL = 'https://mjml.example.test/render';

    private const PLACEHOLDERS = [
        '[[organisation.organisation_name]]',
        '[[recipient_first_name]]',
        '[[sro_first_name]]',
        '[[sro_last_name]]',
        '[[actor_first_name]]',
        '[[actor_last_name]]',
    ];

    private Organisation $organisation;
    private User $actor;
    private User $newSro;
    private User $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'smtp',
            'mjml.default.access.mjmlRenderUrl' => self::MJML_RENDER_URL,
            'mjml.email.from_address' => 'noreply@example.com',
        ]);
        Mail::fake();
        Http::fake([self::MJML_RENDER_URL => Http::response(['html' => '<html></html>'], 201)]);

        $this->organisation = Organisation::factory()->create(['organisation_name' => 'Example Research Institute']);
        $this->actor = User::factory()->create(['first_name' => 'Alex', 'last_name' => 'Actor']);
        $this->newSro = User::factory()->create(['first_name' => 'Sam', 'last_name' => 'Senior']);
        $this->recipient = User::factory()->create(['first_name' => 'Riley', 'last_name' => 'Recipient']);
    }

    private function sendEmail(string $type, string $identifier): void
    {
        TriggerEmail::spawnEmail([
            'type' => $type,
            'identifier' => $identifier,
            'to' => $this->recipient->id,
            'by' => $this->actor->id,
            'for' => $this->newSro->id,
            'organisationId' => $this->organisation->id,
        ]);
    }

    private function assertRenderedEmail(array $expectedText): void
    {
        Mail::assertSent(Email::class, fn (Email $mail) => $mail->hasTo($this->recipient->email));

        Http::assertSent(function (Request $request) use ($expectedText) {
            $mjml = $request['mjml'];

            foreach ($expectedText as $text) {
                if (!str_contains($mjml, $text)) {
                    return false;
                }
            }

            return !str_contains($mjml, '[[');
        });

        $log = EmailLog::where('to', $this->recipient->email)->sole();
        $this->assertStringNotContainsString('[[', $log->subject);
        $this->assertStringContainsString('Example Research Institute', $log->subject);
    }

    public function test_the_seeder_creates_the_sro_appointment_templates(): void
    {
        $identifiers = ['sro_replaced_user', 'sro_appointed_organisation'];
        EmailTemplate::whereIn('identifier', $identifiers)->forceDelete();

        $this->seed(EmailTemplatesSeeder::class);

        foreach ($identifiers as $identifier) {
            $template = EmailTemplate::where('identifier', $identifier)->first();

            $this->assertNotNull($template, "{$identifier} was not created");

            // EmailTemplate declares public $subject/$body properties that shadow the attributes,
            // so read them via array access as SendEmailJob does.
            foreach (self::PLACEHOLDERS as $placeholder) {
                $this->assertStringContainsString($placeholder, $template['subject'] . $template['body']);
            }
        }
    }

    public function test_the_outgoing_sro_is_emailed(): void
    {
        $this->sendEmail('SRO_REPLACED_USER', 'sro_replaced_user');

        $this->assertRenderedEmail([
            'Hello Riley,',
            'Sam Senior has been appointed as the Senior Responsible Officer (SRO) for <b>Example Research Institute</b>',
            'by Alex Actor',
            'you remain a Delegate',
        ]);
    }

    public function test_an_organisation_member_is_emailed_about_the_new_sro(): void
    {
        $this->sendEmail('SRO_APPOINTED_ORGANISATION', 'sro_appointed_organisation');

        $this->assertRenderedEmail([
            'Hello Riley,',
            'Sam Senior has been appointed as the Senior Responsible Officer (SRO) for <b>Example Research Institute</b>',
            'by Alex Actor',
        ]);
    }
}
