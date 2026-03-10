<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        // Return all users with their collection
        $users = User::with('collection')->get();
        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Only admins can create users from this endpoint.'], 403);
        }

        // Validate incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'avatar_url' => 'nullable|url|max:255',
            'nationality' => 'nullable|string|max:255',
            'user_type' => 'required|string|in:admin,editor,user',
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
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $authUser = $request->user();
        $isOwner = $authUser && $authUser->id === $user->id;
        $isAdmin = $authUser && $authUser->isAdmin();

        if (!$isOwner && !$isAdmin) {
            return response()->json(['message' => 'Forbidden. You can only update your own account.'], 403);
        }

        if (!$isAdmin && ($request->has('user_type') || $request->has('is_active'))) {
            return response()->json(['message' => 'Forbidden. Only admins can update role or activation status.'], 403);
        }

        // Validate incoming data (all fields optional for update)
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'avatar_url' => 'nullable|url|max:255',
            'nationality' => 'nullable|string|max:255',
            'is_active' => ($isAdmin ? 'sometimes|boolean' : 'prohibited'),
            'user_type' => ($isAdmin ? 'sometimes|string|in:admin,editor,user' : 'prohibited'),
        ]);

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
    public function destroy(User $user)
    {
        $authUser = request()->user();
        $isOwner = $authUser && $authUser->id === $user->id;
        $isAdmin = $authUser && $authUser->isAdmin();

        if (!$isOwner && !$isAdmin) {
            return response()->json(['message' => 'Forbidden. You can only delete your own account.'], 403);
        }

        $user->delete();
        return response()->json(null, 204);
    }

    /**
     * Upload and assign an avatar to the authenticated user.
     */
    public function uploadAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if (!empty($user->avatar_url)) {
            $oldPath = $this->storagePathFromPublicUrl($user->avatar_url);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $validated['avatar']->store('avatars', 'public');
        $url = Storage::url($path);

        $user->update([
            'avatar_url' => $url,
        ]);

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'avatar_url' => $url,
        ]);
    }

    /**
     * Convert a public storage URL into a disk path.
     */
    private function storagePathFromPublicUrl(string $url): ?string
    {
        $prefix = '/storage/';
        if (str_starts_with($url, $prefix)) {
            return substr($url, strlen($prefix));
        }

        return null;
    }
}
