<?php

namespace App\Http\Middleware;

use App\Models\Custodian;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

// Laravel's ThrottleRequests is on the middleware priority list, so it gets sorted ahead of
// verify.custodian.signed.request and would count rejected requests. This class isn't on that
// list, so it runs where it's listed: after the signature check.
class ThrottleVerifiedCustodian
{
    public function handle(Request $request, Closure $next): Response
    {
        $custodian = $request->attributes->get('custodian');
        if (!$custodian instanceof Custodian) {
            abort(401);
        }

        $key = 'verified-custodian:' . $custodian->id;
        $maxAttempts = (int) config('speedi.system.custodian_export_rate_limit');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'too many requests',
                'data' => null,
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => RateLimiter::availableIn($key),
            ]);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
