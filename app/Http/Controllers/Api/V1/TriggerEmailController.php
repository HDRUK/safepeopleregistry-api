<?php

namespace App\Http\Controllers\Api\V1;

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
                $users = User::where('id', $to)->get();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                foreach ($users as $u) {
                    $newRecipients = [
                        'id' => $u->id,
                        'email' => $u->email,
                    ];
                }
                break;
            case 'ISSUER':
                $issuers = Issuer::where('id', $to)->get();
                $template = EmailTemplate::where('identifier', $identifier)->first();

                foreach ($issuers as $i) {
                    $newRecipients = [
                        'id' => $i->id,
                        'email' => $i->contact_email,
                    ];
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
