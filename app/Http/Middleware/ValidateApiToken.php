<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Integration;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized. API token is required.',
                'error' => 'Missing Bearer token in Authorization header'
            ], 401);
        }

        $integration = Integration::where('token', $token)
            ->where('status', true)
            ->first();

        if (!$integration) {
            return response()->json([
                'message' => 'Unauthorized. Invalid or inactive API token.',
                'error' => 'Authentication failed'
            ], 401);
        }

        $request->merge(['authenticated_integration' => $integration]);

        return $next($request);
    }
}
