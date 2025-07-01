<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class TerminateRequest
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $route = $request->route();
        $action = $route ? $route->getActionName() : null;

        if (is_null($request->route())) {
            return;
        }

        $memory = memory_get_usage(true) / 1024 / 1024;
        $peak = memory_get_peak_usage(true) / 1024 / 1024;
        $collected = gc_collect_cycles();

        Log::info('Memory usage after request', [
            'memory_MB' => round($memory, 2) . ' MB',
            'peak_MB' => round($peak, 2) . ' MB',
            'action' => $action,
            'collected_cycles' => $collected,
        ]);
    }
}
