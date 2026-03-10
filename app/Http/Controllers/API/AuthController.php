<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register a new user and return a token
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'avatar_url' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['user_type'] = 'user';
        $validated['is_active'] = true;

        $user = User::create($validated);

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Authenticate user and return a token
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => mb_strtolower(trim($validated['email'])),
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        if (! $user->is_active) {
            Auth::logout();

            return response()->json([
                'message' => 'This account is inactive.',
            ], 403);
        }

        RateLimiter::clear($credentials['email'].'|'.$request->ip());

        // Optional: Revoke existing tokens for a single-device login
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Revoke the user's token (Logout)
     */
    public function logout(Request $request)
    {
        // Require the user to be authenticated to logout
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
