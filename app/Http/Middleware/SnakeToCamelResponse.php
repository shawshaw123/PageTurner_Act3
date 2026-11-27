<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SnakeToCamelResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $response->headers->get('Content-Type') === 'application/json' &&
            json_decode($response->getContent()) !== null
        ) {
            $content = json_decode($response->getContent(), true);
            $camelCaseContent = $this->transformKeysToCamelCase($content);
            $response->setContent(json_encode($camelCaseContent));
        }

        return $response;
    }

    private function transformKeysToCamelCase($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = is_string($key) ? lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key)))) : $key;
            $result[$camelKey] = is_array($value) ? $this->transformKeysToCamelCase($value) : $value;
        }

        return $result;
    }
}
