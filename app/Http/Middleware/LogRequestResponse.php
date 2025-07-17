<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestResponse
{
    // List of sensitive fields to mask
    protected $sensitiveFields = ['password', 'token', 'access_token', 'authorization'];
    // Max body length to log
    protected $maxBodyLength = 2048;

    public function handle(Request $request, Closure $next)
    {
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
        $responseBody = $this->truncateBody($responseBody);
        $outgoing = [
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'body' => $responseBody,
        ];

        Log::info('LogRequestResponse', [
            'request' => $incoming,
            'response' => $outgoing,
        ]);

        return $response;
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
