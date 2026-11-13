<?php

namespace Tests\Feature;

use App\Enums\ActionLogType;
use App\Enums\CustodianRole;
use App\Jobs\SendEmailJob;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\CustodianModelConfig;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\PendingInvite;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

/**
 * CUSTODIAN_READ_ONLY users can view their Custodian's data but not change it. They can still
 * update their own profile, notifications and personal checklist.
 */
class CustodianReadOnlyTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CustodianActors;

    private Custodian $custodian;

    private User $readOnly;

    private User $approver;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers(true);

        $this->custodian = Custodian::findOrFail(
            CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id
        );

        [$this->readOnly] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::ReadOnly->value);
        [$this->approver] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::Approver->value);
    }

    public function test_inviting_a_read_only_user_sends_the_read_only_invite_email(): void
    {
        $invitee = $this->makeInvitee([CustodianRole::ReadOnly->value]);

        $this->as($this->custodian_admin)->json('POST', "/api/v1/custodian_users/invite/{$invitee->id}")
            ->assertStatus(201);

        $this->assertInviteEmailSent('custodian_invite_read_only');
    }

    public function test_the_invite_email_follows_the_role_not_the_first_permission_row(): void
    {
        // A KEYCARD permission stored before the role must not decide which email is sent
        $invitee = $this->makeInvitee(['CUSTODIAN_KEYCARD_CREATE', CustodianRole::ReadOnly->value]);

        $this->as($this->custodian_admin)->json('POST', "/api/v1/custodian_users/invite/{$invitee->id}")
            ->assertStatus(201);

        $this->assertInviteEmailSent('custodian_invite_read_only');
    }

    public function test_bulk_store_accepts_read_only(): void
    {
        $this->bulkStore('read-only-bulk@example.com', CustodianRole::ReadOnly->value)
            ->assertStatus(201);

        $created = CustodianUser::where('email', 'read-only-bulk@example.com')
            ->where('custodian_id', $this->custodian->id)
            ->firstOrFail();

        $this->assertTrue($created->isReadOnly());
        $this->assertFalse($created->hasPermission(CustodianRole::Admin->value));
        $this->assertFalse($created->hasPermission(CustodianRole::Approver->value));
    }

    public function test_bulk_store_still_rejects_permissions_that_are_not_roles(): void
    {
        $this->bulkStore('keycard-bulk@example.com', 'CUSTODIAN_KEYCARD_CREATE')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'permission must be one of CUSTODIAN_ADMIN, CUSTODIAN_APPROVER or CUSTODIAN_READ_ONLY',
            ]);

        $this->assertDatabaseMissing('custodian_users', ['email' => 'keycard-bulk@example.com']);
    }

    public function test_read_only_cannot_update_the_custodian(): void
    {
        $this->as($this->readOnly)->json('PUT', "/api/v1/custodians/{$this->custodian->id}", [])
            ->assertStatus(403);

        $this->as($this->approver)->json('PUT', "/api/v1/custodians/{$this->custodian->id}", [])
            ->assertStatus(200);
    }

    public function test_read_only_cannot_invite_for_the_custodian(): void
    {
        $invitesBefore = PendingInvite::count();

        $this->as($this->readOnly)->json('POST', "/api/v1/custodians/{$this->custodian->id}/invite")
            ->assertStatus(403);
        $this->assertSame($invitesBefore, PendingInvite::count());

        $this->as($this->approver)->json('POST', "/api/v1/custodians/{$this->custodian->id}/invite")
            ->assertStatus(201);
        $this->assertGreaterThan($invitesBefore, PendingInvite::count());
    }

    public function test_read_only_can_list_access_revocations_but_cannot_confirm_or_approve(): void
    {
        $this->as($this->readOnly)->json('GET', "/api/v1/custodians/{$this->custodian->id}/access_revocations")
            ->assertStatus(200);

        // Confirming a revocation and the custodian approval routes all check CustodianPolicy::update
        $this->assertTrue(Gate::forUser($this->readOnly)->denies('update', $this->custodian));
        $this->assertTrue(Gate::forUser($this->approver)->allows('update', $this->custodian));
    }

    public function test_access_revocations_are_not_visible_to_another_custodians_users(): void
    {
        [$outsider] = $this->makeCustodianUserActor(Custodian::factory()->create()->id, CustodianRole::Admin->value);

        $this->as($outsider)->json('GET', "/api/v1/custodians/{$this->custodian->id}/access_revocations")
            ->assertStatus(403);
    }

    public function test_read_only_cannot_tick_the_custodian_checklist(): void
    {
        $log = ActionLog::create([
            'entity_type' => ActionLogType::CUSTODIAN,
            'entity_id' => $this->custodian->id,
            'action' => 'read_only_test_custodian_action',
        ]);

        $this->as($this->readOnly)->json('PUT', "/api/v1/action_log/{$log->id}", ['complete' => true])
            ->assertStatus(403);
        $this->assertNull($log->fresh()->completed_at);

        $this->as($this->approver)->json('PUT', "/api/v1/action_log/{$log->id}", ['complete' => true])
            ->assertStatus(200);
        $this->assertNotNull($log->fresh()->completed_at);
    }

    public function test_read_only_can_tick_their_own_checklist(): void
    {
        $log = ActionLog::create([
            'entity_type' => ActionLogType::USER,
            'entity_id' => $this->readOnly->id,
            'action' => 'read_only_test_user_action',
        ]);

        $this->as($this->readOnly)->json('PUT', "/api/v1/action_log/{$log->id}", ['complete' => true])
            ->assertStatus(200);
        $this->assertNotNull($log->fresh()->completed_at);
    }

    public function test_read_only_cannot_invite_users(): void
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'read-only-invite-test@example.com',
        ];

        $this->as($this->readOnly)->json('POST', '/api/v1/users/invite', $payload)->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);

        $this->as($this->approver)->json('POST', '/api/v1/users/invite', $payload)->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => $payload['email']]);
    }

    public function test_read_only_cannot_update_a_peer_but_can_update_themselves(): void
    {
        $this->as($this->readOnly)->json('PUT', "/api/v1/users/{$this->approver->id}", ['first_name' => 'Changed'])
            ->assertStatus(403);
        $this->assertNotSame('Changed', $this->approver->fresh()->first_name);

        $this->as($this->readOnly)->json('PUT', "/api/v1/users/{$this->readOnly->id}", ['first_name' => 'Updated'])
            ->assertStatus(200);
        $this->assertSame('Updated', $this->readOnly->fresh()->first_name);
    }

    public function test_an_approver_can_still_update_a_peer(): void
    {
        $this->as($this->approver)->json('PUT', "/api/v1/users/{$this->readOnly->id}", ['first_name' => 'Renamed'])
            ->assertStatus(200);
        $this->assertSame('Renamed', $this->readOnly->fresh()->first_name);
    }

    public function test_read_only_can_mark_their_notifications_read(): void
    {
        $this->as($this->readOnly)->json('PATCH', "/api/v1/users/{$this->readOnly->id}/notifications/read")
            ->assertStatus(200);
    }

    public function test_read_only_cannot_create_custodian_users(): void
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'read-only-custodian-user-test@example.com',
            'permissions' => [Permission::where('name', CustodianRole::Approver->value)->firstOrFail()->id],
        ];

        $this->as($this->readOnly)->json('POST', '/api/v1/custodian_users', $payload)->assertStatus(403);
        $this->assertDatabaseMissing('custodian_users', ['email' => $payload['email']]);

        $this->as($this->approver)->json('POST', '/api/v1/custodian_users', $payload)->assertStatus(201);
        $this->assertDatabaseHas('custodian_users', ['email' => $payload['email']]);
    }

    public function test_read_only_cannot_update_custodian_config(): void
    {
        $config = CustodianModelConfig::factory()->create([
            'custodian_id' => $this->custodian->id,
            'decision_model_id' => 1,
            'active' => 1,
        ]);

        $this->as($this->readOnly)->json('PUT', "/api/v1/custodian_config/{$config->id}", ['active' => 0])
            ->assertStatus(403);
        $this->assertSame(1, (int) $config->fresh()->active);

        // Only a CUSTODIAN_ADMIN may change config
        [$admin] = $this->makeCustodianUserActor($this->custodian->id, CustodianRole::Admin->value);
        $this->as($admin)->json('PUT', "/api/v1/custodian_config/{$config->id}", ['active' => 0])
            ->assertStatus(200);
        $this->assertSame(0, (int) $config->fresh()->active);
    }

    /**
     * A custodian user who hasn't signed up yet, holding the given permissions in the order given.
     *
     * @param  array<int, string>  $permissionNames
     */
    private function makeInvitee(array $permissionNames): CustodianUser
    {
        $invitee = CustodianUser::factory()->create(['custodian_id' => $this->custodian->id]);

        foreach ($permissionNames as $name) {
            CustodianUserHasPermission::create([
                'custodian_user_id' => $invitee->id,
                'permission_id' => Permission::where('name', $name)->firstOrFail()->id,
            ]);
        }

        return $invitee;
    }

    /**
     * The bulk route is server-to-server: the Custodian is found from the x-client-id header, not a
     * signed-in user. Middleware is off in tests, so the signed payload check doesn't run.
     */
    private function bulkStore(string $email, string $permission): TestResponse
    {
        return $this->json('POST', '/api/v1/custodian_users/bulk', [
            'users' => [[
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => $email,
                'permission' => $permission,
            ]],
        ], ['x-client-id' => $this->custodian->client_id]);
    }

    private function assertInviteEmailSent(string $identifier): void
    {
        // The job's template is private, so read it from inside the job. EmailTemplate declares a
        // public $identifier property that hides the stored value, so use getAttribute().
        Queue::assertPushed(
            SendEmailJob::class,
            fn (SendEmailJob $job) => (fn () => $this->template?->getAttribute('identifier'))->call($job) === $identifier
        );
    }

    private function as(User $user): self
    {
        return $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())->actingAs($user);
    }
}
