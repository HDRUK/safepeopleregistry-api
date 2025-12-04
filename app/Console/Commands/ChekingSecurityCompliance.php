<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Models\CustodianHasProjectOrganisation;
use App\Notifications\Organisations\ExpiresSecurityCertifications;

class ChekingSecurityCompliance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cheking-security-compliance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler command to check data security compliance documents validity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cyber Essentials Certified
        $organisations = Organisation::whereRaw('DATE(ce_expiry_date) = CURDATE()')->get();
        foreach ($organisations as $organisation) {
            $this->sendNotificationOnExpires('Cyber Essentials Certified', $organisation->ce_certification_num, $organisation);
        }

        // Cyber Essentials Plus Certified
        $organisations = Organisation::whereRaw('DATE(ce_plus_expiry_date) = CURDATE()')->get();
        foreach ($organisations as $organisation) {
            $this->sendNotificationOnExpires('Cyber Essentials Plus Certified', $organisation->ce_plus_certification_num, $organisation);
        }

        // ISO27001 Accredited
        $organisations = Organisation::whereRaw('DATE(iso_expiry_date) = CURDATE()')->get();
        foreach ($organisations as $organisation) {
            $this->sendNotificationOnExpires('ISO27001 Accredited', $organisation->iso_27001_certification_num, $organisation);
        }

        // DSPT Certified
        $organisations = Organisation::whereRaw('DATE(iso_expiry_date) = CURDATE()')->get();
        foreach ($organisations as $organisation) {
            $this->sendNotificationOnExpires('dsptk_expiry_date', $organisation->dsptk_ods_code, $organisation);
        }

        return Command::SUCCESS;
    }

    public function sendNotificationOnExpires($type, $code, $organisation)
    {
        $organisationId = $organisation->id;

        // organisation
        $userOrganisations = User::where([
                'organisation_id' => $organisation->id,
            ])->get();
        foreach ($userOrganisations as $userOrganisation) {
            Notification::send($userOrganisation, new ExpiresSecurityCertifications($type, $code, $organisation, 'organisation'));
        }

        // custodian
        $userCustodianIds = CustodianHasProjectOrganisation::query()
            ->whereHas('projectOrganisation', function ($query) use ($organisationId) {
                $query->where('organisation_id', $organisationId);
            })
            ->select(['custodian_id'])
            ->pluck('custodian_id')
            ->toArray();
        if ($userCustodianIds) {
            $userCustodians = User::whereIn('custodian_user_id', $userCustodianIds)->get();
            Notification::send($userCustodians, new ExpiresSecurityCertifications($type, $code, $organisation, 'custodian'));
        }
    }
}
