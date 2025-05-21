<?php

namespace App\Jobs;

use App\Enums\ValidationCheckAppliesTo;
use App\Traits\ValidationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCustodianValidation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidationManager;

    protected int $custodianId;
    protected ValidationCheckAppliesTo $appliesTo;

    public function __construct(int $custodianId, ValidationCheckAppliesTo $appliesTo)
    {
        $this->custodianId = $custodianId;
        $this->appliesTo = $appliesTo;
    }

    public function handle(): void
    {
        if ($this->appliesTo === ValidationCheckAppliesTo::Organisation) {
            $this->updateAllCustodianOrganisationValidation(
                $this->custodianId,
            );
        } else if ($this->appliesTo === ValidationCheckAppliesTo::ProjectUser) {
            $this->updateAllCustodianProjectUserValidation(
                $this->custodianId,
            );
        }
    }
}
