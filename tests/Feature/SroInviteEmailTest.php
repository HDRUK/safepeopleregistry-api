<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use Database\Seeders\EmailTemplatesSeeder;
use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use TriggerEmail;

class SroInviteEmailTest extends TestCase
{
    // Run SendEmailJob synchronously so we can observe the rendered email.
    protected bool $shouldFakeQueue = false;

    // Placeholder only: Http::fake intercepts it, so no request leaves the test.
    private const MJML_RENDER_URL = 'https://mjml.example.test/render';

    private Organisation $organisation;
    private User $actor;
    private User $invitee;

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
        $this->invitee = User::factory()->create([
            'first_name' => 'Sam',
            'last_name' => 'Senior',
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => 1,
            'unclaimed' => 1,
            'keycloak_id' => null,
        ]);
    }

    private function sendInvite(?int $inviteId = null): void
    {
        TriggerEmail::spawnEmail(array_filter([
            'type' => 'SRO_INVITE',
            'identifier' => 'sro_invite',
            'to' => $this->invitee->id,
            'by' => $this->actor->id,
            'organisationId' => $this->organisation->id,
            'inviteId' => $inviteId,
        ]));
    }

    private function sroInvites()
    {
        return PendingInvite::where('user_id', $this->invitee->id)->where('type', 'sro_invite');
    }

    public function test_the_seeder_creates_the_sro_invite_template(): void
    {
        EmailTemplate::where('identifier', 'sro_invite')->forceDelete();

        $this->seed(EmailTemplatesSeeder::class);

        $template = EmailTemplate::where('identifier', 'sro_invite')->first();
        $this->assertNotNull($template);

        // EmailTemplate declares public $subject/$body properties that shadow the attributes,
        // so read them via array access as SendEmailJob does.
        foreach ([
            '[[organisation.organisation_name]]',
            '[[recipient_first_name]]',
            '[[actor_first_name]]',
            '[[actor_last_name]]',
            'invite_code=[[inviteCode]]',
            '[[registration.email]]',
        ] as $placeholder) {
            $this->assertStringContainsString($placeholder, $template['subject'] . $template['body']);
        }
    }

    public function test_inviting_an_sro_stores_an_invite_and_emails_its_code(): void
    {
        $this->sendInvite();

        $invite = $this->sroInvites()->sole();
        $this->assertSame($this->organisation->id, $invite->organisation_id);
        $this->assertSame(PendingInvite::STATE_PENDING, $invite->status);
        $this->assertNotNull($invite->invite_code);

        Http::assertSent(fn (Request $request) => str_contains($request['mjml'], 'Hello Sam,')
            && str_contains($request['mjml'], 'Alex Actor has invited you to be the Senior Responsible Officer (SRO) for <b>Example Research Institute</b>')
            && str_contains($request['mjml'], "invite_code={$invite->invite_code}&")
            && !str_contains($request['mjml'], '[['));
    }

    public function test_resending_an_sro_invite_reuses_its_code(): void
    {
        $this->sendInvite();
        $invite = $this->sroInvites()->sole();

        $this->sendInvite($invite->id);

        $this->assertSame(1, $this->sroInvites()->count());
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => str_contains($request['mjml'], "invite_code={$invite->invite_code}&"));
    }
}
