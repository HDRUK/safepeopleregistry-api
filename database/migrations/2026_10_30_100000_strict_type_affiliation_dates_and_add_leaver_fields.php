<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliation `from`/`to` were free-text strings, holding '', NULL, 'Y-m-d' or 'Y-m-d H:i:s'.
 * They are backfilled to 'Y-m-d' (or NULL when blank; 'dd/mm/yyyy' is also read) and then strictly typed as dates,
 * so the leaver flow can compare them reliably.
 *
 * A value that cannot be read as a date aborts the migration rather than being lost.
 */
return new class () extends Migration {
    public function up(): void
    {
        $this->backfillDates();

        // MySQL cannot roll back schema changes, so each part checks it has not already run.
        $hasLeaverFields = Schema::hasColumn('affiliations', 'end_date_recorded_at');

        Schema::table('affiliations', function (Blueprint $table) use ($hasLeaverFields) {
            $table->date('from')->nullable()->change();
            $table->date('to')->nullable()->change();

            if ($hasLeaverFields) {
                return;
            }

            // Who recorded the end date (`to`), when, and from which side. The Organisation's date wins.
            $table->dateTime('end_date_recorded_at')->nullable();
            $table->unsignedBigInteger('end_date_recorded_by')->nullable();
            $table->string('end_date_source')->nullable();
            $table->dateTime('end_date_warning_sent_at')->nullable();
            // When the leaver flow actually ran, which can be after `to`.
            $table->dateTime('left_at')->nullable();

            $table->foreign('end_date_recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index('to');
        });

        // Affiliations are no longer deleted once in use, so project history must never cascade away.
        $this->replaceProjectUserAffiliationForeignKey('restrict');
    }

    public function down(): void
    {
        $this->replaceProjectUserAffiliationForeignKey('cascade');

        Schema::table('affiliations', function (Blueprint $table) {
            $table->dropIndex(['to']);
            $table->dropForeign(['end_date_recorded_by']);
            $table->dropColumn([
                'end_date_recorded_at',
                'end_date_recorded_by',
                'end_date_source',
                'end_date_warning_sent_at',
                'left_at',
            ]);

            $table->string('from')->nullable()->change();
            $table->string('to')->nullable()->change();
        });
    }

    /**
     * project_has_users was rebuilt from project_has_users_temp, so on MySQL its foreign key keeps the temp table's
     * name. The key is found by its column rather than by its expected name.
     */
    private function replaceProjectUserAffiliationForeignKey(string $onDelete): void
    {
        $foreignKey = collect(Schema::getForeignKeys('project_has_users'))
            ->first(fn (array $foreignKey) => $foreignKey['columns'] === ['affiliation_id']);

        if ($foreignKey !== null && strtolower((string) $foreignKey['on_delete']) === $onDelete) {
            return;
        }

        Schema::table('project_has_users', function (Blueprint $table) use ($foreignKey, $onDelete) {
            if ($foreignKey !== null) {
                $table->dropForeign($foreignKey['name'] ?? ['affiliation_id']);
            }

            $foreign = $table->foreign('affiliation_id')->references('id')->on('affiliations');
            $onDelete === 'restrict' ? $foreign->restrictOnDelete() : $foreign->cascadeOnDelete();
        });
    }

    private function backfillDates(): void
    {
        $unreadable = [];

        DB::table('affiliations')
            ->select(['id', 'from', 'to'])
            ->orderBy('id')
            ->chunkById(500, function ($affiliations) use (&$unreadable) {
                foreach ($affiliations as $affiliation) {
                    $changes = [];

                    foreach (['from', 'to'] as $column) {
                        $value = $affiliation->{$column};
                        $date = $this->toDateString($value);

                        if ($date === false) {
                            $unreadable[] = "affiliation {$affiliation->id} {$column}='{$value}'";
                            continue;
                        }

                        if ($date !== $value) {
                            $changes[$column] = $date;
                        }
                    }

                    if ($changes !== []) {
                        DB::table('affiliations')->where('id', $affiliation->id)->update($changes);
                    }
                }
            });

        if ($unreadable !== []) {
            throw new RuntimeException(
                'Affiliation dates could not be read, so they have not been converted: ' . implode(', ', $unreadable)
            );
        }
    }

    /**
     * 'Y-m-d' for a readable date, null for a blank value, false for anything else.
     */
    private function toDateString(?string $value): string|null|false
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // 'Y-m-d', 'Y-m-d H:i:s' and ISO 8601 all start with the date.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $matches)) {
            [, $year, $month, $day] = $matches;
        } elseif (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
            [, $day, $month, $year] = $matches;
        } else {
            return false;
        }

        return checkdate((int) $month, (int) $day, (int) $year) ? "{$year}-{$month}-{$day}" : false;
    }
};
