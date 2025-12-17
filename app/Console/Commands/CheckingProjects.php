<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use App\Traits\Notifications\NotificationCustodianManager;

class CheckingProjects extends Command
{
    use NotificationCustodianManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checking-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler command that checks if the project end date has been reached and sends notifications.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = Project::whereRaw('DATE(end_date) = CURDATE()')->get();

        if ($projects->count() === 0) {
            return Command::SUCCESS;
        }

        foreach ($projects as $project) {
            $this->notifyOnProjectEndDate($project);
        }

        return Command::SUCCESS;
    }
}
