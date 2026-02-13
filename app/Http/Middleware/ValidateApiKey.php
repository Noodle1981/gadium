<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey || $apiKey !== config('services.grafana.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'API key invalida o no proporcionada.',
            ], 401);
        }

        return $next($request);
    }
}
