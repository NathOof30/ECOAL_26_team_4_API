<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Register a new user and return a token
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);
        $validated['user_type'] = 'user';
        $validated['is_active'] = true;

        $user = User::create($validated);

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    /**
     * Authenticate user and return a token
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $credentials = ['email' => $validated['email'], 'password' => $validated['password']];

        if (! Auth::attempt($credentials)) {
            Log::channel('audit')->warning('auth.login_failed', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return ApiResponse::error('Invalid login details', 401);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        if (! $user->is_active) {
            Auth::logout();

            Log::channel('audit')->warning('auth.login_inactive_user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return ApiResponse::error('This account is inactive.', 403);
        }

        RateLimiter::clear($credentials['email'].'|'.$request->ip());

        // Optional: Revoke existing tokens for a single-device login
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::channel('audit')->info('auth.login_success', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Revoke the user's token (Logout)
     */
    public function logout(Request $request)
    {
        // Require the user to be authenticated to logout
        Log::channel('audit')->info('auth.logout', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => [
                'message' => 'Successfully logged out',
            ],
        ]);
    }
}
