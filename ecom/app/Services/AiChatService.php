<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url');
        $this->timeout = config('services.ai.timeout', 300);
    }

    public function chat(string $question, string $collection = 'ecom_documents', int $topK = 5): array
    {
        try {
          Log::info("SEnding request: ",[
            "timeout" =>$this->timeout ,
            "base url" => $this->baseUrl ,
            "collection" => $collection,
          ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/chat", [
                    'question' => $question,
                    'collection' => $collection,
                    'top_k' => $topK,
                ]);

                Log::info("this is the response : ", ["response" => $response]);

            if ($response->failed()) {
                Log::error('AI service responded with error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'answer' => 'Sorry, the AI service is temporarily unavailable.',
                    'sources' => [],
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to connect to AI service', [
                'message' =>"__". $e->getMessage(),
            ]);

            return [
                'success' => false,
                'answer' => 'Sorry, I could not connect to the AI service. Please try again later.',
                'sources' => [],
            ];
        }
    }
}
