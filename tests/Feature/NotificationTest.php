<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Database\Seeders\EmailTemplatesSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\BaseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminUserChanged;

class NotificationTest extends TestCase
{
    use Authorisation;
    use RefreshDatabase;
    use ActingAsKeycloakUser;

    public const TEST_URL = '/api/v1/users/1/notifications';

    private $user = null;


    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            EmailTemplatesSeeder::class,
            BaseDemoSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();

    }

    public function test_admin_user_changed_organisation()
    {
        Notification::fake();
        Notification::sendNow($this->user, new AdminUserChanged($this->user, ['test' => ['old' => 'Old','new' => 'New']]));

        Notification::assertSentTo(
            [$this->user],
            AdminUserChanged::class
        );
    }

    public function test_user_can_retrieve_notifications()
    {
        Notification::sendNow($this->user, new AdminUserChanged($this->user, ['test' => ['old' => 'Old','new' => 'New']]));

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200)
                 ->assertJson(['message' => 'success'])
                 ->assertJsonStructure([
                    'data' => [
                        'current_page',
                        'data' => [
                            '*' => [
                                'id',
                                'type',
                                'notifiable_type',
                                'notifiable_id',
                                'data',
                                'read_at',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                    ]]);
    }

    public function test_user_can_read_notifications()
    {
        Notification::sendNow($this->user, new AdminUserChanged($this->user, ['test' => ['old' => 'Old','new' => 'New']]));
        Notification::sendNow($this->user, new AdminUserChanged($this->user, ['test' => ['old' => 'New','new' => 'New2']]));

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200)
        ->assertJson(['message' => 'success'])
        ->assertJsonStructure([
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'notifiable_type',
                        'notifiable_id',
                        'data',
                        'read_at',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]
        ])
        ->assertJsonCount(2, 'data.data');

        $not1 = $response['data']['data'][0]['id'];
        $not2 = $response['data']['data'][1]['id'];

        # mark it as read
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PATCH',
            self::TEST_URL . '/' . $not1 . '/read'
        );

        $response->assertStatus(200)
        ->assertJson(['message' => 'Notification marked as read']);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '?status=read'
        );

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data')
                 ->assertJsonFragment(['id' => $not1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '?status=unread'
        );

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data')
                 ->assertJsonFragment(['id' => $not2]);

        # mark it as unread again
        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'PATCH',
            self::TEST_URL . '/' . $not1 . '/unread'
        );

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '?status=unread'
        );

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data.data');

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '?status=read'
        );

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data.data');

    }



}
