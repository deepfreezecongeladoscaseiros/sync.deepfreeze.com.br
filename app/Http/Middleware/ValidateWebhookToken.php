<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Integration;
use Symfony\Component\HttpFoundation\Response;

class ValidateWebhookToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token not provided. Use Authorization: Bearer {token}'
            ], 401);
        }

        $integration = Integration::where('token', $token)->first();

        if (!$integration) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid token'
            ], 401);
        }

        $request->merge(['integration' => $integration]);
        $request->merge(['integration_id' => $integration->id]);

        return $next($request);
    }
}
