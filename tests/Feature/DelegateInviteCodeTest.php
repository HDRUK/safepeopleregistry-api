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

class DelegateInviteCodeTest extends TestCase
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
    }

    public function test_a_delegate_invite_emails_the_code_it_stores(): void
    {
        $organisation = Organisation::factory()->create();
        $delegate = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $organisation->id,
            'is_delegate' => 1,
            'keycloak_id' => null,
            'unclaimed' => 1,
        ]);

        TriggerEmail::spawnEmail([
            'type' => 'USER_DELEGATE',
            'to' => $delegate->id,
            'by' => $organisation->id,
            'identifier' => 'delegate_invite',
            'typeInvite' => 'delegate_invite',
        ]);

        $storedCode = PendingInvite::where('user_id', $delegate->id)->sole()->invite_code;

        $this->assertNotNull($storedCode);
        Http::assertSent(
            fn (Request $request) => str_contains($request['mjml'], "invite_code={$storedCode}&")
        );
    }
}
