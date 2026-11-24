<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestResponse
{
    // List of sensitive fields (body keys and header names) to mask
    protected $sensitiveFields = ['password', 'token', 'access_token', 'authorization', 'cookie', 'set-cookie', 'unique_identifier'];
    // Max body length to log
    protected $maxBodyLength = 2048;

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // Mask sensitive fields in request body
        $body = $this->maskSensitive($request->all());
        $body = $this->truncateBody($body);

        // Log the incoming request
        $incoming = [
            'method' => $request->getMethod(),
            'url' => $request->fullUrl(),
            'headers' => $this->maskSensitive($request->headers->all()),
            'body' => $body,
            'user_id' => optional($request->user())->id,
        ];

        $response = $next($request);

        // Log the outgoing response
        $responseBody = method_exists($response, 'getContent') ? $response->getContent() : null;
        $responseBody = $this->truncateBody($this->maskJsonBody($responseBody));
        $outgoing = [
            'status' => $response->getStatusCode(),
            'headers' => $this->maskSensitive($response->headers->all()),
            'body' => $responseBody,
        ];

        Log::info('LogRequestResponse', [
            'request' => $incoming,
            'response' => $outgoing,
        ]);

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        $horizonPath = trim(config('horizon.path', 'horizon'), '/');

        return $request->is(
            $horizonPath,
            $horizonPath . '/*',
            'auth/horizon',
            'auth/horizon/*',
            // The response carries the Custodian's new API key
            'api/v1/custodians/*/credentials/rotate',
            // Responses are bulk researcher data
            'api/v1/custodian/export/*',
        );
    }

    protected function maskSensitive($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (in_array(strtolower($key), $this->sensitiveFields)) {
                    $value = '***';
                } elseif (is_array($value)) {
                    $value = $this->maskSensitive($value);
                }
            }
        }
        return $data;
    }

    protected function maskJsonBody(mixed $body): mixed
    {
        if (!is_string($body)) {
            return $body;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $this->maskSensitive($decoded) : $body;
    }

    protected function truncateBody($body)
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }
        if (is_string($body) && strlen($body) > $this->maxBodyLength) {
            return substr($body, 0, $this->maxBodyLength) . '...';
        }
        return $body;
    }
}
