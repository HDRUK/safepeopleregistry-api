<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Backfills organisations.sro_appointed_at from the activity log, so an SRO change made before it
 * was recorded still needs a new SRO declaration. Each claimed SRO's logged changes are replayed in
 * order, and the last time they became their organisation's claimed SRO is taken. Organisations
 * whose SRO the log cannot account for are left null, so any declaration counts for them.
 */
return new class () extends Migration {
    public function up(): void
    {
        $activityLog = DB::connection(config('activitylog.database_connection'))
            ->table(config('activitylog.table_name'));

        $sros = DB::table('users')
            ->where('user_group', User::GROUP_ORGANISATIONS)
            ->whereNotNull('organisation_id')
            ->where('is_sro', 1)
            ->where('unclaimed', 0)
            ->get(['id', 'organisation_id']);

        foreach ($sros as $sro) {
            $activities = (clone $activityLog)
                ->where('subject_type', User::class)
                ->where('subject_id', $sro->id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['properties', 'created_at']);

            $appointedAt = $this->lastAppointedAt($activities, (int) $sro->organisation_id);

            if ($appointedAt !== null) {
                DB::table('organisations')
                    ->where('id', $sro->organisation_id)
                    ->whereNull('sro_appointed_at')
                    ->update(['sro_appointed_at' => $appointedAt]);
            }
        }
    }

    public function down(): void
    {
        // The backfilled values cannot be told apart from ones recorded since, so they are kept.
    }

    /**
     * When the user last became the organisation's claimed SRO, or null if the log cannot say.
     */
    private function lastAppointedAt(Collection $activities, int $organisationId): ?string
    {
        // Unknown until logged. 'old' values are not relied on, as they are not always accurate.
        // organisation_id rarely changes and is only logged when it does, so it starts as the current one.
        $state = ['is_sro' => null, 'unclaimed' => null, 'organisation_id' => $organisationId];
        $appointedAt = null;

        foreach ($activities as $activity) {
            $attributes = json_decode($activity->properties, true)['attributes'] ?? [];
            $wasSro = $this->isClaimedSro($state, $organisationId);

            foreach (array_keys($state) as $field) {
                if (array_key_exists($field, $attributes)) {
                    $state[$field] = $attributes[$field];
                }
            }

            if (!$wasSro && $this->isClaimedSro($state, $organisationId)) {
                $appointedAt = $activity->created_at;
            }
        }

        // The log must agree with the user's current state, or it is missing changes.
        return $this->isClaimedSro($state, $organisationId) ? $appointedAt : null;
    }

    private function isClaimedSro(array $state, int $organisationId): bool
    {
        return $state['is_sro'] !== null && (bool) $state['is_sro']
            && $state['unclaimed'] !== null && !(bool) $state['unclaimed']
            && (int) $state['organisation_id'] === $organisationId;
    }
};
