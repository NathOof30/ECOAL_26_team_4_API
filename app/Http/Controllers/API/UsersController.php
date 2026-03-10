<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

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
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

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
    public function update(UpdateUserRequest $request, User $user)
    {
        $actor = $request->user();
        $isAdmin = $actor->user_type === 'admin';
        $validated = $request->validated();

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
        $this->authorize('delete', $user);

        $user->delete();
        return response()->json(null, 204);
    }
}
