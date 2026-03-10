<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CollectionPolicy
{
    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function update(User $user, Collection $collection): Response
    {
        return $collection->user_id === $user->id
            ? Response::allow()
            : Response::deny('Unauthorized. You can only update your own collection.');
    }

    public function delete(User $user, Collection $collection): Response
    {
        return $collection->user_id === $user->id
            ? Response::allow()
            : Response::deny('Unauthorized. You can only delete your own collection.');
    }
}
