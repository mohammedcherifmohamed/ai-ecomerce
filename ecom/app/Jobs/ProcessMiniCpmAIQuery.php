<?php
namespace App\Jobs;

use App\Models\AIQueryJob;
use App\Services\Ai\MiniCpmAIQueryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessMiniCpmAIQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    protected AIQueryJob $aiQueryJob;

    public function __construct(AIQueryJob $job)
    {
        $this->aiQueryJob = $job;
    }

    public function handle(MiniCpmAIQueryService $service): void
    {
        $correlationId = (string) Str::uuid();
        
        Log::channel('ai_query')->info('Processing AI query job', [
            'correlation_id' => $correlationId,
            'job_id' => $this->aiQueryJob->id,
            'attempt' => $this->attempts(),
        ]);

        $service->sendToAIService($this->aiQueryJob);
    }

    public function failed(\Throwable $exception): void
    {
        $this->aiQueryJob->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::channel('ai_query')->error('AI Query job failed', [
            'job_id' => $this->aiQueryJob->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}