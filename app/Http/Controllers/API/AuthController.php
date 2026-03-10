<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'password' => 'required|string|min:8',
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive.',
            ], 403);
        }

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
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get authenticated user with collections data and formatted URLs.
     */
    public function currentUser(Request $request)
    {
        $user = $request->user()->load('collection');
        $avatarUrl = $user->avatar_url;
        
        if ($avatarUrl && !str_starts_with($avatarUrl, 'http')) {
            $avatarUrl = $this->getAbsoluteStorageUrl(str_replace('/storage/', '', $avatarUrl));
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $avatarUrl,
            'nationality' => $user->nationality,
            'is_active' => $user->is_active,
            'user_type' => $user->user_type,
            'created_at' => $user->created_at,
            'collection' => $user->collection,
            'has_collection' => (bool) $user->collection,
        ]);
    }

    /**
     * Convert a relative storage path to an absolute backend URL.
     */
    private function getAbsoluteStorageUrl(string $relativePath): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        return "{$baseUrl}/storage/{$relativePath}";
    }
}
