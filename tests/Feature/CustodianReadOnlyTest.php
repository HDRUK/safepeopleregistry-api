<?php

namespace Tests\Feature;

use App\Enums\ActionLogType;
use App\Enums\CustodianRole;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\CustodianModelConfig;
use App\Models\CustodianUser;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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

    public function test_read_only_cannot_update_the_custodian(): void
    {
        $this->as($this->readOnly)->json('PUT', "/api/v1/custodians/{$this->custodian->id}", [])
            ->assertStatus(403);

        $this->as($this->approver)->json('PUT', "/api/v1/custodians/{$this->custodian->id}", [])
            ->assertStatus(200);
    }

    public function test_read_only_cannot_invite_for_the_custodian(): void
    {
        $this->as($this->readOnly)->json('POST', "/api/v1/custodians/{$this->custodian->id}/invite")
            ->assertStatus(403);
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
        $this->as($this->readOnly)->json('POST', '/api/v1/users/invite', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
        ])->assertStatus(403);
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
        $this->as($this->readOnly)->json('POST', '/api/v1/custodian_users', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
        ])->assertStatus(403);
    }

    public function test_read_only_cannot_update_custodian_config(): void
    {
        $config = CustodianModelConfig::factory()->create([
            'custodian_id' => $this->custodian->id,
            'decision_model_id' => 1,
            'active' => 1,
        ]);

        $this->as($this->readOnly)->json('PUT', "/api/v1/custodian_config/{$config->id}", [])
            ->assertStatus(403);
    }

    private function as(User $user): self
    {
        return $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())->actingAs($user);
    }
}
