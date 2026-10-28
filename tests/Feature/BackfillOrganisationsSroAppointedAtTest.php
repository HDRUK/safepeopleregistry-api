<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackfillOrganisationsSroAppointedAtTest extends TestCase
{
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
    }

    private function createSro(): User
    {
        return User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => $this->organisation->id,
            'is_sro' => true,
            'is_delegate' => 0,
            'unclaimed' => 0,
        ]);
    }

    private function logActivity(User $user, string $event, array $attributes, string $at): void
    {
        DB::table(config('activitylog.table_name'))->insert([
            'log_name' => 'user',
            'description' => $event,
            'event' => $event,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'properties' => json_encode(['attributes' => $attributes]),
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    private function backfill(): ?string
    {
        $migration = require database_path('migrations/2026_10_27_120002_backfill_organisations_sro_appointed_at.php');
        $migration->up();

        return $this->organisation->fresh()->sro_appointed_at?->toDateTimeString();
    }

    public function test_it_uses_when_a_delegate_was_appointed_sro(): void
    {
        $sro = $this->createSro();
        $this->logActivity($sro, 'created', ['is_sro' => false, 'unclaimed' => false, 'organisation_id' => $this->organisation->id], '2026-01-01 09:00:00');
        $this->logActivity($sro, 'updated', ['is_sro' => true], '2026-03-01 09:00:00');
        $this->logActivity($sro, 'updated', ['first_name' => 'Jane'], '2026-04-01 09:00:00');

        $this->assertSame('2026-03-01 09:00:00', $this->backfill());
    }

    public function test_it_uses_when_an_invited_sro_claimed(): void
    {
        $sro = $this->createSro();
        $this->logActivity($sro, 'created', ['is_sro' => true, 'unclaimed' => true, 'organisation_id' => $this->organisation->id], '2026-01-01 09:00:00');
        $this->logActivity($sro, 'updated', ['unclaimed' => false], '2026-02-01 09:00:00');

        $this->assertSame('2026-02-01 09:00:00', $this->backfill());
    }

    public function test_it_uses_the_latest_time_they_became_sro(): void
    {
        $sro = $this->createSro();
        $this->logActivity($sro, 'created', ['is_sro' => true, 'unclaimed' => false], '2026-01-01 09:00:00');
        $this->logActivity($sro, 'updated', ['is_sro' => false], '2026-02-01 09:00:00');
        $this->logActivity($sro, 'updated', ['is_sro' => true], '2026-03-01 09:00:00');

        $this->assertSame('2026-03-01 09:00:00', $this->backfill());
    }

    public function test_it_leaves_it_null_when_the_log_cannot_account_for_the_sro(): void
    {
        $sro = $this->createSro();
        // Invited before logging began: whether they were already the SRO is unknown.
        $this->logActivity($sro, 'updated', ['unclaimed' => false], '2026-02-01 09:00:00');

        $this->assertNull($this->backfill());
    }

    public function test_it_leaves_it_null_without_any_log(): void
    {
        $this->createSro();

        $this->assertNull($this->backfill());
    }
}
