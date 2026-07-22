<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiChatController extends Controller
{
    public function __construct(
        protected AiChatService $chatService,
    ) {}

    public function ask(Request $request): JsonResponse
    {
        try{

            $request->validate([
                'question' => 'required|string|min:3|max:5000',
                ]);
                
                $result = $this->chatService->chat(
                    question: $request->input('question'),
                    collection: $request->input('collection', 'ecom_documents'),
                    topK: $request->input('top_k', 5),
                    customerId: $request->user()?->customer?->id,
                );
                    
                return response()->json($result);
        }catch(\Exception $e){
           $result= [
                'success' => false,
                'answer' => 'required|string|min:3|max:5000',
                'sources' => [],
            ];
            return response()->json($result);
        }
    }
}
