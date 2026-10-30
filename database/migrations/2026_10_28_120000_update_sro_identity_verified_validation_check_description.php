<?php

use App\Models\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds checking the SRO declaration to the "Verify SRO identity" manual check, in environments already
 * seeded by ValidationCheckSeeder. Only checks still worded as seeded are changed, so a description a
 * custodian has edited is kept.
 */
return new class () extends Migration {
    private const NAME = 'sro_identity_verified';
    private const OLD_DESCRIPTION = 'Contact details tab: Verify SRO identity';
    private const NEW_DESCRIPTION = 'Contact details tab: Verify SRO identity and check SRO declaration';

    public function up(): void
    {
        $this->reword(self::OLD_DESCRIPTION, self::NEW_DESCRIPTION);
    }

    public function down(): void
    {
        $this->reword(self::NEW_DESCRIPTION, self::OLD_DESCRIPTION);
    }

    private function reword(string $from, string $to): void
    {
        DB::table('validation_checks')
            ->where('name', self::NAME)
            ->where('applies_to', Organisation::class)
            ->where('description', $from)
            ->update(['description' => $to, 'updated_at' => now()]);
    }
};
