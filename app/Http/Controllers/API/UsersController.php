<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    protected function publicUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'nationality' => $user->nationality,
            'collection' => $user->collection,
        ];
    }

    /**
     * Display a listing of all users.
     */
    public function index()
    {
        // Return all users with their collection
        $users = User::with('collection')->get();
        return response()->json($users->map(fn (User $user) => $this->publicUserData($user)));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        // Validate incoming data
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
            'user_type' => 'sometimes|string|in:admin,editor,user',
            'is_active' => 'sometimes|boolean',
        ]);

        // Hash the password before saving
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Load the user's collection and return
        $user->load('collection');
        return response()->json($this->publicUserData($user));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $actor = $request->user();
        $isAdmin = $actor->user_type === 'admin';

        if (! $isAdmin && $actor->id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own profile.',
            ], 403);
        }

        // Validate incoming data (all fields optional for update)
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'avatar_url' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'user_type' => 'sometimes|string|in:admin,editor,user',
        ]);

        if (! $isAdmin) {
            unset($validated['is_active'], $validated['user_type']);
        }

        // Hash password if it was provided
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account through this endpoint.',
            ], 403);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}
