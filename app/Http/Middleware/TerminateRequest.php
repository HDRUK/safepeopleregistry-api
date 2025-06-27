<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DebugLog;
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
        if (is_null($request->route())) {
            return;
        }
        DebugLog::create([
            'class' => $request->route()->getActionName(),
            'log' => 'Memory usage after request: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        ]);
    }
}
