<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;


class AuthenticationController extends Controller
{
    public function Auth(AuthRequest $request): JsonResponse
    {
        // The incoming request is valid... AuthRequest do the trick

        if (!Auth::attempt($request->validated())) {
            return $this->unauthorizedResponse();
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'token' => $user->createToken('TokenApi')->plainTextToken,
        ]);
    }

    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'error'   => 'Unauthorized',
            'message' => 'Invalid token or credentials'
        ], 401);
    }
}
