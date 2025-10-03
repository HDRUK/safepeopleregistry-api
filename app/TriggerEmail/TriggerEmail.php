<?php

namespace App\TriggerEmail;

use Str;
use App\Jobs\SendEmailJob;
use App\Models\Affiliation;
use App\Models\Custodian;
use App\Models\CustodianUser;
use App\Models\Organisation;
use App\Models\PendingInvite;
use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Hdruk\LaravelMjml\Models\EmailTemplate;

class TriggerEmail
{
    // LS - Stubbed for later.
    // public function spawnAdminEmail(array $input): void
    // {
    //     $replacements = [];
    //     $recipients = [];
    //     $template = null;

    //     $recipients = [
    //         'id' => $input['to'],
    //         'email' => $input['email'],
    //     ];

    //     $replacements = [
    //         '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
    //         '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
    //         '[[ORGANISATION_NAME]]' => $input['name'],
    //     ];

    //     SendEmailJob::dispatch($recipients, $template, $replacements, $recipients['email']);
    // }

    public function generateInviteCode(): string
    {
        return Str::uuid();
    }

    public function spawnEmail(array $input): void
    {
        $replacements = [];
        $newRecipients = [];
        $invitedBy = [];
        $template = null;

        $type = $input['type'];
        $unclaimedUserId = isset($input['unclaimed_user_id']) ? $input['unclaimed_user_id'] : null;
        $to = $input['to'];
        $by = isset($input['by']) ? $input['by'] : null;
        $for = isset($input['for']) ? $input['for'] : null;
        $affiliationId = isset($input['affiliationId']) ? $input['affiliationId'] : null;
        $custodianId = isset($input['custodianId']) ? $input['custodianId'] : null;
        $identifier = $input['identifier'];

        $inviteCode = $this->generateInviteCode();

        switch (strtoupper($type)) {
            case 'AFFILIATION':
                if ($input['email'] === '') {
                    // Log and return
                    return;
                }

                $user = User::where('id', $to)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $user->id,
                    'email' => $input['pro_email'],
                ];

                $replacements = [
                    '[[users.first_name]]' => $user->first_name,
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                ];
                break;
            case 'USER_WITHOUT_ORGANISATION':
                $user = User::where('id', $to)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $user->id,
                    'email' => $user->email,
                ];

                $replacements = [
                    '[[users.first_name]]' => $user->first_name,
                    '[[users.last_name]]' => $user->last_name,
                    '[[users.created_at]]' => $user->created_at,
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),

                ];

                PendingInvite::create([
                    'user_id' => $user->id,
                    'invite_sent_at' => Carbon::now(),
                    'status' => config('speedi.invite_status.PENDING'),
                ]);
                break;
            case 'USER':
                $user = User::where('id', $to)->first();
                $organisation = Organisation::where('id', $by)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();
                $custodian = $custodianId ? User::where('id', $custodianId)->first() : null;

                $newRecipients = [
                    'id' => $user->id,
                    'email' => $user->email,
                ];

                $replacements = [
                    '[[organisation.organisation_name]]' => $organisation->organisation_name,
                    '[[users.first_name]]' => $user->first_name,
                    '[[users.last_name]]' => $user->last_name,
                    '[[users.created_at]]' => $user->created_at,
                    '[[custodian.name]]' => $custodianId ? $custodian->name : '',
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                    '[[env(PORTAL_PATH_INVITE)]]' => config('speedi.system.portal_path_invite'),
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                ];

                PendingInvite::create([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation->id,
                    'invite_sent_at' => Carbon::now(),
                    'status' => config('speedi.invite_status.PENDING'),
                ]);
                break;
            case 'USER_DELEGATE':
                $delegate = User::where('id', $to)->first();
                $organisation = Organisation::where('id', $by)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();


                $newRecipients = [
                    'id' => $delegate->id,
                    'email' => $delegate->email,
                ];

                $invitedBy = [
                    'id' => $organisation->id,
                    'email' => $organisation->lead_applicant_email,
                ];

                $replacements = [
                    '[[organisation.organisation_name]]' => $organisation->organisation_name,
                    '[[delegate_first_name]]' => $delegate->first_name,
                    '[[delegate_last_name]]' => $delegate->last_name,
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                    '[[env(INVITE_TIME_HOURS)]]' => config('speedi.system.invite_time_hours'),
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[organisations.id]]' => $organisation->id,
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                ];

                PendingInvite::create([
                    'user_id' => $delegate->id,
                    'organisation_id' => $organisation->id,
                    'invite_sent_at' => Carbon::now(),
                    'status' => config('speedi.invite_status.PENDING'),
                ]);
                break;
            case 'CUSTODIAN':
                $custodian = Custodian::where('id', $to)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $custodian->id,
                    'email' => $custodian->contact_email,
                ];

                $replacements = [
                    '[[custodian.name]]' => $custodian->name,
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                    '[[env(PORTAL_PATH_INVITE)]]' => config('speedi.system.portal_path_invite'),
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                ];

                PendingInvite::create([
                    'user_id' => $unclaimedUserId,
                    'status' => config('speedi.invite_status.PENDING'),
                    'invite_sent_at' => Carbon::now()
                ]);

                break;
            case 'CUSTODIAN_USER':
                $custodianUser = CustodianUser::with('userPermissions.permission')->where('id', $to)->first();
                $custodian = Custodian::where('id', $custodianUser->custodian_id)->first();
                $user = User::where('id', $unclaimedUserId)->first();

                $role_description = '';

                if (count($custodianUser->userPermissions) > 0) {
                    $permission = Permission::where('id', $custodianUser->userPermissions[0]->permission_id)->first();
                    $role_description = "as an $permission->description";
                }

                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $custodianUser->id,
                    'email' => $custodianUser->email,
                ];

                $replacements = [
                    '[[custodian_user.id]]' => $custodianUser->id,
                    '[[custodian_user.first_name]]' => $custodianUser->first_name,
                    '[[custodian_user.last_name]]' => $custodianUser->last_name,
                    '[[custodian.name]]' => $custodian->name,
                    '[[custodian.id]]' => $custodian->id,
                    '[[users.first_name]]' => $user->first_name,
                    '[[users.last_name]]' => $user->last_name,
                    '[[role_description]]' => $role_description,
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                    '[[env(PORTAL_PATH_INVITE)]]' => config('speedi.system.portal_path_invite'),
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                ];

                PendingInvite::create([
                    'user_id' => $unclaimedUserId,
                    'status' => config('speedi.invite_status.PENDING'),
                    'invite_sent_at' => Carbon::now()
                ]);

                break;
            case 'ORGANISATION':
                $organisation = Organisation::where('id', $to)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $organisation->id,
                    'email' => $organisation->lead_applicant_email,
                ];

                $replacements = [
                    '[[organisation.organisation_name]]' => $organisation->organisation_name,
                    '[[inviteCode]]' => $inviteCode,
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                    '[[env(PORTAL_PATH_INVITE)]]' => config('speedi.system.portal_path_invite'),
                    // '[[digi_ident]]' => User::where('id', $unclaimedUserId)->first()->registry->digi_ident,
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                ];

                PendingInvite::create([
                    'user_id' => $unclaimedUserId,
                    'status' => config('speedi.invite_status.PENDING'),
                    'invite_sent_at' => Carbon::now(),
                    'invite_code' => $inviteCode
                ]);

                break;
            case 'DELEGATE_AFFILIATION_REQUEST':
                $template = EmailTemplate::where('identifier', $identifier)->first();
                $delegate = User::where('id', $to)->first();

                $newRecipients = [
                    'id' => $delegate->id,
                    'email' => $delegate->email,
                ];

                $organisation = Organisation::where('id', $by)->first();
                $affiliation = Affiliation::where('id', $affiliationId)->first();
                $user = User::where('registry_id', $by)->first();

                $replacements = [
                    '[[delegate_first_name]]' => $delegate->first_name,
                    '[[delegate_last_name]]' => $delegate->last_name,
                    '[[organisation.organisation_name]]' => $organisation->organisation_name,
                    '[[users.first_name]]' => $user->first_name,
                    '[[users.last_name]]' => $user->last_name,
                    '[[users.email]]' => $affiliation->email,
                    '[[users.profile]]' => config('speedi.system.portal_url') . '/organisation/profile/user-administration/employees-and-students/' . $user->id . '/identity',
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                    '[[env(PORTAL_URL)]]' => config('speedi.system.portal_url'),
                    '[[env(PORTAL_PATH_INVITE)]]' => config('speedi.system.portal_path_invite'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                ];

                PendingInvite::create([
                    'user_id' => $delegate->id,
                    'organisation_id' => $organisation->id,
                    'invite_sent_at' => Carbon::now(),
                    'status' => config('speedi.invite_status.PENDING'),
                ]);

                break;

            case 'ORGANISATION_NEEDS_CONFIRMATION':
                $template = EmailTemplate::where('identifier', $identifier)->first();
                $organisation = Organisation::where('id', $to)->first();
                $newRecipients = [
                    'id' => $to,
                    'email' => $organisation->lead_applicant_email,
                ];

                $replacements = [
                    '[[organisation.organisation_name]]' => $organisation->organisation_name,
                    '[[ORGANISATION_PATH_PROFILE]]' => config('speedi.system.portal_url') . '/organisation/profile/details',
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                ];

                break;
            case 'AFFILIATION_VERIFY':
                $template = EmailTemplate::where('identifier', $identifier)->first();
                $affiliation = Affiliation::where([
                    'id' => $to,
                    'current_employer' => true
                ])->first();

                $newRecipients = [
                    'id' => $to,
                    'email' => $affiliation->email,
                ];

                $validationHours = round((int)config('speedi.system.otp_affiliation_validity_minutes') / 60, 0);
                $replacements = [
                    '[[env(SUPPORT_EMAIL)]]' => config('speedi.system.support_email'),
                    '[[env(APP_NAME)]]' => config('speedi.system.app_name'),
                    '[[env(REGISTRY_IMAGE_URL)]]' => config('speedi.system.registry_image_url'),
                    '[[AFFILIATION_VERIFICATION_PATH]]' => config('speedi.system.portal_url') . '/user/profile/affiliations?verify=' . $affiliation->verification_code,
                    '[[env(OTP_AFFILIATION_VALIDITY_HOURS)]]' => $validationHours,
                ];

                break;
            default: // Unknown type.
                break;
        }

        SendEmailJob::dispatch($newRecipients, $template, $replacements, $newRecipients['email']);
    }
}
