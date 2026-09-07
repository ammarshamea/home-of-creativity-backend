<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(): JsonResponse
    {
        return response()->json([
            'data' => null,
            'message' => 'Accounts are created only through the Telegram bot.',
        ], 403);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'data' => [
                'token' => $user->createToken('api')->plainTextToken,
                'user' => UserResource::make($user),
            ],
            'message' => 'ok',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'data' => null,
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user())->additional(['message' => 'ok']);
    }
}
