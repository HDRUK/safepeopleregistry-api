<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;

use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Database\Seeders\IssuerSeeder;
use Database\Seeders\EmailTemplatesSeeder;

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
            UserSeeder::class,
            IssuerSeeder::class,
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
}