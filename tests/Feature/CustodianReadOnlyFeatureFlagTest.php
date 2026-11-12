<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Jobs\SendEmailJob;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\PendingInvite;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use KeycloakGuard\ActingAsKeycloakUser;
use Laravel\Pennant\Feature;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

/**
 * CustodianReadOnlyEnabled is the feature flag for read-only custodian access. It's on by default.
 * A missing row reads as off in Pennant, so both ways the row is created are tested.
 *
 * Like the other flags, the front end reads it from GET /v1/features and shows read-only users a
 * "contact support" notice while it's off. Writes stay blocked for read-only users whatever the flag says.
 */
class CustodianReadOnlyFeatureFlagTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CustodianActors;

    private const FLAG = 'CustodianReadOnlyEnabled';

    private const DISABLED_MESSAGE = 'Read-only access is not enabled';

    public function test_the_flag_is_on_in_a_seeded_database(): void
    {
        // The test database is migrated and then seeded; FeatureSeeder truncates features,
        // so this checks the FeatureSeeder entry
        Feature::flushCache();

        $this->assertTrue(Feature::active(self::FLAG));
    }

    public function test_the_migration_adds_the_flag_switched_on(): void
    {
        DB::table('features')->where('name', self::FLAG)->delete();
        Feature::flushCache();
        $this->assertFalse(Feature::active(self::FLAG), 'A missing row should read as off');

        $this->migration()->up();
        Feature::flushCache();

        $this->assertTrue(Feature::active(self::FLAG));
        $this->assertSame(1, DB::table('features')->where('name', self::FLAG)->count());
    }

    public function test_rolling_back_removes_the_flag(): void
    {
        $this->migration()->up();
        $this->migration()->down();
        Feature::flushCache();

        $this->assertSame(0, DB::table('features')->where('name', self::FLAG)->count());
        $this->assertFalse(Feature::active(self::FLAG));
    }

    public function test_writes_stay_blocked_for_read_only_users_while_the_flag_is_off(): void
    {
        $this->withUsers(true);
        $custodian = Custodian::findOrFail(
            CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id
        );
        /** @var User $readOnly */
        [$readOnly] = $this->makeCustodianUserActor($custodian->id, CustodianRole::ReadOnly->value);

        Feature::deactivate(self::FLAG);

        $this->actingAsKeycloakUser($readOnly, $this->getMockedKeycloakPayload())
            ->actingAs($readOnly)
            ->json('PUT', "/api/v1/custodians/{$custodian->id}", [])
            ->assertStatus(403);
    }

    public function test_store_refuses_read_only_while_the_flag_is_off(): void
    {
        $this->withUsers(true);
        $payload = fn (string $email) => [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => $email,
            'permissions' => [$this->permissionId(CustodianRole::ReadOnly)],
        ];

        Feature::deactivate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('POST', '/api/v1/custodian_users', $payload('flag-off-store@example.com'))
            ->assertStatus(422)
            ->assertJson(['message' => self::DISABLED_MESSAGE]);
        $this->assertDatabaseMissing('custodian_users', ['email' => 'flag-off-store@example.com']);

        Feature::activate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('POST', '/api/v1/custodian_users', $payload('flag-on-store@example.com'))
            ->assertStatus(201);
    }

    public function test_update_refuses_making_someone_read_only_while_the_flag_is_off(): void
    {
        $this->withUsers(true);
        [, $approver] = $this->makeCustodianUserActor($this->adminsCustodianId(), CustodianRole::Approver->value);
        $payload = ['permissions' => [$this->permissionId(CustodianRole::ReadOnly)]];

        Feature::deactivate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('PUT', "/api/v1/custodian_users/{$approver->id}", $payload)
            ->assertStatus(422)
            ->assertJson(['message' => self::DISABLED_MESSAGE]);
        $this->assertTrue($approver->fresh()->hasPermission(CustodianRole::Approver->value));
        $this->assertFalse($approver->fresh()->isReadOnly());

        Feature::activate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('PUT', "/api/v1/custodian_users/{$approver->id}", $payload)
            ->assertStatus(200);
        $this->assertTrue($approver->fresh()->isReadOnly());
    }

    public function test_update_still_edits_an_existing_read_only_user_while_the_flag_is_off(): void
    {
        // The front end may send the full permissions list on every edit; keeping a role isn't assigning it
        $this->withUsers(true);
        [, $readOnly] = $this->makeCustodianUserActor($this->adminsCustodianId(), CustodianRole::ReadOnly->value);

        Feature::deactivate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('PUT', "/api/v1/custodian_users/{$readOnly->id}", [
                'first_name' => 'Renamed',
                'permissions' => [$this->permissionId(CustodianRole::ReadOnly)],
            ])
            ->assertStatus(200);

        $this->assertSame('Renamed', $readOnly->fresh()->first_name);
        $this->assertTrue($readOnly->fresh()->isReadOnly());
    }

    public function test_bulk_store_refuses_read_only_while_the_flag_is_off(): void
    {
        $custodian = Custodian::factory()->create();
        $bulk = fn (string $email) => $this->json('POST', '/api/v1/custodian_users/bulk', [
            'users' => [[
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => $email,
                'permission' => CustodianRole::ReadOnly->value,
            ]],
        ], ['x-client-id' => $custodian->client_id]);

        Feature::deactivate(self::FLAG);
        $bulk('flag-off-bulk@example.com')
            ->assertStatus(422)
            ->assertJson(['message' => self::DISABLED_MESSAGE]);
        $this->assertDatabaseMissing('custodian_users', ['email' => 'flag-off-bulk@example.com']);

        Feature::activate(self::FLAG);
        $bulk('flag-on-bulk@example.com')->assertStatus(201);
    }

    public function test_invite_refuses_read_only_users_while_the_flag_is_off(): void
    {
        $this->withUsers(true);
        $invitee = CustodianUser::factory()->create(['custodian_id' => $this->adminsCustodianId()]);
        CustodianUserHasPermission::create([
            'custodian_user_id' => $invitee->id,
            'permission_id' => $this->permissionId(CustodianRole::ReadOnly),
        ]);
        $invitesBefore = PendingInvite::count();

        Feature::deactivate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('POST', "/api/v1/custodian_users/invite/{$invitee->id}")
            ->assertStatus(422)
            ->assertJson(['message' => self::DISABLED_MESSAGE]);
        Queue::assertNotPushed(SendEmailJob::class);
        $this->assertSame($invitesBefore, PendingInvite::count());
        $this->assertDatabaseMissing('users', ['custodian_user_id' => $invitee->id]);

        Feature::activate(self::FLAG);
        $this->asUser($this->custodian_admin)
            ->json('POST', "/api/v1/custodian_users/invite/{$invitee->id}")
            ->assertStatus(201);
        Queue::assertPushed(SendEmailJob::class);
    }

    private function permissionId(CustodianRole $role): int
    {
        return Permission::where('name', $role->value)->firstOrFail()->id;
    }

    private function adminsCustodianId(): int
    {
        return CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id;
    }

    private function asUser(User $user): self
    {
        return $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())->actingAs($user);
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_12_130000_add_custodian_read_only_enabled_feature.php');
    }
}
