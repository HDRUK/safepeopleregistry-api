<?php

namespace App\Observers;

use App\Models\Organisation;
use App\Models\ActionLog;
use Carbon\Carbon;

class OrganisationObserver
{
    protected array $nameAndAddressFields = [
        'organisation_name',
        'address_1',
        'town',
        'country',
        'postcode'
    ];

    protected array $digitalIdentifiers = [
        'companies_house_no',
        'ror_id',
    ];

    protected array $sectorSize = [
        'sector_id',
        'website',
        // 'organisation_size'
    ];

    protected array $securityCompliance = [
        'dsptk_ods_code',
        'dsptk_expiry_date',
        'iso_27001_certification_num',
        'iso_expiry_date',
        'ce_certification_num',
        'ce_expiry_date',
        'ce_plus_certification_num',
        'ce_plus_expiry_date',
    ];

    /**
     * Handle the Organisation "created" event.
     */
    public function created(Organisation $organisation): void
    {
        foreach (Organisation::getDefaultActions() as $action) {
            ActionLog::create([
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => $action,
                'completed_at' => null,
            ]);
        }
    }

    /**
     * Handle the Organisation "updated" event.
     */
    public function updated(Organisation $organisation): void
    {

        $this->checkIsComplete(
            $organisation,
            $this->nameAndAddressFields,
            Organisation::ACTION_NAME_ADDRESS_COMPLETED
        );

        $this->checkIsComplete(
            $organisation,
            $this->digitalIdentifiers,
            Organisation::ACTION_DIGITAL_ID_COMPLETED
        );

        $this->checkIsComplete(
            $organisation,
            $this->sectorSize,
            Organisation::ACTION_SECTOR_SIZE_COMPLETED
        );

        // note - future improvement to also check the date is not expired
        $this->checkIsComplete(
            $organisation,
            $this->securityCompliance,
            Organisation::ACTION_DATA_SECURITY_COMPLETED
        );


    }

    /**
     * Handle the Organisation "deleted" event.
     */
    public function deleted(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "restored" event.
     */
    public function restored(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "force deleted" event.
     */
    public function forceDeleted(Organisation $organisation): void
    {
        //
    }

    private function checkIsComplete(Organisation $organisation, array $fields, string $action): void
    {
        if ($organisation->isDirty($fields)) {
            $isProfileComplete = collect($fields)
                ->every(fn ($field) => !empty($organisation->$field));

            if ($isProfileComplete) {
                ActionLog::updateOrCreate(
                    [
                        'entity_id' => $organisation->id,
                        'entity_type' => Organisation::class,
                        'action' => $action
                    ],
                    ['completed_at' => Carbon::now()]
                );
            }
        }
    }

}
