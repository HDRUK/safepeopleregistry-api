<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\User;
use App\Models\Issuer;
use App\Jobs\SendEmailJob;
use Hdruk\LaravelMjml\Models\EmailTemplate;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\Controller;

class TriggerEmailController extends Controller
{
    public function spawnEmail(Request $request): void
    {
        $newRecipients = [];

        $input = $request->all();

        $type = $input['type'];
        $to = $input['to'];
        $identifier = $input['identifier'];

        switch (strtoupper($type)) {
            case 'USER':
                $users = User::where('id', $to)->first();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                foreach ($users as $u) {
                    $newRecipients = [
                        'id' => $u->id,
                        'email' => $u->email,
                    ];
                }
                break;
            case 'ISSUER':
                $issuer = Issuer::where('id', $to)->first();
                if ($issuer->invite_accepted_at === null) {
                    $template = EmailTemplate::where('identifier', $identifier)->first();

                    $newRecipients = [
                        'id' => $issuer->id,
                        'email' => $issuer->contact_email,
                    ];
                } else {
                    throw new Exception('issuer ' . $issuer->id . ' already accepted invite at ' . $issuer->invite_accepted_at);
                }
                break;
            case 'ORGANISATION':
                break;
            default: // Unknown type.
                break;
        }

        SendEmailJob::dispatch($newRecipients, $template, []);
    }
}
