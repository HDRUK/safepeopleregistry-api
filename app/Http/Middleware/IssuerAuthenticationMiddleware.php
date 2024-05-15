<?php

namespace App\Http\Middleware;

use Hash;
use Closure;
use Exception;

use App\Models\Issuer;
use App\Exceptions\UnauthorizedException;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IssuerAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\JsonResponse)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        try {
            if (!$request->header('x-issuer-key')) {
                return response()->json([
                    'message' => 'you must provide your Issuer key',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $issuerKey = $request->header('x-issuer-key');
            $issuer = Issuer::where('unique_identifier', $issuerKey)->first();
            if (!$issuer) {
                return response()->json([
                    'message' => 'no known issuer matches the credentials provided',
                ], Response::HTTP_UNAUTHORIZED);
            }

            if (!(Hash::check(
                $issuerKey . ':' . env('ISSUER_SALT_1') . ':' . env('ISSUER_SALT_2'),
                $issuer->calculated_hash
            ))) {
                return response()->json([
                    'message' => 'the credentials provided are invalid',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $next($request);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
