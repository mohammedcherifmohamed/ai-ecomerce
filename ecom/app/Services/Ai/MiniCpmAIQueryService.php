<?php
namespace App\Services\Ai;

use App\Models\AIQueryJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessMiniCpmAIQuery;
use Illuminate\Support\Str;

class MiniCpmAIQueryService
{
    public function processQuery(AIQueryJob $job): void
    {
        dispatch(new ProcessMiniCpmAIQuery($job));
    }

    public function sendToAIService(AIQueryJob $job): void
    {
        $correlationId = (string) Str::uuid();
        
        Log::channel('ai_query')->info('Sending query to AI service', [
            'correlation_id' => $correlationId,
            'job_id' => $job->id,
            'user_id' => $job->user_id,
            'query_preview' => Str::limit($job->user_input, 100),
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => config('services.ai.api_key'),
                    'Content-Type' => 'application/json',
                    'X-Correlation-ID' => $correlationId,
                ])
                ->post(config('services.ai.url') . '/process-query', [
                    'job_id' => $job->id,
                    'query' => $job->user_input,
                    'webhook_url' => route('api.ai.webhook'),
                ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to send query to AI service: ' . $response->body());
            }

            $job->update(['status' => 'processing']);

            Log::channel('ai_query')->info('Query sent to AI service successfully', [
                'correlation_id' => $correlationId,
                'job_id' => $job->id,
                'ai_response' => $response->json(),
            ]);

        } catch (\Exception $e) {
            Log::channel('ai_query')->error('AI Service communication error', [
                'correlation_id' => $correlationId,
                'job_id' => $job->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $job->update([
                'status' => 'failed',
                'error_message' => 'Failed to communicate with AI service',
            ]);
        }
    }
}