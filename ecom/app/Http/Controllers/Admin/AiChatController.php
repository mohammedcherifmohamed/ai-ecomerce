<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiChatController extends Controller
{
    public function __construct(
        protected AdminAiChatService $chatService,
    ) {}

    public function index()
    {
        return view('admin.ai_chat');
    }

    public function ask(Request $request): JsonResponse
    {
        try {
            set_time_limit(300);

            $request->validate([
                'question' => 'required|string|min:3|max:5000',
            ]);

            $result = $this->chatService->chat(
                question: $request->input('question'),
                collection: $request->input('collection', 'ecom_documents'),
                topK: $request->input('top_k', 5),
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'answer' => 'Sorry, something went wrong.',
                'sources' => [],
            ]);
        }
    }

    public function result(string $requestId): JsonResponse
    {
        $data = Cache::get("chat_result:{$requestId}");

        if (!$data) {
            return response()->json([
                'status' => 'processing',
            ]);
        }

        return response()->json([
            'status' => 'completed',
            'success' => $data['success'] ?? false,
            'answer' => $data['answer'] ?? null,
        ]);
    }
}
