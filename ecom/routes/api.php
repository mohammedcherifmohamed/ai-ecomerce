<?php
use App\Http\Controllers\Api\AI\AIOrderController ;
use Illuminate\Support\Facades\Route;


Route::prefix('ai')->middleware('auth.ai')->group(function(){

    Route::post('/orders/status',[AIOrderController::class ,  "status"]);

    Route::post('/orders/cancel',[AIOrderController::class ,  "cancel"]);

    Route::post('/inquiry/create',[AIOrderController::class ,  "createInquiry"]);

});


Route::prefix('ai')->group(function(){
    Route::get("/health",function(){
        return 'ok';
    });
});