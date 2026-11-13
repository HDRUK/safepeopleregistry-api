<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Exceptions\CustodianRoleConflictException;
use App\Models\Custodian;
use App\Models\CustodianModelConfig;
use App\Models\CustodianUser;
use App\Models\CustodianUserHasPermission;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use KeycloakGuard\ActingAsKeycloakUser;
use Tests\TestCase;
use Tests\Traits\Authorisation;
use Tests\Traits\CustodianActors;

class CustodianRoleExclusivityTest extends TestCase
{
    use Authorisation;
    use ActingAsKeycloakUser;
    use CustodianActors;

    public const TEST_URL = '/api/v1/custodian_users';

    private User $custodianAdmin;

    private int $custodianId;

    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers(true);

        $this->custodianId = CustodianUser::findOrFail($this->custodian_admin->custodian_user_id)->custodian_id;
        [$this->custodianAdmin] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Admin->value);
    }

    public function test_creating_a_custodian_user_with_two_roles_is_rejected(): void
    {
        $email = fake()->unique()->safeEmail();

        $response = $this->actingAsKeycloakUser($this->custodianAdmin, $this->getMockedKeycloakPayload())
            ->actingAs($this->custodianAdmin)
            ->json('POST', self::TEST_URL, [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => $email,
                'permissions' => [
                    $this->permissionId(CustodianRole::Admin),
                    $this->permissionId(CustodianRole::Approver),
                ],
            ]);

        $response->assertStatus(422)
            ->assertJson(['message' => CustodianRoleConflictException::MESSAGE]);
        $this->assertDatabaseMissing('custodian_users', ['email' => $email]);
    }

    public function test_updating_a_custodian_user_to_two_roles_is_rejected_and_keeps_their_role(): void
    {
        [, $approver] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);

        $response = $this->actingAsKeycloakUser($this->custodianAdmin, $this->getMockedKeycloakPayload())
            ->actingAs($this->custodianAdmin)
            ->json('PUT', self::TEST_URL . '/' . $approver->id, [
                'permissions' => [
                    $this->permissionId(CustodianRole::Approver),
                    $this->permissionId(CustodianRole::ReadOnly),
                ],
            ]);

        $response->assertStatus(422)
            ->assertJson(['message' => CustodianRoleConflictException::MESSAGE]);

        $approver = $approver->fresh();
        $this->assertTrue($approver->hasPermission(CustodianRole::Approver->value));
        $this->assertFalse($approver->hasPermission(CustodianRole::ReadOnly->value));
    }

    public function test_a_custodian_users_role_can_be_changed(): void
    {
        [, $approver] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);

        // Model events are off in tests by default; turn them on so the role-exclusivity hook runs
        $this->enableObservers();

        $response = $this->actingAsKeycloakUser($this->custodianAdmin, $this->getMockedKeycloakPayload())
            ->actingAs($this->custodianAdmin)
            ->json('PUT', self::TEST_URL . '/' . $approver->id, [
                'permissions' => [
                    $this->permissionId(CustodianRole::ReadOnly),
                ],
            ]);

        $response->assertStatus(200);

        $approver = $approver->fresh();
        $this->assertTrue($approver->hasPermission(CustodianRole::ReadOnly->value));
        $this->assertFalse($approver->hasPermission(CustodianRole::Approver->value));
    }

    public function test_changing_a_role_does_not_return_the_old_permissions(): void
    {
        // An approver editing someone loads the target's permissions in the policy, before update replaces them
        [$approverActor] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);
        [, $colleague] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);

        $this->actingAsKeycloakUser($approverActor, $this->getMockedKeycloakPayload())
            ->actingAs($approverActor)
            ->json('PUT', self::TEST_URL . '/' . $colleague->id, [
                'permissions' => [$this->permissionId(CustodianRole::ReadOnly)],
            ])
            ->assertStatus(200)
            ->assertJsonMissingPath('data.user_permissions');

        $this->assertTrue($colleague->fresh()->isReadOnly());
    }

    public function test_a_second_role_cannot_be_added_directly(): void
    {
        [, $approver] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);

        // Model events are off in tests by default; turn them on so the role-exclusivity hook runs
        $this->enableObservers();

        $this->expectException(CustodianRoleConflictException::class);

        CustodianUserHasPermission::create([
            'custodian_user_id' => $approver->id,
            'permission_id' => $this->permissionId(CustodianRole::Admin),
        ]);
    }

    public function test_a_non_role_permission_can_sit_alongside_a_role(): void
    {
        [, $approver] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);

        // Model events are off in tests by default; turn them on so the role-exclusivity hook runs
        $this->enableObservers();

        CustodianUserHasPermission::create([
            'custodian_user_id' => $approver->id,
            'permission_id' => Permission::where('name', 'CUSTODIAN_KEYCARD_CREATE')->firstOrFail()->id,
        ]);

        $this->assertSame(2, CustodianUserHasPermission::where('custodian_user_id', $approver->id)->count());
        $this->assertTrue($approver->fresh()->hasPermission(CustodianRole::Approver->value));
    }

    public function test_read_only_wins_when_bad_data_also_gives_a_user_admin(): void
    {
        // Observers are off here, so the exclusivity check doesn't stop the second role: bad data on purpose
        [$both, $bothCustodianUser] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::ReadOnly->value);
        CustodianUserHasPermission::create([
            'custodian_user_id' => $bothCustodianUser->id,
            'permission_id' => $this->permissionId(CustodianRole::Admin),
        ]);
        $this->assertTrue($bothCustodianUser->fresh()->hasPermission(CustodianRole::Admin->value));

        [, $colleague] = $this->makeCustodianUserActor($this->custodianId, CustodianRole::Approver->value);
        $config = CustodianModelConfig::factory()->create([
            'custodian_id' => $this->custodianId,
            'decision_model_id' => 1,
            'active' => 1,
        ]);

        $abilities = [
            'create custodian user' => ['create', CustodianUser::class],
            'update custodian user' => ['update', $colleague],
            'create config' => ['create', [CustodianModelConfig::class, $this->custodianId]],
            'update config' => ['update', $config],
        ];

        foreach ($abilities as $label => [$ability, $arguments]) {
            // A plain admin passes, so a refusal below comes from the read-only role
            $this->assertTrue(Gate::forUser($this->custodianAdmin)->allows($ability, $arguments), "Admin: {$label}");
            $this->assertTrue(Gate::forUser($both)->denies($ability, $arguments), "Admin + read-only: {$label}");
        }
    }

    public function test_inviting_a_custodian_whose_contact_already_holds_another_role_is_a_422(): void
    {
        // The invite reuses the custodian user with the contact email and makes them ADMIN
        $contact = CustodianUser::factory()->create(['custodian_id' => $this->custodianId]);
        CustodianUserHasPermission::create([
            'custodian_user_id' => $contact->id,
            'permission_id' => $this->permissionId(CustodianRole::Approver),
        ]);
        $custodian = Custodian::factory()->create(['contact_email' => $contact->email]);

        // Every observer runs once they're on; the user observer would call Keycloak
        Http::fake();
        $this->enableObservers();

        $this->actingAsKeycloakUser($this->admin, $this->getMockedKeycloakPayload())
            ->actingAs($this->admin)
            ->json('POST', "/api/v1/custodians/{$custodian->id}/invite")
            ->assertStatus(422)
            ->assertJson(['message' => CustodianRoleConflictException::MESSAGE]);

        $this->assertTrue($contact->fresh()->hasPermission(CustodianRole::Approver->value));
        $this->assertFalse($contact->fresh()->hasPermission(CustodianRole::Admin->value));
    }

    private function permissionId(CustodianRole $role): int
    {
        return Permission::where('name', $role->value)->firstOrFail()->id;
    }
}
