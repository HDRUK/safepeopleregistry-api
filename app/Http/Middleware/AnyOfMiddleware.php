<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;

class AnyOfMiddleware
{
    use Responses;
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            $middlewareClass = $this->resolveMiddlewareClass($guard);

            $dummyClosure = function($request){return $this->OKResponse([]);};
            $response = app($middlewareClass)->handle($request,$dummyClosure);

            if ($this->isSuccessfulResponse($response)) {
                return $next($request);
            }
        }

        return $this->ForbiddenResponse();
    }

    private function resolveMiddlewareClass($name)
    {
        $map = [
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'is_custodian' => \App\Http\Middleware\IsCustodian::class,
            'is_organisation' => \App\Http\Middleware\IsOrganisation::class,
            'is_owner' => \App\Http\Middleware\IsOwner::class,
            // add more mappings as needed
        ];

        return $map[$name] ?? abort(500, "Unknown middleware: $name");
    }

    private function isSuccessfulResponse($response): bool
    {
        return $response->getStatusCode() === 200;
    }
}
