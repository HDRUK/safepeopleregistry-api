<?php

namespace App\TriggerEmail;

use App\Jobs\SendEmailJob;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\Organisation;
use App\Models\OrganisationDelegate;
use App\Models\PendingInvite;
use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Hdruk\LaravelMjml\Models\EmailTemplate;

class TriggerEmail
{
    public function spawnEmail(array $input): void
    {
        $replacements = [];
        $newRecipients = [];
        $invitedBy = [];
        $template = null;

        $type = $input['type'];
        $to = $input['to'];
        $by = isset($input['by']) ? $input['by'] : null;
        $identifier = $input['identifier'];

        switch (strtoupper($type)) {
            case 'RESEARCHER':
                $user = User::where('id', $to)->first();
                $organisation = Organisation::where('id', $by)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();
                $delegate = OrganisationDelegate::where([
                    'organisation_id' => $by,
                    'priority_order' => 0,
                ])->first();

                if ($identifier === 'delegate_sponsor') {
                    $newRecipients = [
                        'id' => $delegate->id,
                        'email' => $delegate->email,
                    ];
                } else {
                    $newRecipients = [
                        'id' => $user->id,
                        'email' => $user->email,
                    ];
                }

                $invitedBy = [
                    'id' => $organisation->id,
                    'email' => $organisation->lead_applicant_email,
                ];

                $replacements = [
                    '[[organisation_delegates.first_name]]' => $delegate->first_name,
                    '[[organisation_delegates.last_name]]' => $delegate->last_name,
                    '[[organisations.organisation_name]]' => $organisation->organisation_name,
                    '[[organisations.lead_application_organisation_name]]' => $organisation->lead_applicant_organisation_name,
                    '[[users.first_name]]' => $user->first_name,
                    '[[users.last_name]]' => $user->last_name,
                    '[[users.created_at]]' => $user->created_at,
                    '[[env(INVITE_TIME_HOURS)]]' => env('INVITE_TIME_HOURS'),
                    '[[env(SUPPORT_EMAIL)]]' => env('SUPPORT_EMAIL'),
                    '[[users.id]]' => $user->id,
                    '[[organisations.id]]' => $organisation->id,
                    '[[organisation_delegates.id]]' => $delegate->id,
                ];

                PendingInvite::create([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation->id,
                    'status' => config('speedi.invite_status.PENDING'),
                ]);
                break;
            case 'CUSTODIAN':
                $custodian = Custodian::where('id', $to)->first();
                if ($custodian->invite_accepted_at === null) {
                    $template = EmailTemplate::where('identifier', $identifier)->first();

                    $newRecipients = [
                        'id' => $custodian->id,
                        'email' => $custodian->contact_email,
                    ];

                    $custodian->invite_sent_at = Carbon::now();
                    $custodian->save();

                    $ivitedBy = [];
                } else {
                    throw new Exception('custodian '.$custodian->id.' already accepted invite at '.$custodian->invite_accepted_at);
                }
                break;
            case 'CUSTODIAN_USER':
                $user = CustodianUser::where('id', $to)->with('permission')->first();
                $custodian = Custodian::where('id', $user->custodian_id)->first();

                // dd($user);

                $permission = Permission::where('id', $user->permission_id)->first();

                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $user->id,
                    'email' => $user->email,
                ];

                $replacements = [
                    '[[custodian.name]]' => $custodian->name,
                    '[[permission.name]]' => $user->permission->name,
                    '[[env(SUPPORT_EMAIL)]]' => env('SUPPORT_EMAIL'),
                ];
            case 'ORGANISATION':
                break;
            default: // Unknown type.
                break;
        }

        SendEmailJob::dispatch($newRecipients, $template, $replacements, $invitedBy);
    }
}
