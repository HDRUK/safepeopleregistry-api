<?php

namespace App\Observers;

use App\Models\Organisation;
use App\Models\Custodian;
use App\Models\Affiliation;
use App\Models\State;
use App\Models\ActionLog;
use Carbon\Carbon;
use App\Traits\ValidationManager;

class OrganisationObserver
{
    use ValidationManager;
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
        'organisation_size',
    ];

    protected array $securityCompliance = [
        'dsptk_ods_code',
        'dsptk_expiry_date',
        'dsptk_expiry_evidence',
        'iso_27001_certification_num',
        'iso_expiry_date',
        'iso_expiry_evidence',
        'ce_certification_num',
        'ce_expiry_date',
        'ce_expiry_evidence',
        'ce_plus_certification_num',
        'ce_plus_expiry_date',
        'ce_plus_expiry_evidence',
    ];

    /**
     * Handle the Organisation "created" event.
     */
    public function created(Organisation $organisation): void
    {
        foreach (Organisation::getDefaultActions() as $action) {
            ActionLog::firstOrCreate([
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => $action,
            ], [
                'completed_at' => null,
            ]);
        }

        // Force completeness checks on creation
        $this->checkIsComplete($organisation, $this->nameAndAddressFields, Organisation::ACTION_NAME_ADDRESS_COMPLETED, true);
        $this->checkIsComplete($organisation, $this->digitalIdentifiers, Organisation::ACTION_DIGITAL_ID_COMPLETED, true);
        $this->checkIsComplete($organisation, $this->sectorSize, Organisation::ACTION_SECTOR_SIZE_COMPLETED, true);
        $this->checkIsComplete($organisation, $this->securityCompliance, Organisation::ACTION_DATA_SECURITY_COMPLETED, true);

        $this->manageAffiliationStates($organisation);

        $custodianIds = Custodian::select("id")->pluck("id");
        foreach ($custodianIds as $custodianId) {
            $this->updateCustodianOrganisationValidation(
                $custodianId,
                $organisation->id
            );
        }
    }

    /**
     * Handle the Organisation "updated" event.
     */
    public function updated(Organisation $organisation): void
    {
        $this->checkIsComplete($organisation, $this->nameAndAddressFields, Organisation::ACTION_NAME_ADDRESS_COMPLETED);
        $this->checkIsComplete($organisation, $this->digitalIdentifiers, Organisation::ACTION_DIGITAL_ID_COMPLETED);
        $this->checkIsComplete($organisation, $this->sectorSize, Organisation::ACTION_SECTOR_SIZE_COMPLETED);
        $this->checkIsComplete($organisation, $this->securityCompliance, Organisation::ACTION_DATA_SECURITY_COMPLETED);
        $this->manageAffiliationStates($organisation);
    }

    private function checkIsComplete(Organisation $organisation, array $fields, string $action, bool $force = false): void
    {
        if ($force || $organisation->isDirty($fields)) {
            $isProfileComplete = collect($fields)->every(function ($field) use ($organisation) {
                if ($this->isDateField($field)) {
                    return $this->isDateValid($organisation->$field);
                }
                return !empty($organisation->$field);
            });

            ActionLog::updateOrCreate(
                [
                    'entity_id' => $organisation->id,
                    'entity_type' => Organisation::class,
                    'action' => $action
                ],
                ['completed_at' => $isProfileComplete ? Carbon::now() : null]
            );
        }
    }

    private function manageAffiliationStates(Organisation $organisation): void
    {
        if ($organisation->isDirty('unclaimed')) {
            $unclaimed = $organisation->unclaimed;
            $state = $unclaimed ? State::STATE_AFFILIATION_INVITED : State::STATE_AFFILIATION_PENDING;
            $affiliations = Affiliation::where("organisation_id", $organisation->id)->get();

            foreach ($affiliations as $affiliation) {
                $affiliation->setState($state);
            }
        }
    }

    private function isDateField(string $field): bool
    {
        return str_contains($field, '_expiry_date');
    }

    private function isDateValid(?string $date): bool
    {
        if (!$date) {
            return false;
        }

        $expiryDate = Carbon::parse($date);
        return $expiryDate->isFuture();
    }
}
