<?php
use App\Http\Controllers\Api\AI\AdminAnalysisController;
use App\Http\Controllers\Api\AI\AiChatCallbackController;
use App\Http\Controllers\Api\AI\AIOrderController ;
use Illuminate\Support\Facades\Route;


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