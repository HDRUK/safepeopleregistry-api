<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;
use Tests\TestCase;

/**
 * CustodianReadOnlyEnabled is the kill switch for read-only custodian access. It's on by default.
 * A missing row reads as off in Pennant, so both ways the row is created are tested.
 */
class CustodianReadOnlyKillSwitchTest extends TestCase
{
    private const FLAG = 'CustodianReadOnlyEnabled';

    private function migration(): object
    {
        return require database_path('migrations/2026_11_12_130000_add_custodian_read_only_enabled_feature.php');
    }

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
}
