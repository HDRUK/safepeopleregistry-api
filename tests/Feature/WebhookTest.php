<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\CustodianWebhookReceiver;
use App\Models\WebhookEventTrigger;
use App\Models\Custodian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;

class WebhookTest extends TestCase
{
    use RefreshDatabase;
    use ActingAsKeycloakUser;
    use Authorisation;

    protected $user;
    protected const TEST_URL = '/api/v1/webhooks';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->custodian = Custodian::factory()->create();
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
            'url' => 'https://example.com/new-webhook',
            'webhook_event_id' => $newEventTrigger->id,
        ];

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->putJson(self::TEST_URL . "/receivers/{$this->custodian->id}/{$receiver->id}", $updateData);

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

    public function test_the_application_can_delete_webhook_receiver(): void
    {
        $receiver = CustodianWebhookReceiver::factory()->create(['custodian_id' => $this->custodian->id]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->deleteJson(self::TEST_URL . "/receivers/{$this->custodian->id}/{$receiver->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'success',
            'data' => null
        ]);

        $this->assertDatabaseMissing('custodian_webhook_receivers', ['id' => $receiver->id]);
    }
}
