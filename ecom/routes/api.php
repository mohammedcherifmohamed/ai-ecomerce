<?php
use App\Http\Controllers\Api\AI\AdminAnalysisController;
use App\Http\Controllers\Api\AI\AiChatCallbackController;
use App\Http\Controllers\Api\AI\AIOrderController ;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Analysis\AiMiniCpmController;


Route::prefix('ai')->middleware('auth.ai')->group(function(){

    Route::post('/orders/status',[AIOrderController::class ,  "status"]);

    Route::post('/orders/cancel',[AIOrderController::class ,  "cancel"]);

    Route::post('/inquiry/create',[AIOrderController::class ,  "createInquiry"]);

    Route::post('/inquiries/search', [AdminAnalysisController::class, 'searchInquiries']);
    Route::post('/customers/summary', [AdminAnalysisController::class, 'customerSummary']);
    Route::post('/orders/trends', [AdminAnalysisController::class, 'trends']);
    Route::post('/tickets/analysis', [AdminAnalysisController::class, 'ticketAnalysis']);

    Route::post('/chat/callback', [AiChatCallbackController::class, 'handle']);

});


Route::prefix('ai')->group(function(){
    Route::get("/health",function(){
        return 'ok';
    });
});


// routes MiciCpm5
 Route::get('/miniHealth',function(){
        return "success";
});

Route::middleware(['auth:sanctum'])->group(function () {
   
    Route::post('/ai/query', [AiMiniCpmController::class, 'submitQuery']);
    Route::get('/ai/query/{job}/status', [AiMiniCpmController::class, 'getStatus']);
});

Route::post('/ai/webhook', [AiMiniCpmController::class, 'webhook'])
    ->name('api.ai.webhook');