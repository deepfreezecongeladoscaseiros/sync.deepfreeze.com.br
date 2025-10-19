<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $responseTimeMs = round(($endTime - $startTime) * 1000);

        $this->logRequest($request, $response, $responseTimeMs);

        return $response;
    }

    protected function logRequest(Request $request, Response $response, int $responseTimeMs): void
    {
        try {
            $statusCode = $response->getStatusCode();
            $responseContent = $response->getContent();
            
            $responseBody = null;
            if ($statusCode >= 400 || $responseTimeMs > 1000) {
                $responseBody = json_decode($responseContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $responseBody = ['raw' => substr($responseContent, 0, 1000)];
                }
            }

            $errorMessage = null;
            $errorType = null;
            if ($statusCode >= 400) {
                if (is_array($responseBody) && isset($responseBody['message'])) {
                    $errorMessage = $responseBody['message'];
                }
                $errorType = $this->getErrorType($statusCode);
            }

            $integration = $request->get('authenticated_integration');
            
            ApiRequestLog::create([
                'integration_id' => $integration?->id,
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'full_url' => $request->fullUrl(),
                'query_params' => $request->query->all(),
                'request_body' => $request->method() !== 'GET' ? $request->except(['password', 'token']) : null,
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'response_time_ms' => $responseTimeMs,
                'error_message' => $errorMessage,
                'error_type' => $errorType,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log API request: ' . $e->getMessage());
        }
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        $allowedHeaders = [
            'accept',
            'content-type',
            'user-agent',
            'x-requested-with',
            'referer',
        ];

        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $allowedHeaders)) {
                $sanitized[$key] = is_array($value) ? $value[0] : $value;
            }
        }

        return $sanitized;
    }

    protected function getErrorType(int $statusCode): string
    {
        return match (true) {
            $statusCode === 400 => 'Bad Request',
            $statusCode === 401 => 'Unauthorized',
            $statusCode === 403 => 'Forbidden',
            $statusCode === 404 => 'Not Found',
            $statusCode === 422 => 'Validation Error',
            $statusCode === 429 => 'Rate Limit Exceeded',
            $statusCode >= 500 => 'Server Error',
            $statusCode >= 400 => 'Client Error',
            default => 'Unknown Error',
        };
    }
}
