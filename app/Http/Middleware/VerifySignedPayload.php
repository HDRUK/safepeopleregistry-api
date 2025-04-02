<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Custodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

use App\Http\Traits\Responses;

class VerifySignedPayload
{
    use Responses;

    public function handle(Request $request, Closure $next)
    {
        // Extract the identifiers
        $clientId = $request->header('x-client-id');
        $signature = $request->header('x-signature');

        $payload = json_encode($request->all(), JSON_UNESCAPED_SLASHES);

        if (!$clientId || !$signature) {
            return $this->MissingCustodianCredentialsResponse();
        }

        $custodian = Custodian::where('client_id', $clientId)->first();
        if (!$custodian) {
            return $this->NotFoundResponse();
        }

        $secretKey = $custodian->unique_identifier;
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $secretKey, true));

        if (!hash_equals($expectedSignature, $signature)) {
            return $this->InvalidSignatureResponse();
        }

        return $next($request);
    }
}