<?php

namespace Tests\Feature;

use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\EmailTemplatesSeeder;
use Hdruk\LaravelMjml\Email;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use TriggerEmail;

class OrganisationInviteByCustodianEmailTest extends TestCase
{
    // Run SendEmailJob synchronously so we can observe the rendered email.
    protected bool $shouldFakeQueue = false;

    // Placeholder only: Http::fake intercepts it, so no request leaves the test.
    private const MJML_RENDER_URL = 'https://mjml.example.test/render';

    private Organisation $organisation;

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

        $this->organisation = Organisation::factory()->create([
            'organisation_name' => 'Example Research Institute',
            'lead_applicant_email' => 'lead.applicant@example.com',
        ]);
    }

    private function inviter(?int $custodianUserId): User
    {
        return User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'user_group' => User::GROUP_CUSTODIANS,
            'custodian_user_id' => $custodianUserId,
        ]);
    }

    private function sendInvite(User $inviter): void
    {
        TriggerEmail::spawnEmail([
            'type' => 'ORGANISATION_INVITE_BY_CUSTODIAN',
            'identifier' => 'organisation_invite_by_custodian',
            'to' => $this->organisation->lead_applicant_email,
            'by' => $inviter->id,
            'organisationId' => $this->organisation->id,
        ]);
    }

    public function test_the_invite_names_the_inviter_and_their_custodian(): void
    {
        $custodian = Custodian::factory()->create(['name' => 'Example Health Custodian']);
        $custodianUser = CustodianUser::factory()->create(['custodian_id' => $custodian->id]);

        $this->sendInvite($this->inviter($custodianUser->id));

        Mail::assertSent(Email::class, fn (Email $mail) => $mail->hasTo('lead.applicant@example.com')
            && $mail->envelope()->subject === 'Safe People Registry | Organisation invite from Jane Doe on behalf of Example Health Custodian');

        Http::assertSent(fn (Request $request) => str_contains($request['mjml'], 'by Jane Doe on behalf of Example Health Custodian.')
            && ! str_contains($request['mjml'], 'TO REPLACE')
            && ! str_contains($request['mjml'], '[['));
    }

    public function test_an_inviter_without_a_custodian_cannot_send_the_invite(): void
    {
        $this->assertThrows(
            fn () => $this->sendInvite($this->inviter(null)),
            ModelNotFoundException::class
        );

        Mail::assertNothingSent();
    }
}
