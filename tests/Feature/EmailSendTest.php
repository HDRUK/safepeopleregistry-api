<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;

use App\Models\PendingInvite;

use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\OrganisationSeeder;
use Database\Seeders\EmailTemplatesSeeder;
use Database\Seeders\OrganisationDelegateSeeder;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;


use Tests\Traits\Authorisation;

class EmailSendTest extends TestCase
{
    use RefreshDatabase, Authorisation;

    const TEST_URL = '/api/v1/trigger_email';

    private $headers = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            IssuerSeeder::class,
            UserSeeder::class,
            OrganisationSeeder::class,
            OrganisationDelegateSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $this->getAuthToken(),
        ];
    }

    public function test_the_application_can_send_emails(): void
    {
        Queue::fake();
        Queue::assertNothingPushed();

        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'to' => 1, // Primary key - in this instance, it relates to pre-seeded Issuer record
                'type' => 'issuer', // Type of model relating to email
                'identifier' => 'issuer_invite', // Email Template
            ],
            $this->headers
        );

        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_the_application_adds_pending_invites(): void
    {
        Queue::fake();
        Queue::assertNothingPushed();

        $response = $this->json(
            'POST',
            self::TEST_URL,
            [
                'to' => 1,
                'type' => 'researcher',
                'by' => 1,
                'identifier' => 'researcher_invite',
            ],
            $this->headers
        );

        $response->assertStatus(200);

        Queue::assertPushed(SendEmailJob::class);

        $invites = PendingInvite::all();
        $this->assertTrue(count($invites) === 1);
        $this->assertTrue($invites[0]->user_id === 1);
        $this->assertTrue($invites[0]->organisation_id === 1);
    }
}