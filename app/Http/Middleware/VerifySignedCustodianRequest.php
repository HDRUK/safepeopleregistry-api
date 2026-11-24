<?php

namespace App\Http\Middleware;

use App\Http\Traits\HmacSigning;
use App\Http\Traits\Responses;
use App\Models\Custodian;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySignedCustodianRequest
{
    use Responses;
    use HmacSigning;

    public function handle(Request $request, Closure $next): Response
    {
        $clientId = (string) $request->header('x-client-id', '');
        $timestamp = (string) $request->header('x-timestamp', '');
        $signature = (string) $request->header('x-signature', '');

        if ($clientId === '' || $timestamp === '' || $signature === '') {
            return $this->MissingCustodianCredentialsResponse();
        }

        // Every other failure gets the same response, so callers can't probe which check failed
        if (!ctype_digit($timestamp)
            || abs(Carbon::now()->timestamp - (int) $timestamp) > config('speedi.system.custodian_signature_tolerance')) {
            return $this->UnauthorisedResponse();
        }

        $custodian = Custodian::where('client_id', $clientId)->first();
        if (!$custodian || !$custodian->enabled) {
            return $this->UnauthorisedResponse();
        }

        $expected = $this->generateCanonicalRequestSignature(
            $timestamp,
            $request->method(),
            $request->getPathInfo(),
            $request->query(),
            $custodian->unique_identifier,
        );

        if (!hash_equals($expected, $signature)) {
            return $this->UnauthorisedResponse();
        }

        $request->attributes->set('custodian', $custodian);

        return $next($request);
    }
}
