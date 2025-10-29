<?php

namespace App\Jobs;

use App\Models\ProjectHasUser;
use App\Traits\ValidationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProjectUserValidation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ValidationManager;

    public $tries = 3;

    protected ProjectHasUser $phu;

    public function __construct(ProjectHasUser $phu)
    {
        $this->phu = $phu;
    }

    public function handle(): void
    {
        $this->updateCustodianProjectUserValidation(
            $this->phu->project_id,
            $this->phu->user_digital_ident
        );
    }
}
