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
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
            'is.custodian' => \App\Http\Middleware\IsCustodian::class,
            'is.organisation' => \App\Http\Middleware\IsOrganisation::class,
            'is.owner' => \App\Http\Middleware\IsOwner::class,
            // add more mappings as needed
        ];

        [$middlewareName] = explode(':', $name, 2); 
        return $map[$middlewareName] ?? abort(500, "Unknown middleware: $middlewareName");
    }

    private function isSuccessfulResponse($response): bool
    {
        return $response->getStatusCode() === 200;
    }
}
