<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAiChatService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(
        protected AdminAnalysisService $analysisService,
    ) {
        $this->baseUrl = config('services.ai.url');
        $this->timeout = config('services.ai.timeout', 300);
    }

    public function chat(string $question, string $collection = 'ecom_documents', int $topK = 5): array
    {
        try {
            Log::info("Admin AI Step 1: Sending to AI service (no tool execution)");

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/admin", [
                    'question' => $question,
                    'collection' => $collection,
                    'top_k' => $topK,
                    'execute_tools' => false,
                ]);

            if ($response->failed()) {
                return $this->error('Sorry, the AI service is temporarily unavailable.');
            }

            $result = $response->json();
            $answer = $result['answer'] ?? '';

            if (!str_contains($answer, 'TOOL_CALL:')) {
                Log::info("Admin AI: No tool call needed, returning direct response");
                return $result;
            }

            Log::info("Admin AI Step 2: AI requested tool call, executing locally");

            $toolCall = $this->parseToolCall($answer);
            if (!$toolCall) {
                return $this->error('Invalid tool call from AI.');
            }

            $toolResult = $this->executeTool($toolCall);

            Log::info("Admin AI Step 3: Submitting tool result for async processing with callback");

            $callbackUrl = url('/api/ai/chat/callback');

            $asyncResponse = Http::timeout(30)
                ->post("{$this->baseUrl}/chat/admin/async", [
                    'question' => $question,
                    'collection' => $collection,
                    'top_k' => $topK,
                    'tool_result' => $toolResult,
                    'callback_url' => $callbackUrl,
                ]);

            if ($asyncResponse->failed()) {
                return $this->error('Failed to submit for async processing.');
            }

            $requestId = $asyncResponse->json('request_id');
            Log::info("Admin AI async submitted: {$requestId}, callback: {$callbackUrl}");

            return [
                'success' => true,
                'request_id' => $requestId,
                'status' => 'processing',
            ];

        } catch (\Exception $e) {
            Log::error('Admin AI service connection failed', ['message' => $e->getMessage()]);
            return $this->error('Sorry, I could not connect to the AI service. Please try again later.');
        }
    }

    protected function parseToolCall(string $answer): ?array
    {
        $lines = explode("\n", $answer);
        $toolCallLine = '';
        foreach ($lines as $line) {
            if (str_contains($line, 'TOOL_CALL:')) {
                $toolCallLine = trim($line);
                break;
            }
        }

        if (!$toolCallLine) {
            return null;
        }

        $toolCallPos = strpos($toolCallLine, 'TOOL_CALL:');
        $toolCallStr = $toolCallPos !== false
            ? substr($toolCallLine, $toolCallPos + 10)
            : $toolCallLine;

        $toolCall = json_decode($toolCallStr, true);
        return (is_array($toolCall) && isset($toolCall['tool'])) ? $toolCall : null;
    }

    protected function executeTool(array $toolCall): array
    {
        $toolName = $toolCall['tool'];

        return match ($toolName) {
            'search_inquiries' => $this->analysisService->searchInquiries(
                keyword: $toolCall['keyword'] ?? null,
                category: $toolCall['category'] ?? null,
                dateFrom: $toolCall['date_from'] ?? null,
                dateTo: $toolCall['date_to'] ?? null,
                limit: $toolCall['limit'] ?? null,
            ),
            'customer_summary' => $this->analysisService->customerSummary(
                customerId: $toolCall['customer_id'] ?? null,
                email: $toolCall['email'] ?? null,
            ),
            'trends_statistics' => $this->analysisService->trends(
                period: $toolCall['period'] ?? null,
                dateFrom: $toolCall['date_from'] ?? null,
                dateTo: $toolCall['date_to'] ?? null,
            ),
            'ticket_analysis' => $this->analysisService->ticketAnalysis(
                dateFrom: $toolCall['date_from'] ?? null,
                dateTo: $toolCall['date_to'] ?? null,
                category: $toolCall['category'] ?? null,
            ),
            default => ['success' => false, 'error' => "Unknown admin tool: {$toolName}"],
        };
    }

    protected function error(string $message): array
    {
        return [
            'success' => false,
            'answer' => $message,
            'sources' => [],
        ];
    }
}
