<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use TriggerEmail;

class ResendInviteCodeTest extends TestCase
{
    // Run SendEmailJob synchronously so we can observe the rendered email.
    protected bool $shouldFakeQueue = false;

    // Placeholder only: Http::fake intercepts it, so no request leaves the test.
    private const MJML_RENDER_URL = 'https://mjml.example.test/render';

    private Organisation $organisation;
    private User $sro;

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

        $this->organisation = Organisation::factory()->create();
        $this->sro = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'keycloak_id' => null,
            'unclaimed' => 1,
        ]);
    }

    private function createInvite(?string $inviteCode): PendingInvite
    {
        return PendingInvite::create([
            'user_id' => $this->sro->id,
            'organisation_id' => $this->organisation->id,
            'status' => PendingInvite::STATE_PENDING,
            'invite_code' => $inviteCode,
            'type' => 'organisation_invite',
        ]);
    }

    private function resend(PendingInvite $invite): void
    {
        TriggerEmail::spawnEmail([
            'type' => 'ORGANISATION',
            'to' => $this->organisation->id,
            'unclaimed_user_id' => $this->sro->id,
            'identifier' => 'organisation_invite',
            'inviteId' => $invite->id,
        ]);
    }

    private function assertEmailedInviteCode(string $inviteCode): void
    {
        Http::assertSent(
            fn (Request $request) => str_contains($request['mjml'], "invite_code={$inviteCode}")
        );
    }

    public function test_a_resent_invite_emails_the_code_already_stored_on_the_invite(): void
    {
        $invite = $this->createInvite('6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10');

        $this->resend($invite);

        $this->assertSame('6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10', $invite->fresh()->invite_code);
        $this->assertEmailedInviteCode('6f1c1c9e-4b6e-4c1a-9a43-2d7c1e0b5f10');
    }

    public function test_resending_an_invite_without_a_code_stores_the_code_it_emails(): void
    {
        $invite = $this->createInvite(null);

        $this->resend($invite);

        $storedCode = $invite->fresh()->invite_code;
        $this->assertNotNull($storedCode);
        $this->assertEmailedInviteCode($storedCode);
    }
}
