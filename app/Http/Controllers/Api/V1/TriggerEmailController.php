<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\PendingInvite;

use Hdruk\LaravelMjml\Models\EmailTemplate;

use App\Jobs\SendEmailJob;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\Controller;

class TriggerEmailController extends Controller
{
    public function spawnEmail(Request $request): void
    {
        $newRecipients = [];
        $invitedBy = [];

        $input = $request->all();

        $type = $input['type'];
        $to = $input['to'];
        $by = isset($input['by']) ? $input['by'] : null;
        $identifier = $input['identifier'];

        switch (strtoupper($type)) {
            case 'RESEARCHER':
                $user = User::where('id', $to)->first();
                $organisation = Organisation::where('id', $by)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                $newRecipients = [
                    'id' => $user->id,
                    'email' => $user->email,
                ];

                $invitedBy = [
                    'id' => $organisation->id,
                    'email' => $organisation->lead_applicant_email,
                ];

                PendingInvite::create([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation->id,
                    'status' => config('speedi.invite_status.PENDING'),
                ]);
                break;
            case 'ISSUER':
                $issuer = Issuer::where('id', $to)->first();
                if ($issuer->invite_accepted_at === null) {
                    $template = EmailTemplate::where('identifier', $identifier)->first();

                    $newRecipients = [
                        'id' => $issuer->id,
                        'email' => $issuer->contact_email,
                    ];

                    $issuer->invite_sent_at = Carbon::now();
                    $issuer->save();

                    $ivitedBy = [];
                } else {
                    throw new Exception('issuer ' . $issuer->id . ' already accepted invite at ' . $issuer->invite_accepted_at);
                }
                break;
            case 'ORGANISATION':
                break;
            default: // Unknown type.
                break;
        }

        SendEmailJob::dispatch($newRecipients, $template, [], $invitedBy);
    }
}
