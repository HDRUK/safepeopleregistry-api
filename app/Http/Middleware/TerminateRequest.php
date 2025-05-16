<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use Illuminate\Contracts\Foundation\Application;

class TerminateRequest
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        Log::info('Request: ' . $request->getMethod() . ' ' . $request->getRequestUri());
        Log::info('Response: ' . $response->getStatusCode());

        $memoryUsage = memory_get_usage(true);

        Log::info('Memory usage after request: ' . round($memoryUsage / 1024 / 1024, 2) . ' MB');
    }
}