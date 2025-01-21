<?php

namespace Tests\Feature;

use KeycloakGuard\ActingAsKeycloakUser;
use App\Models\User;
use Database\Seeders\EmailTemplatesSeeder;
use Database\Seeders\CustodianSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\BaseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminUserChangedOrganisation;

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
            CustodianSeeder::class,
            EmailTemplatesSeeder::class,
            BaseDemoSeeder::class,
        ]);

        $this->user = User::where('id', 1)->first();

    }

    public function test_admin_user_changed_organisation()
    {
        Notification::fake();
        Notification::sendNow($this->user, new AdminUserChangedOrganisation("A user has changed their details"));

        Notification::assertSentTo(
            [$this->user],
            AdminUserChangedOrganisation::class
        );
    }

    public function test_user_can_retrieve_notifications()
    {
        Notification::sendNow($this->user, new AdminUserChangedOrganisation("A user has changed their details"));

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200)
                 ->assertJson(['message' => 'success'])
                 ->assertJsonStructure(['data' => [['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at']]]);
    }

    public function test_user_can_read_notifications()
    {
        Notification::sendNow($this->user, new AdminUserChangedOrganisation("Notificiation 1"));
        Notification::sendNow($this->user, new AdminUserChangedOrganisation("Notificiation 1"));

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
            ->json(
                'GET',
                self::TEST_URL
            );

        $response->assertStatus(200)
                 ->assertJson(['message' => 'success'])
                 ->assertJsonStructure(['data' => [['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at']]])
                 ->assertJsonCount(2, 'data');

        $not1 = $response['data'][0]['id'];
        $not2 = $response['data'][1]['id'];

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
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['id' => $not1]);

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '?status=unread'
        );

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
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
                 ->assertJsonCount(2, 'data');

        $response = $this->actingAsKeycloakUser($this->user, $this->getMockedKeycloakPayload())
        ->json(
            'GET',
            self::TEST_URL . '?status=read'
        );

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data');



    }



}
