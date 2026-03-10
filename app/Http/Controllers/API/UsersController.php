<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserPublicResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $query = User::with('collection');

        if (request()->filled('name')) {
            $query->where('name', 'like', '%'.request('name').'%');
        }

        if (request()->filled('nationality')) {
            $query->where('nationality', request('nationality'));
        }

        $sort = in_array(request('sort'), ['id', 'name', 'nationality'], true) ? request('sort') : 'id';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = min((int) request('per_page', 15), 100);

        $users = $query->orderBy($sort, $direction)->paginate($perPage)->appends(request()->query());

        return UserPublicResource::collection($users);
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
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Load the user's collection and return
        $user->load('collection');
        return new UserPublicResource($user);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $actor = $request->user();
        $isAdmin = $actor->user_type === 'admin';
        $validated = $request->validated();
        $wasActive = $user->is_active;

        if (! $isAdmin) {
            unset($validated['is_active'], $validated['user_type']);
        }

        // Hash password if it was provided
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        $user->refresh();

        if ($wasActive && $user->is_active === false) {
            $user->tokens()->delete();
        }

        return new UserResource($user);
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
