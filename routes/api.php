<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('/auth', [AuthenticationController::class, 'Auth']);
// A route named "login" is necessary to handle sanctum redirect behaviour on failed authentication
Route::get('/auth-failed', [AuthenticationController::class, "unauthorizedResponse"])->name("login");
