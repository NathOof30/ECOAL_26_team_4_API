<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function create(User $user): bool
    {
        return $user->user_type === 'admin';
    }

    public function update(User $actor, User $target): Response
    {
        return $actor->user_type === 'admin' || $actor->id === $target->id
            ? Response::allow()
            : Response::deny('Unauthorized. You can only update your own profile.');
    }

    public function delete(User $actor, User $target): Response
    {
        if ($actor->user_type !== 'admin') {
            return Response::deny('Forbidden. Insufficient permissions.');
        }

        if ($actor->id === $target->id) {
            return Response::deny('You cannot delete your own account through this endpoint.');
        }

        return Response::allow();
    }
}
