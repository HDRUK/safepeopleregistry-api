<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Custodian;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Http\Traits\HmacSigning;

class VerifySignedPayload
{
    use Responses;
    use HmacSigning;

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
        if (!$this->verifyBase64RawSignature($payload, $secretKey, $signature)) {
            return $this->InvalidSignatureResponse();
        }

        return $next($request);
    }
}
