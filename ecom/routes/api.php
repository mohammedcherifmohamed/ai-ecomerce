<?php
use App\Http\Controllers\Api\AI\AIOrderController ;
use Illuminate\Support\Facades\Route;


Route::prefix('ai')->group(function(){

    Route::post('/orders/status',[AIOrderController::class ,  "status"]);

    Route::post('/orders/cancle',[AIOrderController::class ,  "status"]);

});
