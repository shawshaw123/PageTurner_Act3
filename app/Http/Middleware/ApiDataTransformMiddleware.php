<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ApiDataTransformMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Transform request data (snake_case to camelCase)
        $this->transformRequest($request);

        $response = $next($request);

        // Transform response data (snake_case to camelCase)
        if ($this->shouldTransformResponse($request, $response)) {
            $response = $this->transformResponse($response, $request);
        }

        return $response;
    }

    /**
     * Transform incoming request data from camelCase to snake_case.
     */
    protected function transformRequest(Request $request): void
    {
        if (!$request->isJson() || !$request->isMethod('POST') && !$request->isMethod('PUT') && !$request->isMethod('PATCH')) {
            return;
        }

        $input = $request->all();
        $transformed = $this->arrayKeysToSnakeCase($input);

        // Replace the request input
        $request->replace($transformed);
    }

    /**
     * Transform response data from snake_case to camelCase.
     */
    protected function transformResponse($response, Request $request)
    {
        if (!$response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        }

        $data = $response->getData(true);
        
        if (!is_array($data)) {
            return $response;
        }

        // Apply field filtering if requested
        if ($request->has('fields')) {
            $data = $this->filterFields($data, $request->get('fields'));
        }

        // Transform keys to camelCase
        $transformed = $this->arrayKeysToCamelCase($data);

        // Add metadata
        $transformed = $this->addResponseMetadata($transformed, $request);

        return $response->setData($transformed);
    }

    /**
     * Convert array keys from camelCase to snake_case.
     */
    protected function arrayKeysToSnakeCase(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = is_string($key) ? $this->camelToSnake($key) : $key;
            
            if (is_array($value)) {
                $result[$newKey] = $this->arrayKeysToSnakeCase($value);
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Convert array keys from snake_case to camelCase.
     */
    protected function arrayKeysToCamelCase(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = is_string($key) ? $this->snakeToCamel($key) : $key;
            
            if (is_array($value)) {
                $result[$newKey] = $this->arrayKeysToCamelCase($value);
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Convert camelCase to snake_case.
     */
    protected function camelToSnake(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Convert snake_case to camelCase.
     */
    protected function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    /**
     * Filter fields based on request parameter.
     */
    protected function filterFields(array $data, string $fields): array
    {
        $requestedFields = explode(',', $fields);
        $requestedFields = array_map('trim', $requestedFields);
        
        $result = [];
        
        foreach ($requestedFields as $field) {
            $fieldParts = explode('.', $field);
            $value = $this->getNestedValue($data, $fieldParts);
            
            if ($value !== null) {
                $this->setNestedValue($result, $fieldParts, $value);
            }
        }
        
        return $result;
    }

    /**
     * Get nested value from array using dot notation.
     */
    protected function getNestedValue(array $array, array $keys)
    {
        $current = $array;
        
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }
        
        return $current;
    }

    /**
     * Set nested value in array using dot notation.
     */
    protected function setNestedValue(array &$array, array $keys, $value): void
    {
        $current = &$array;
        
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
                return;
            }
            
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            
            $current = &$current[$key];
        }
    }

    /**
     * Add metadata to response.
     */
    protected function addResponseMetadata(array $data, Request $request): array
    {
        $metadata = [
            'timestamp' => now()->toISOString(),
            'requestId' => $request->header('X-Request-ID', uniqid()),
        ];

        // Add pagination metadata if present
        if (isset($data['data']) && isset($data['links'])) {
            $metadata['pagination'] = [
                'currentPage' => $data['current_page'] ?? null,
                'totalPages' => $data['last_page'] ?? null,
                'totalItems' => $data['total'] ?? null,
                'perPage' => $data['per_page'] ?? null,
            ];
        }

        // Add rate limit headers as metadata
        if ($request->headers->has('X-RateLimit-Limit')) {
            $metadata['rateLimit'] = [
                'limit' => $request->headers->get('X-RateLimit-Limit'),
                'remaining' => $request->headers->get('X-RateLimit-Remaining'),
                'tier' => $request->headers->get('X-RateLimit-Tier'),
            ];
        }

        // Wrap data if not already wrapped
        if (!isset($data['data'])) {
            return [
                'data' => $data,
                'meta' => $metadata,
            ];
        }

        $data['meta'] = $metadata;
        return $data;
    }

    /**
     * Determine if response should be transformed.
     */
    protected function shouldTransformResponse(Request $request, $response): bool
    {
        // Don't transform if it's not a JSON response
        if (!$response instanceof \Illuminate\Http\JsonResponse) {
            return false;
        }

        // Don't transform if explicitly disabled
        if ($request->header('X-Skip-Transform') === 'true') {
            return false;
        }

        // Don't transform error responses
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return true;
    }
}
