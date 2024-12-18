<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Models\PendingInvite;
use Database\Seeders\EmailTemplatesSeeder;
use Database\Seeders\CustodianSeeder;
use Database\Seeders\OrganisationDelegateSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class EmailSendTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/trigger_email';

    private $user = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            CustodianSeeder::class,
            UserSeeder::class,
            OrganisationSeeder::class,
            OrganisationDelegateSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();
    }

    public function test_the_application_can_send_emails(): void
    {
        Queue::fake();
        Queue::assertNothingPushed();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'POST',
                self::TEST_URL,
                [
                    'to' => 1, // Primary key - in this instance, it relates to pre-seeded Custodian record
                    'type' => 'custodian', // Type of model relating to email
                    'identifier' => 'custodian_invite', // Email Template
                ]
            );

        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_the_application_adds_pending_invites(): void
    {
        Queue::fake();
        Queue::assertNothingPushed();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json('POST', self::TEST_URL, [
                'to' => 1,
                'type' => 'user',
                'by' => 1,
                'identifier' => 'researcher_invite',
            ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendEmailJob::class);

        $invites = PendingInvite::all();
        $this->assertTrue(count($invites) === 1);
        $this->assertTrue($invites[0]->user_id === 1);
        $this->assertTrue($invites[0]->organisation_id === 1);
    }
}
