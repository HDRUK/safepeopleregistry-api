<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\ValidationCheck;
use Tests\TestCase;

class UpdateSroIdentityVerifiedDescriptionTest extends TestCase
{
    private const OLD_DESCRIPTION = 'Contact details tab: Verify SRO identity';
    private const NEW_DESCRIPTION = 'Contact details tab: Verify SRO identity and check SRO declaration';

    private function migration(): object
    {
        return require database_path('migrations/2026_10_28_120000_update_sro_identity_verified_validation_check_description.php');
    }

    private function createCheck(string $description): ValidationCheck
    {
        return ValidationCheck::create([
            'name' => 'sro_identity_verified',
            'description' => $description,
            'applies_to' => Organisation::class,
            'enabled' => 1,
        ]);
    }

    public function test_it_rewords_checks_still_worded_as_seeded(): void
    {
        $check = $this->createCheck(self::OLD_DESCRIPTION);

        $this->migration()->up();

        $this->assertSame(self::NEW_DESCRIPTION, $check->fresh()->description);
    }

    public function test_it_keeps_a_description_a_custodian_has_edited(): void
    {
        $check = $this->createCheck('Check the SRO in person');

        $this->migration()->up();

        $this->assertSame('Check the SRO in person', $check->fresh()->description);
    }

    public function test_rolling_back_restores_the_old_wording(): void
    {
        $check = $this->createCheck(self::OLD_DESCRIPTION);

        $this->migration()->up();
        $this->migration()->down();

        $this->assertSame(self::OLD_DESCRIPTION, $check->fresh()->description);
    }
}
