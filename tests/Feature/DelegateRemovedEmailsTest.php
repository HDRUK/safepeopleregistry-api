<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\Organisation;
use App\Models\User;
use Hdruk\LaravelMjml\Email;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use TriggerEmail;

class DelegateRemovedEmailsTest extends TestCase
{
    // Run SendEmailJob synchronously so we can observe the rendered email.
    protected bool $shouldFakeQueue = false;

    private Organisation $organisation;
    private User $actor;
    private User $removed;
    private User $recipient;

    // Placeholder only: Http::fake intercepts it, so no request leaves the test.
    private const MJML_RENDER_URL = 'https://mjml.example.test/render';

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
        $this->removed = User::factory()->create(['first_name' => 'Dana', 'last_name' => 'Delegate']);
        $this->recipient = User::factory()->create(['first_name' => 'Riley', 'last_name' => 'Recipient']);
    }

    private function assertRenderedEmail(User $to, array $expectedText): void
    {
        Mail::assertSent(Email::class, fn (Email $mail) => $mail->hasTo($to->email));

        Http::assertSent(function (Request $request) use ($expectedText) {
            $mjml = $request['mjml'];

            foreach ($expectedText as $text) {
                if (!str_contains($mjml, $text)) {
                    return false;
                }
            }

            return !str_contains($mjml, '[[');
        });

        $log = EmailLog::where('to', $to->email)->sole();
        $this->assertStringNotContainsString('[[', $log->subject);
        $this->assertStringContainsString('Example Research Institute', $log->subject);
    }

    public function test_the_removed_delegate_is_emailed(): void
    {
        TriggerEmail::spawnEmail([
            'type' => 'DELEGATE_REMOVED_USER',
            'identifier' => 'delegate_removed_user',
            'to' => $this->removed->id,
            'by' => $this->actor->id,
            'organisationId' => $this->organisation->id,
        ]);

        $this->assertRenderedEmail($this->removed, [
            'Hello Dana,',
            'Alex Actor has removed you as a Delegate for <b>Example Research Institute</b>',
        ]);
    }

    public function test_an_organisation_member_is_emailed_about_the_removal(): void
    {
        TriggerEmail::spawnEmail([
            'type' => 'DELEGATE_REMOVED_ORGANISATION',
            'identifier' => 'delegate_removed_organisation',
            'to' => $this->recipient->id,
            'by' => $this->actor->id,
            'for' => $this->removed->id,
            'organisationId' => $this->organisation->id,
        ]);

        $this->assertRenderedEmail($this->recipient, [
            'Hello Riley,',
            'Alex Actor has removed Dana Delegate as a Delegate for <b>Example Research Institute</b>',
        ]);
    }
}
