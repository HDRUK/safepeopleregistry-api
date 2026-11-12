<?php

namespace Tests\Feature;

use App\Enums\CustodianRole;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    private function migration(): object
    {
        return require database_path('migrations/2026_11_12_130000_add_custodian_read_only_enabled_feature.php');
    }
}
