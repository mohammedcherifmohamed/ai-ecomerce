<?php

namespace App\Http\Controllers\Admin\Analysis;

use App\Http\Controllers\Controller;
use App\Models\AIQueryJob;
use App\Services\Ai\MiniCpmAIQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiMiniCpmWebController extends Controller
{
    public function __construct(
        protected MiniCpmAIQueryService $aiQueryService,
    ) {}

    public function index(): View
    {
        $recentJobs = AIQueryJob::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $aiServiceStatus = $this->checkAiServiceHealth();

        return view('admin.ai_query_agent', [
            'recentJobs' => $recentJobs,
            'aiServiceStatus' => $aiServiceStatus,
        ]);
    }

    private function checkAiServiceHealth(): bool
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-API-Key' => config('services.ai.api_key')])
                ->get(config('services.ai.url') . '/health');
            
            return $response->successful() && ($response->json('database') ?? false);
        } catch (\Exception $e) {
            Log::channel('ai_query')->warning('AI service health check failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function submitQuery(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:5000',
        ]);

        $correlationId = $request->header('X-Correlation-ID') ?? (string) Str::uuid();

        $job = AIQueryJob::create([
            'id' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'user_input' => $request->input('query'),
            'status' => 'pending',
        ]);

        Log::channel('ai_query')->info('AI Query submitted via web', [
            'correlation_id' => $correlationId,
            'job_id' => $job->id,
            'user_id' => $job->user_id,
            'query_preview' => Str::limit($job->user_input, 100),
        ]);

        $this->aiQueryService->processQuery($job);

        return response()->json([
            'job_id' => $job->id,
            'status' => 'pending',
            'message' => 'Query submitted successfully',
            'check_status_url' => route('admin.ai.query.status', ['job' => $job->id]),
            'correlation_id' => $correlationId,
        ], 202);
    }

    public function getStatus(AIQueryJob $job): JsonResponse
    {
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

    public function history(): JsonResponse
    {
        $jobs = AIQueryJob::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($jobs);
    }
}