<?php

namespace App\Console\Commands;

use Throwable;
use Carbon\Carbon;
use App\Models\State;
use App\Models\Affiliation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Models\CustodianHasProjectUser;
use App\Services\AffiliationLeaverService;

class CheckingAffiliations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checking-affiliations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler command to end affiliations whose end date has arrived, warn users whose affiliations end soon, and remind Custodians to revoke the access of users who have left';

    /**
     * Execute the console command. Each check only picks up what it has not already done, so
     * running it more than once a day, or after a missed day, is safe.
     */
    public function handle(AffiliationLeaverService $leaver): int
    {
        $failures = 0;

        // Ended first, so an affiliation ending today is not also warned about.
        $failures += $this->endAffiliations($leaver);
        $failures += $this->warnAffiliationsEndingSoon($leaver);
        $failures += $this->remindAccessRevocations($leaver);

        return $failures === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function endAffiliations(AffiliationLeaverService $leaver): int
    {
        $affiliations = $this->notEnded(Affiliation::query())
            ->whereNotNull('to')
            ->whereDate('to', '<=', Carbon::today());

        return $this->each($affiliations, 'ending affiliation', function (Affiliation $affiliation) use ($leaver) {
            $leaver->end($affiliation);
        });
    }

    private function warnAffiliationsEndingSoon(AffiliationLeaverService $leaver): int
    {
        $warningDays = (int) config('speedi.system.affiliation_end_warning_days');

        $affiliations = $this->notEnded(Affiliation::query())
            ->whereNull('end_date_warning_sent_at')
            ->whereDate('to', '>', Carbon::today())
            ->whereDate('to', '<=', Carbon::today()->addDays($warningDays));

        return $this->each($affiliations, 'warning affiliation ending soon', function (Affiliation $affiliation) use ($leaver) {
            $leaver->warnEndingSoon($affiliation);
        });
    }

    private function remindAccessRevocations(AffiliationLeaverService $leaver): int
    {
        $remindAfter = Carbon::now()->subDays((int) config('speedi.system.access_revocation_reminder_days'));

        $projectUsers = CustodianHasProjectUser::query()
            ->awaitingAccessRevocation()
            ->where(function (Builder $query) use ($remindAfter) {
                $query->where(function (Builder $query) use ($remindAfter) {
                    $query->whereNull('access_revocation_reminded_at')
                        ->where('access_revocation_required_at', '<=', $remindAfter);
                })->orWhere('access_revocation_reminded_at', '<=', $remindAfter);
            });

        return $this->each($projectUsers, 'reminding access revocation', function (CustodianHasProjectUser $projectUser) use ($leaver) {
            $leaver->remindAccessRevocation($projectUser);
        });
    }

    private function notEnded(Builder $query): Builder
    {
        return $query->whereDoesntHave('modelState.state', function (Builder $query) {
            $query->whereIn('slug', [...Affiliation::ENDED_STATES, State::STATE_AFFILIATION_REJECTED]);
        });
    }

    /**
     * Runs $action on each model, logging any that fail so the rest still run.
     *
     * @return int how many failed
     */
    private function each(Builder $query, string $description, callable $action): int
    {
        $failures = 0;

        $query->chunkById(100, function ($models) use ($description, $action, &$failures) {
            /** @var Affiliation|CustodianHasProjectUser $model */
            foreach ($models as $model) {
                try {
                    $action($model);
                    $this->info("{$description} id {$model->id} :: done");
                } catch (Throwable $e) {
                    $failures++;
                    $this->error("{$description} id {$model->id} :: {$e->getMessage()}");

                    Log::error("Command checking affiliations: {$description}", [
                        'id' => $model->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        });

        return $failures;
    }
}
