<?php

namespace App\Jobs;

use App\Models\Affiliation;
use App\Models\DebugLog;
use App\Models\ProjectHasUser;
use App\Models\Registry;
use App\Models\User;
use App\Models\ValidationLog;
use App\Traits\CommonFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class MergeUserAccounts implements ShouldQueue
{
    use CommonFunctions;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    private ?Affiliation $affiliation = null;

    /**
     * Create a new job instance.
     */
    public function __construct(Affiliation $affiliation)
    {
        $this->affiliation = $affiliation;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $professional_email = $this->affiliation->email;
        $user = $this->affiliation->registry->user;
        $registry = $user->registry;

        if ($user->unclaimed === 1) {
            DebugLog::create([
                'class' => __CLASS__,
                'log' => 'Affiliation associated to unclaimed account so skipping merging of user accounts'
            ]);
            return;
        }

        $existingUnclaimedUser = User::where([
            'email' => $professional_email,
            'unclaimed' => 1
        ])->first();

        if (!$existingUnclaimedUser) {
            DebugLog::create([
                'class' => __CLASS__,
                'log' => 'No unclaimed account found with the same professional email'
            ]);
            return;
        }

        // replace project has user with claimed user
        ProjectHasUser::where([
            'user_digital_ident' => $existingUnclaimedUser->registry->digi_ident
        ])
            ->update([
                'user_digital_ident' => $registry->digi_ident
            ]);

        //update any validation logs
        ValidationLog::where([
            'tertiary_entity_id' => $existingUnclaimedUser->registry->id,
            'tertiary_entity_type' => Registry::class,
        ])
            ->update(['tertiary_entity_id' => $registry->id]);


        // hard or soft delete?  Hard for now..
        $existingUnclaimedUser->delete();
    }
}
