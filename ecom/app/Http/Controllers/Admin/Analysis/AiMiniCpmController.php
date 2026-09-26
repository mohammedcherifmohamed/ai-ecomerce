<?php

namespace App\Http\Controllers\Admin\Analysis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\AIQueryJob;

use App\Services\Ai\MiniCpmAIQueryService;


class AiMiniCpmController extends Controller
{
    public function __construct(
        private MiniCpmAIQueryService $aiminiCpmQueryService 
    ){}

    public function submitQuery(Request $req): JsonResponse
    {
        $req->validate([
            "query" => "required|string|max:5000",
        ]);

        $correlationId = $req->header('X-Correlation-ID') ?? (string) Str::uuid();

        $job = AIQueryJob::create([
            'id' => (string) Str::uuid(),
            'user_id' => $req->user()->id,
            'user_input' => $req->input('query'),
            'status' => 'pending',
        ]);

        Log::channel('ai_query')->info('AI Query submitted', [
            'correlation_id' => $correlationId,
            'job_id' => $job->id,
            'user_id' => $job->user_id,
            'query_preview' => Str::limit($job->user_input, 100),
        ]);

        // Dispatch to queue
        $this->aiminiCpmQueryService->processQuery($job);

        return response()->json([
            'job_id' => $job->id,
            'status' => 'pending',
            'message' => 'Query submitted successfully',
            'check_status_url' => route('api.ai.query.status', ['job' => $job->id]),
            'correlation_id' => $correlationId,
        ], 202);

    }

    public function getStatus(AIQueryJob $job): JsonResponse
    {
        // Authorization check
        if ($job->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'result' => $job->status === 'completed' ? $job->ai_response : null,
            'error' => $job->status === 'failed' ? $job->error_message : null,
            'generated_sql' => $job->generated_sql,
            'query_result' => $job->query_result,
            'completed_at' => $job->completed_at,
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $correlationId = $request->header('X-Correlation-ID') ?? 'unknown';

        // Verify webhook signature
        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            Log::channel('ai_query')->warning('Invalid webhook signature', [
                'correlation_id' => $correlationId,
                'signature' => $signature,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->validate([
            'job_id' => 'required|string|exists:ai_query_jobs,id',
            'status' => 'required|in:completed,failed',
            'result' => 'required_if:status,completed|nullable|string',
            'error' => 'required_if:status,failed|nullable|string',
            'sql_query' => 'nullable|string',
            'query_result' => 'nullable|array',
        ]);

        $job = AIQueryJob::findOrFail($data['job_id']);
        
        $job->update([
            'status' => $data['status'],
            'ai_response' => $data['result'] ?? null,
            'error_message' => $data['error'] ?? null,
            'generated_sql' => $data['sql_query'] ?? null,
            'query_result' => $data['query_result'] ?? null,
            'completed_at' => now(),
        ]);

        Log::channel('ai_query')->info('Webhook received from AI service', [
            'correlation_id' => $correlationId,
            'job_id' => $job->id,
            'status' => $data['status'],
            'has_result' => !empty($data['result']),
            'has_error' => !empty($data['error']),
            'has_sql' => !empty($data['sql_query']),
            'has_query_result' => !empty($data['query_result']),
        ]);

        // Optional: Send notification to user via websocket/email
        // event(new AIQueryCompleted($job));

        return response()->json(['status' => 'ok']);
    }
    private function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }
        
        $expected = hash_hmac('sha256', $payload, config('services.ai.webhook_secret'));
        return hash_equals($expected, $signature);
    }

}
