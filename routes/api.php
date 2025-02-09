<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('/auth', [AuthenticationController::class, 'Auth']);
// A route named "login" is necessary to handle sanctum redirect behaviour on failed authentication
Route::get('/auth-failed', [AuthenticationController::class, "unauthorizedResponse"])->name("login");


Route::prefix('order')->middleware(['auth:api'])->group(function () {
    Route::get('/', [OrderController::class, "index"]);
    Route::post('/', [OrderController::class, "store"]);
    Route::get('/{id}', [OrderController::class, "show"]);
    Route::patch('/{id}', [OrderController::class, 'update']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});
