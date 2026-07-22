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
            // Step 1: Ask AI without executing tools (AI returns TOOL_CALL as-is)
            Log::info("Step 1: Sending to AI service (no tool execution)", [
                "base url" => $this->baseUrl,
                "collection" => $collection,
            ]);

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

            // Step 2: Check if AI requested a tool call
            if (str_contains($answer, 'TOOL_CALL:')) {
                Log::info("Step 2: AI requested tool call, executing locally");

                // Extract the TOOL_CALL line
                $lines = explode("\n", $answer);
                $toolCallLine = '';
                foreach ($lines as $line) {
                    if (str_contains($line, 'TOOL_CALL:')) {
                        $toolCallLine = trim($line);
                        break;
                    }
                }

                // Parse TOOL_CALL:{"tool":"name",...}
                $toolCallStr = str_replace('TOOL_CALL:', '', $toolCallLine);
                $toolCall = json_decode($toolCallStr, true);
                $toolName = $toolCall['tool'];

                // Execute the tool locally (no HTTP call)
                if ($toolName === 'get_order_status') {
                    $toolCustomerId = (int) $toolCall['customer_id'];
                    $toolOrderId = (int) $toolCall['order_id'];
                    $toolResult = $this->orderService->getOrderStatusForAI($toolCustomerId, $toolOrderId);
                } elseif ($toolName === 'cancel_order') {
                    $toolCustomerId = (int) $toolCall['customer_id'];
                    $toolOrderId = (int) $toolCall['order_id'];
                    $toolResult = $this->orderService->cancelOrderForAI($toolCustomerId, $toolOrderId);
                } elseif ($toolName === 'create_inquiry') {
                    $inquiry = $this->inquiryService->create(
                        inquiry: $toolCall['inquiry'],
                        category: $toolCall['category'] ?? null,
                    );
                    $toolResult = [
                        'success' => true,
                        'inquiry_id' => $inquiry->id,
                    ];
                } else {
                    $toolResult = ['success' => false, 'error' => "Unknown tool: {$toolName}"];
                }

                Log::info("Tool executed locally", ['result' => $toolResult]);

                // Step 3: Send tool result back to AI for final answer
                Log::info("Step 3: Sending tool result to AI for final answer");

                $finalPayload = [
                    'question' => $question,
                    'collection' => $collection,
                    'top_k' => $topK,
                    'tool_result' => $toolResult,
                ];

                if ($customerId) {
                    $finalPayload['customer_id'] = $customerId;
                }

                $finalResponse = Http::timeout($this->timeout)
                    ->post("{$this->baseUrl}/chat", $finalPayload);

                if ($finalResponse->failed()) {
                    Log::error('AI service final step failed');
                    return $result;
                }

                $finalResult = $finalResponse->json();

                Log::info("this is the response : ", ["response" => $finalResponse]);

                return $finalResult;
            }

            // No tool call needed, return AI response directly
            Log::info("No tool call needed, returning direct response");
            return $result;

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
}
