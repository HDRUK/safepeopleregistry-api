<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\EmailTemplatesSeeder;
use Hdruk\LaravelMjml\Email;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use TriggerEmail;

class OrganisationInviteByUserEmailTest extends TestCase
{
    // Run SendEmailJob synchronously so we can observe the rendered email.
    protected bool $shouldFakeQueue = false;

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

        $this->seed(EmailTemplatesSeeder::class);
    }

    public function test_the_invite_names_the_inviter(): void
    {
        $organisation = Organisation::factory()->create([
            'organisation_name' => 'Example Research Institute',
            'lead_applicant_email' => 'lead.applicant@example.com',
        ]);
        $inviter = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'user_group' => User::GROUP_USERS,
        ]);

        TriggerEmail::spawnEmail([
            'type' => 'ORGANISATION_INVITE_BY_USER',
            'identifier' => 'organisation_invite_by_user',
            'to' => $organisation->lead_applicant_email,
            'by' => $inviter->id,
            'organisationId' => $organisation->id,
        ]);

        Mail::assertSent(Email::class, fn (Email $mail) => $mail->hasTo('lead.applicant@example.com')
            && $mail->envelope()->subject === 'Safe People Registry | Organisation invite from Jane Doe');

        Http::assertSent(fn (Request $request) => str_contains($request['mjml'], 'by Jane Doe.')
            && ! str_contains($request['mjml'], '[['));
    }
}
