<?php

namespace Tests\Feature;

use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\ValidationCheck;
use App\Models\ValidationLog;
use Tests\TestCase;

class SroDeclarationVerifiedValidationCheckTest extends TestCase
{
    private function migration(): object
    {
        return require database_path('migrations/2026_10_28_120000_add_sro_declaration_verified_validation_check.php');
    }

    private function checks()
    {
        return ValidationCheck::where('name', 'sro_declaration_verified')
            ->where('applies_to', Organisation::class);
    }

    public function test_it_is_a_default_organisation_check(): void
    {
        $this->assertContains('sro_declaration_verified', array_column(Organisation::defaultValidationChecks(), 'name'));
    }

    public function test_it_adds_the_check_as_the_default_and_for_every_custodian(): void
    {
        $this->migration()->down();
        $this->withTemporaryObservers(fn () => $this->migration()->up());

        $this->assertSame(1, $this->checks()->clone()->whereNull('custodian_id')->count());
        $this->assertEqualsCanonicalizing(
            Custodian::pluck('id')->all(),
            $this->checks()->clone()->whereNotNull('custodian_id')->pluck('custodian_id')->all()
        );
    }

    public function test_each_custodian_gets_a_pending_check_for_every_organisation(): void
    {
        $this->migration()->down();
        $this->withTemporaryObservers(fn () => $this->migration()->up());

        $custodianCheckIds = $this->checks()->clone()->whereNotNull('custodian_id')->pluck('id');

        $this->assertSame(
            $custodianCheckIds->count() * Organisation::count(),
            ValidationLog::whereIn('validation_check_id', $custodianCheckIds)->whereNull('completed_at')->count()
        );
    }

    public function test_running_it_again_adds_nothing(): void
    {
        $this->withTemporaryObservers(fn () => $this->migration()->up());
        $count = $this->checks()->count();

        $this->withTemporaryObservers(fn () => $this->migration()->up());

        $this->assertSame($count, $this->checks()->count());
    }

    public function test_rolling_back_removes_the_checks_and_their_logs(): void
    {
        $this->withTemporaryObservers(fn () => $this->migration()->up());
        $checkIds = $this->checks()->pluck('id');

        $this->migration()->down();

        $this->assertSame(0, $this->checks()->count());
        $this->assertSame(0, ValidationLog::whereIn('validation_check_id', $checkIds)->count());
    }
}
