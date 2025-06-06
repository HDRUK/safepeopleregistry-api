<?php

namespace App\Http\Middleware;

use Closure;
use Hash;
use App\Models\Custodian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustodianAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\JsonResponse)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (! $request->header('x-client-id')) {
            return response()->json([
                'message' => 'you must provide your Custodian key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $custodianKey = $request->header('x-client-id');
        $custodian = Custodian::where('client_id', $custodianKey)->first();
        if (! $custodian) {
            return response()->json([
                'message' => 'no known custodian matches the credentials provided',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! (Hash::check(
            $custodianKey . ':' . config('speedi.system.custodian_salt_1') . ':' . config('speedi.system.custodian_salt_2'),
            $custodian->calculated_hash
        ))) {
            return response()->json([
                'message' => 'the credentials provided are invalid',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
