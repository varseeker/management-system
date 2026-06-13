<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInventoryApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = config('inventory.api_token');

        if (! $configured) {
            abort(503, 'Inventory API token is not configured.');
        }

        $token = $request->bearerToken();

        if (! $token || ! hash_equals($configured, $token)) {
            abort(401, 'Invalid inventory API token.');
        }

        return $next($request);
    }
}
