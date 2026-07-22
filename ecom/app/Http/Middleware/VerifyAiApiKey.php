<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAiApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.ai_api_key');

        if (!$expected) {
            return response()->json(['message' => 'AI API key not configured.'], 500);
        }

        $provided = $request->bearerToken();

        if (!$provided || $provided !== $expected) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
