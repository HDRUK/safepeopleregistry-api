<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailLog;
use App\Models\Custodian;
use App\Models\CustodianUser;
use Tests\Traits\Authorisation;
use App\Models\WebhookEventTrigger;
use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\CustodianWebhookReceiver;

class WebhookTest extends TestCase
{
    use ActingAsKeycloakUser;
    use Authorisation;

    protected $custodian;
    protected const TEST_URL = '/api/v1/webhooks';
    protected $eventTrigger = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = CustodianUser::where('id', 1)->first();
        $this->custodian = Custodian::where('id', $this->user->custodian_id)->first();
        $this->eventTrigger = WebhookEventTrigger::factory()->create();
    }

    public function test_the_application_can_get_all_webhook_receivers(): void
    {
        CustodianWebhookReceiver::factory()->count(3)->create();

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->getJson(self::TEST_URL . '/receivers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'custodian_id',
                    'url',
                    'webhook_event',
                    'created_at',
                    'updated_at',
                    'event_trigger' => [
                        'id',
                        'name',
                        'description'
                    ]
                ]
            ]
        ]);
    }

    public function test_the_application_can_get_webhook_receivers_by_custodian(): void
    {
        CustodianWebhookReceiver::factory()->count(3)->create(['custodian_id' => $this->custodian->id]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->getJson(self::TEST_URL . '/receivers/' . $this->custodian->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'custodian_id',
                    'url',
                    'webhook_event',
                    'created_at',
                    'updated_at',
                    'event_trigger' => [
                        'id',
                        'name',
                        'description'
                    ]
                ]
            ]
        ]);
    }

    public function test_the_application_cannot_get_webhook_receivers_by_custodian(): void
    {
        CustodianWebhookReceiver::factory()->count(3)->create(['custodian_id' => $this->custodian->id]);

        $latestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $latestCustodian ? $latestCustodian->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->getJson(self::TEST_URL . "/receivers/{$custodianIdTest}");

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_create_webhook_receiver(): void
    {
        $webhookData = [
            'custodian_id' => $this->custodian->id,
            'url' => 'https://example.com/webhook',
            'webhook_event_id' => $this->eventTrigger->id,
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->postJson(self::TEST_URL . '/receivers', $webhookData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'custodian_id',
                'url',
                'webhook_event',
                'created_at',
                'updated_at',
            ],
        ]);

        $this->assertDatabaseHas('custodian_webhook_receivers', [
            'custodian_id' => $webhookData['custodian_id'],
            'url' => $webhookData['url'],
            'webhook_event' => $webhookData['webhook_event_id'],
        ]);
    }

    public function test_the_application_can_update_webhook_receiver(): void
    {
        $receiver = CustodianWebhookReceiver::factory()->create(['custodian_id' => $this->custodian->id]);
        $newEventTrigger = WebhookEventTrigger::factory()->create();

        $updateData = [
            'id' => $receiver->id,
            'url' => 'https://example.com/new-webhook',
            'webhook_event_id' => $newEventTrigger->id,
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->putJson(self::TEST_URL . "/receivers/{$this->custodian->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'success',
            'data' => null
        ]);

        $this->assertDatabaseHas('custodian_webhook_receivers', [
            'id' => $receiver->id,
            'custodian_id' => $this->custodian->id,
            'url' => $updateData['url'],
            'webhook_event' => $updateData['webhook_event_id'],
        ]);
    }

    public function test_the_application_cannot_update_webhook_receiver_by_custodian_id(): void
    {
        $receiver = CustodianWebhookReceiver::factory()->create(['custodian_id' => $this->custodian->id]);
        $newEventTrigger = WebhookEventTrigger::factory()->create();

        $updateData = [
            'id' => $receiver->id,
            'url' => 'https://example.com/new-webhook',
            'webhook_event_id' => $newEventTrigger->id,
        ];

        $latestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $latestCustodian ? $latestCustodian->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->putJson(self::TEST_URL . "/receivers/{$custodianIdTest}", $updateData);

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_the_application_can_delete_webhook_receiver(): void
    {
        $receiver = CustodianWebhookReceiver::factory()->create(['custodian_id' => $this->custodian->id]);

        $deleteData = [
            'id' => $receiver->id,
        ];
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->deleteJson(self::TEST_URL . "/receivers/{$this->custodian->id}", $deleteData);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'success',
            'data' => null
        ]);

        $this->assertDatabaseMissing('custodian_webhook_receivers', ['id' => $receiver->id]);
    }

    public function test_the_application_cannot_delete_webhook_receiver(): void
    {
        $receiver = CustodianWebhookReceiver::factory()->create(['custodian_id' => $this->custodian->id]);

        $deleteData = [
            'id' => $receiver->id,
        ];

        $latestCustodian = Custodian::query()->orderBy('id', 'desc')->first();
        $custodianIdTest = $latestCustodian ? $latestCustodian->id + 1 : 1;

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->deleteJson(self::TEST_URL . "/receivers/{$custodianIdTest}", $deleteData);

        $response->assertStatus(400);
        $message = $response->decodeResponseJson()['message'];
        $this->assertEquals('Invalid argument(s)', $message);
    }

    public function test_delivered_event_updates_email_log_successfully()
    {
        $jobUuid = 'test-uuid-123';
        
        $emailLog = EmailLog::create([
            'to' => fake()->email(),
            'subject' => 'test',
            'template' => 'test',
            'body' => '<html>test</html>',
            'job_uuid' => $jobUuid,
            'job_status' => 0,
            'message_status' => null,
            'message_response' => null,
        ]);

        $payload = [
            [
                'event' => 'delivered',
                'job_uuid' => $jobUuid,
                'sg_message_id' => 'sg-message-123',
                'timestamp' => now()->timestamp,
            ]
        ];

        $response = $this->postJson('/api/v1/webhooks/sendgrid', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'success']);

        $emailLog->refresh();
        
        $this->assertEquals(1, $emailLog->job_status);
        $this->assertEquals('delivered', $emailLog->message_status);
        $this->assertNotNull($emailLog->message_response);
    }
}
