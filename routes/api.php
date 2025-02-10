<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\api\V1\OrderController;


// A route named "login" is necessary to handle sanctum redirect behaviour on failed authentication
Route::post('/auth', [AuthenticationController::class, 'Auth']);
Route::get('/auth-failed', [AuthenticationController::class, "unauthorizedResponse"])->name("login");

Route::prefix('V1')->group(function () {

    Route::prefix('order')->middleware(['auth:api'])->group(function () {
        Route::get('/', [OrderController::class, "index"]);
        Route::post('/', [OrderController::class, "store"]);
        Route::get('/{id}', [OrderController::class, "show"]);
        Route::patch('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
    });
});
