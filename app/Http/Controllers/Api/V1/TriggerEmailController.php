<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use TriggerEmail;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\PendingInvite;

use Hdruk\LaravelMjml\Models\EmailTemplate;

use App\Jobs\SendEmailJob;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\Controller;

class TriggerEmailController extends Controller
{
    public function spawnEmail(Request $request): JsonResponse
    {
        $input = $request->all();
        
        TriggerEmail::spawnEmail($input);
        return response()->json([
            'message' => 'success',
            'data' => null,
        ]);
    }
}
