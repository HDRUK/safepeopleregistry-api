<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckingTrainingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withUsers();
    }

    public function test_successfully_processes_checking_training_expired()
    {
        Training::query()->update([
            'expires_at' => now(),
        ]);

        $today = Carbon::now()->format('Y-m-d');

        $trainingExpiredUsers = User::query()
            ->where([
                'user_group' => User::GROUP_USERS,
            ])
            ->with([
                'registry.trainings' => function ($query) use ($today) {
                    $query->whereRaw('DATE(expires_at) = ?', [$today]);
                },
            ])
            ->whereHas('registry.trainings', function ($query) use ($today) {
                $query->whereRaw('DATE(expires_at) = ?', [$today]);
            })
            ->get();

        $command = $this->artisan('app:checking-trainings');
        foreach ($trainingExpiredUsers as $item) {
            $command->expectsOutput('checking trainings expire for user id ' . $item->id . ' :: done');
        }

        $command->assertExitCode(Command::SUCCESS);
    }

    public function test_successfully_processes_checking_training_warning()
    {
        $warningTrainingExpireDays = config('speedi.system.training_expire_days');

        Training::query()->update([
            'expires_at' => now()->addDays((int)$warningTrainingExpireDays),
        ]);

        $todayAddDays = Carbon::now()->addDays((int)$warningTrainingExpireDays)->format('Y-m-d');

        $trainingExpiredUsers = User::query()
            ->where([
                'user_group' => User::GROUP_USERS,
            ])
            ->with([
                'registry.trainings' => function ($query) use ($todayAddDays) {
                    $query->whereRaw('DATE(expires_at) = ?', [$todayAddDays]);
                },
            ])
            ->whereHas('registry.trainings', function ($query) use ($todayAddDays) {
                $query->whereRaw('DATE(expires_at) = ?', [$todayAddDays]);
            })
            ->get();

        $command = $this->artisan('app:checking-trainings');
        foreach ($trainingExpiredUsers as $item) {
            $command->expectsOutput('checking warning trainings expire for user id ' . $item->id . ' :: done');
        }

        $command->assertExitCode(Command::SUCCESS);
    }
}