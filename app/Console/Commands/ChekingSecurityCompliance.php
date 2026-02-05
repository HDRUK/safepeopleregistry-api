<?php

namespace App\Console\Commands;

use Exception;
use TriggerEmail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        $today = Carbon::now()->format('Y-m-d');

        try {
            // Cyber Essentials Certified
            $organisations = Organisation::whereRaw('DATE(ce_expiry_date) = ?', [$today])->get();
            foreach ($organisations as $organisation) {
                $this->sendNotificationOnExpires('Cyber Essentials Certified', $organisation->ce_certification_num, $organisation, $organisation->ce_expiry_date->format('Y-m-d'));
                $this->info("checking Cyber Essentials Certified expire for organisation id {$organisation->id} :: done");
            }

            // Cyber Essentials Plus Certified
            $organisations = Organisation::whereRaw('DATE(ce_plus_expiry_date) = ?', [$today])->get();
            foreach ($organisations as $organisation) {
                $this->sendNotificationOnExpires('Cyber Essentials Plus Certified', $organisation->ce_plus_certification_num, $organisation, $organisation->ce_plus_expiry_date->format('Y-m-d'));
                $this->info("checking Cyber Essentials Plus Certified expire for organisation id {$organisation->id} :: done");
            }

            // ISO27001 Accredited
            $organisations = Organisation::whereRaw('DATE(iso_expiry_date) = ?', [$today])->get();
            foreach ($organisations as $organisation) {
                $this->sendNotificationOnExpires('ISO27001 Accredited', $organisation->iso_27001_certification_num, $organisation, $organisation->iso_expiry_date->format('Y-m-d'));
                $this->info("checking ISO27001 Accredited expire for organisation id {$organisation->id} :: done");
            }

            // Data Security and Protection Toolkit / DSPT Certified
            $organisations = Organisation::whereRaw('DATE(dsptk_expiry_date) = ?', [$today])->get();
            foreach ($organisations as $organisation) {
                $this->sendNotificationOnExpires('DSPT Certified', $organisation->dsptk_ods_code, $organisation, $organisation->dsptk_expiry_date->format('Y-m-d'));
                $this->info("checking DSPT Certified expire for organisation id {$organisation->id} :: done");
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());


            Log::error('Command checking warning trainings expire', [
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }

    }

    public function sendNotificationOnExpires($type, $code, $organisation, $expiryDate)
    {
        $organisationId = $organisation->id;

        // organisation
        $userOrganisations = $this->getUserOrganisation($organisationId);
        if ($userOrganisations->isNotEmpty()) {
            foreach ($userOrganisations as $userOrganisation) {
                Notification::send($userOrganisation, new ExpiresSecurityCertifications($type, $code, $organisation, 'organisation'));
                $this->sendEmailOnExpiresToOrganisation($type, $expiryDate, $code, $userOrganisation, $organisation);
            }
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
            $userCustodians = $this->getUserCustodian($userCustodianIds);
            foreach ($userCustodians as $userCustodian) {
                Notification::send($userCustodian, new ExpiresSecurityCertifications($type, $code, $organisation, 'custodian'));
                $this->sendEmailOnExpiresToCustodian($type, $expiryDate, $code, $userCustodian, $organisation);
            }
        }
    }

    public function sendEmailOnExpiresToOrganisation($typeName, $expiryDate, $code, $user, $organisation)
    {
        $email = [
            'type' => 'ORG_SECURITY_COMPLIANCE_EXPIRED',
            'to' => $user->id,
            'by' => -1,
            'identifier' => 'org_security_compliance_expired',
            'typeName' => $typeName,
            'typeCode' => $code,
            'expiryDate' => $expiryDate,
        ];

        TriggerEmail::spawnEmail($email);
    }

    public function sendEmailOnExpiresToCustodian($typeName, $expiryDate, $code, $user, $organisation)
    {
        $email = [
            'type' => 'CUST_SECURITY_COMPLIANCE_EXPIRED',
            'to' => $user->id,
            'by' => -1,
            'identifier' => 'cust_security_compliance_expired',
            'organisationId' => $organisation->id,
            'typeName' => $typeName,
            'typeCode' => $code,
            'expiryDate' => $expiryDate,
        ];

        TriggerEmail::spawnEmail($email);
    }

    private function getUserOrganisation($organisationId): Collection
    {
        $userSros = User::where([
            'organisation_id' => $organisationId,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_sro' => 1,
        ])->get();

        if ($userSros->count()) {
            return $userSros;
        }

        $userDelegates = User::where([
            'organisation_id' => $organisationId,
            'user_group' => User::GROUP_ORGANISATIONS,
            'is_delegate' => 1,
        ])->get();

        if ($userDelegates->count()) {
            return $userDelegates;
        }

        return collect();
    }

    private function getUserCustodian(array $custodianIds): Collection
    {
        $users = User::query()
            ->where([
                'user_group' => User::GROUP_CUSTODIANS,
                'unclaimed' => 0,
            ])
            ->with([
                'custodian_user' => function ($query) use ($custodianIds) {
                    $query->whereIn('custodian_id', $custodianIds);
                },
            ])
            ->whereHas('custodian_user', function ($query) use ($custodianIds) {
                $query->whereIn('custodian_id', $custodianIds);
            })
            ->get();

        return $users;
    }
}
