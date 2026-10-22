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

    private const COMMON_PLACEHOLDERS = [
        '[[organisation.organisation_name]]',
        '[[recipient_first_name]]',
        '[[actor_first_name]]',
        '[[actor_last_name]]',
    ];

    // The new SRO's own email is addressed to them, so it does not name the SRO separately.
    private const TEMPLATES = [
        'sro_replaced_user' => ['[[sro_first_name]]', '[[sro_last_name]]'],
        'sro_appointed_organisation' => ['[[sro_first_name]]', '[[sro_last_name]]'],
        'sro_appointed_user' => [],
        'sro_claimed_user' => [],
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

    private function sendEmail(string $type, string $identifier, ?User $sro = null): void
    {
        TriggerEmail::spawnEmail([
            'type' => $type,
            'identifier' => $identifier,
            'to' => $this->recipient->id,
            'by' => $this->actor->id,
            'for' => ($sro ?? $this->newSro)->id,
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
        EmailTemplate::whereIn('identifier', array_keys(self::TEMPLATES))->forceDelete();

        $this->seed(EmailTemplatesSeeder::class);

        foreach (self::TEMPLATES as $identifier => $placeholders) {
            $template = EmailTemplate::where('identifier', $identifier)->first();

            $this->assertNotNull($template, "{$identifier} was not created");

            // EmailTemplate declares public $subject/$body properties that shadow the attributes,
            // so read them via array access as SendEmailJob does.
            foreach ([...self::COMMON_PLACEHOLDERS, ...$placeholders] as $placeholder) {
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

    public function test_the_new_sro_is_emailed(): void
    {
        $this->sendEmail('SRO_APPOINTED_USER', 'sro_appointed_user', sro: $this->recipient);

        $this->assertRenderedEmail([
            'Hello Riley,',
            'You have been appointed as the Senior Responsible Officer (SRO) for <b>Example Research Institute</b>',
            'by Alex Actor',
            'SRO declaration',
        ]);
    }

    public function test_an_sro_who_has_claimed_their_account_gets_a_confirmation(): void
    {
        $this->sendEmail('SRO_CLAIMED_USER', 'sro_claimed_user', sro: $this->recipient);

        $this->assertRenderedEmail([
            'Hello Riley,',
            'This confirms that you have accepted the invitation from Alex Actor and are now the Senior Responsible Officer (SRO) for <b>Example Research Institute</b>',
            'SRO declaration',
        ]);
    }
}
