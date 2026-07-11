<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class ProcessDocumentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->document->update(['status' => 'processing']);

        \Log::info('Processing document:', [
            'document_id' => $this->document->id,
        ]);

        try {
            $response = Http::timeout(config('services.ai.timeout', 300))
                ->post(config('services.ai.url') . '/documents/process', [
                    'document_id' => $this->document->id,
                    'file_path' => Storage::disk('public')->path($this->document->file_path),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                \Log::info('AI Service response', ['data' => $data]);
                $this->document->update(['status' => 'completed']);
            } else {
                \Log::error('AI Service error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->document->update(['status' => 'failed']);
            }
        } catch (\Exception $e) {
            \Log::error('Error calling AI service', [
                'error' => $e->getMessage(),
            ]);
            $this->document->update(['status' => 'failed']);
        }
    }
}
