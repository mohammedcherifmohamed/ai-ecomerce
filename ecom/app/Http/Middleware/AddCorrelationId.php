<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AddCorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header('X-Correlation-ID') ?? (string) Str::uuid();
        
        // Store in request for easy access
        $request->merge(['correlation_id' => $correlationId]);
        
        $response = $next($request);
        
        // Add correlation ID to response headers
        $response->headers->set('X-Correlation-ID', $correlationId);
        
        return $response;
    }
}