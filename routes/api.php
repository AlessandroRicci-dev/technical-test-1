<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/auth', function (AuthRequest $request) {

    // The incoming request is valid... AuthRequest do the trick

    if (!Auth::attempt($request->validated())) {
        return response()->json([
            'error' => 'Unauthenticated',
            'message' => 'Invalid token or credentials'
        ], 401);
    }

    $user = User::where("email", $request->validated("email"))->first();
    return response()->json([
        'token' => $user->createToken('TokenApi')->plainTextToken
    ]);
});

// A route named "login" is necessary to handle sanctum redirect behaviour on failed authentication

Route::get('/auth-failed', function (Request $request) {

    return response()->json([
        'error' => 'Unauthenticated',
        'message' => 'Invalid token or credentials'
    ], 401);
})->name("login");
