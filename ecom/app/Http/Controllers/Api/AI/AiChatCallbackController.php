<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiChatCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $requestId = $request->input('request_id');
        $success = $request->input('success');
        $answer = $request->input('answer');
        $error = $request->input('error');

        Log::info("AI Chat callback received", [
            'request_id' => $requestId,
            'success' => $success,
            'has_answer' => !is_null($answer),
        ]);

        if (!$requestId) {
            return response()->json(['message' => 'Missing request_id'], 400);
        }

        $data = $success
            ? ['success' => true, 'answer' => $answer]
            : ['success' => false, 'error' => $error ?? 'Unknown error'];

        Cache::put("chat_result:{$requestId}", $data, 600);

        return response()->json(['message' => 'ok']);
    }
}
