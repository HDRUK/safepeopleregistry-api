<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Organisation;
use App\Http\Traits\Responses;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Responds 404 to a request for an Organisation the User can't see (see
 * Organisation::scopeVisibleTo), so a restricted Organisation's existence isn't revealed.
 * Unknown ids pass through for the controller to handle as before.
 */
class EnsureOrganisationIsVisible
{
    use Responses;

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $parameter = 'id'): Response
    {
        $organisation = Organisation::find($request->route($parameter));

        if ($organisation && !$request->user()?->can('view', $organisation)) {
            return $this->NotFoundResponse();
        }

        return $next($request);
    }
}
