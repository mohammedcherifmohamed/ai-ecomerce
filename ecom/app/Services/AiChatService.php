<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(
        protected OrderService $orderService,
        protected InquiryService $inquiryService,
    ) {
        $this->baseUrl = config('services.ai.url');
        $this->timeout = config('services.ai.timeout', 300);
    }

    public function chat(string $question, string $collection = 'ecom_documents', int $topK = 5, ?int $customerId = null): array
    {
        try {
            Log::info("Step 1: Sending to AI service (no tool execution)");

            $payload = [
                'question' => $question,
                'collection' => $collection,
                'top_k' => $topK,
                'execute_tools' => false,
            ];

            if ($customerId) {
                $payload['customer_id'] = $customerId;
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/chat", $payload);

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

            $result = $response->json();
            $answer = $result['answer'] ?? '';

            if (!str_contains($answer, 'TOOL_CALL:')) {
                Log::info("No tool call needed, returning direct response");
                return $result;
            }

            Log::info("Step 2: AI requested tool call, executing locally");

            $toolCall = $this->parseToolCall($answer);
            if (!$toolCall) {
                return $this->error('Invalid tool call from AI.');
            }

            $toolResult = $this->executeTool($toolCall, $customerId);

            Log::info("Tool executed locally", ['result' => $toolResult]);

            Log::info("Step 3: Submitting tool result for async processing with callback");

            $callbackUrl = url('/api/ai/chat/callback');
            $asyncPayload = [
                'question' => $question,
                'collection' => $collection,
                'top_k' => $topK,
                'tool_result' => $toolResult,
                'callback_url' => $callbackUrl,
            ];

            if ($customerId) {
                $asyncPayload['customer_id'] = $customerId;
            }

            $asyncResponse = Http::timeout(30)
                ->post("{$this->baseUrl}/chat/async", $asyncPayload);

            if ($asyncResponse->failed()) {
                Log::error('AI service async submission failed');
                return $this->error('Failed to start async processing.');
            }

            $requestId = $asyncResponse->json('request_id');
            Log::info("Async submitted: {$requestId}, callback: {$callbackUrl}");

            return [
                'success' => true,
                'request_id' => $requestId,
                'status' => 'processing',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to connect to AI service', [
                'message' => "__" . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'answer' => 'Sorry, I could not connect to the AI service. Please try again later.',
                'sources' => [],
            ];
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

    protected function executeTool(array $toolCall, ?int $customerId): array
    {
        $toolName = $toolCall['tool'];

        if ($toolName === 'get_order_status') {
            $toolCustomerId = isset($toolCall['customer_id']) ? (int) $toolCall['customer_id'] : $customerId;
            $toolOrderId = isset($toolCall['order_id']) ? (int) $toolCall['order_id'] : null;
            return (!$toolCustomerId || !$toolOrderId)
                ? ['success' => false, 'error' => 'Missing customer_id or order_id for get_order_status']
                : $this->orderService->getOrderStatusForAI($toolCustomerId, $toolOrderId);
        }

        if ($toolName === 'cancel_order') {
            $toolCustomerId = isset($toolCall['customer_id']) ? (int) $toolCall['customer_id'] : $customerId;
            $toolOrderId = isset($toolCall['order_id']) ? (int) $toolCall['order_id'] : null;
            return (!$toolCustomerId || !$toolOrderId)
                ? ['success' => false, 'error' => 'Missing customer_id or order_id for cancel_order']
                : $this->orderService->cancelOrderForAI($toolCustomerId, $toolOrderId);
        }

        if ($toolName === 'create_inquiry') {
            $inquiryText = $toolCall['inquiry'] ?? null;
            return !$inquiryText
                ? ['success' => false, 'error' => 'Missing inquiry text for create_inquiry']
                : ['success' => true, 'inquiry_id' => $this->inquiryService->create(
                    inquiry: $inquiryText,
                    category: $toolCall['category'] ?? null,
                )->id];
        }

        return ['success' => false, 'error' => "Unknown tool: {$toolName}"];
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
