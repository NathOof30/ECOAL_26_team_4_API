<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserPublicResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        Log::channel('audit')->info('users.created', [
            'actor_id' => $request->user()->id,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'user_type' => $user->user_type,
            'is_active' => $user->is_active,
            'ip' => $request->ip(),
        ]);

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
        $oldUserType = $user->user_type;

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

            Log::channel('audit')->warning('users.deactivated', [
                'actor_id' => $actor->id,
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'previous_is_active' => $wasActive,
                'new_is_active' => $user->is_active,
                'tokens_revoked' => true,
                'ip' => $request->ip(),
            ]);
        }

        if ($oldUserType !== $user->user_type) {
            Log::channel('audit')->warning('users.role_changed', [
                'actor_id' => $actor->id,
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'previous_user_type' => $oldUserType,
                'new_user_type' => $user->user_type,
                'ip' => $request->ip(),
            ]);
        }

        if ($wasActive !== $user->is_active && ! ($wasActive && $user->is_active === false)) {
            Log::channel('audit')->info('users.active_status_changed', [
                'actor_id' => $actor->id,
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'previous_is_active' => $wasActive,
                'new_is_active' => $user->is_active,
                'ip' => $request->ip(),
            ]);
        }

        return new UserResource($user->load('collection'));
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        Log::channel('audit')->warning('users.deleted', [
            'actor_id' => $request->user()->id,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'target_user_type' => $user->user_type,
            'ip' => $request->ip(),
        ]);

        $user->delete();
        return response()->json(null, 204);
    }
}
