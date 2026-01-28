<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmailLog;
use App\Jobs\SentHtmlEmalJob;
use App\Jobs\SentHtmlEmailJob;
use Tests\Traits\Authorisation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use KeycloakGuard\ActingAsKeycloakUser;
use Hdruk\LaravelMjml\Models\EmailTemplate;

class EmailLogTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/email_logs';
    
    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
        $this->createDataEmailLog();
    }

    public function test_application_can_access_if_no_admin_role()
    {
        $user = User::where('user_group', User::GROUP_USERS)->first();

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                self::TEST_URL . "/emails/1/resend",
            );

        $response->assertStatus(403);
    }

    public function test_application_can_access_as_admin_but_wrong_id()
    {
        $lastestEmailLog = EmailLog::query()->orderBy('id', 'desc')->first();
        $emailLogIdTest = $lastestEmailLog ? $lastestEmailLog->id + 1 : 1;

        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "/emails/{$emailLogIdTest}/resend",
            );

        $response->assertStatus(400);
    }

    public function test_application_can_access_as_admin_with_success()
    {
        $response = $this->actingAs($this->admin)
            ->json(
                'PUT',
                self::TEST_URL . "/emails/1/resend",
            );

        $response->assertStatus(200);
    }

    public function createDataEmailLog()
    {
        $emailTemplate = EmailTemplate::where('id', 1)->first();

        EmailLog::create([
            'to' => fake()->email(),
            'subject' => $emailTemplate->subject,
            'template' => $emailTemplate->identifier,
            'body' => <<<HTML
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                .button { 
                                    background-color: #007bff; 
                                    color: white; 
                                    padding: 10px 20px; 
                                    text-decoration: none; 
                                }
                            </style>
                        </head>
                        <body>
                            <h2>Test</h2>
                        </body>
                        </html>
                        HTML,
            'job_uuid' => '1497a554-a43d-46e6-bd76-d62e388482f8',
            'job_status' => 1,
            'message_id' => 'wOxTOLDV8V79U3jbi26FEvonJTjDWn96DjtYD6Vn6OY',
            'message_status' => null,
            'message_response' => null,
            'error_message' => null,
        ]);

    }
}