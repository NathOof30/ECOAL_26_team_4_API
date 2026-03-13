<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{
    public function update(User $user, Item $item): Response
    {
        return $item->collection()?->user_id === $user->id
            ? Response::allow()
            : Response::deny('Unauthorized. You can only update items in your own collection.');
    }

    public function delete(User $user, Item $item): Response
    {
        return $item->collection()?->user_id === $user->id
            ? Response::allow()
            : Response::deny('Unauthorized. You can only delete items from your own collection.');
    }

    public function score(User $user, Item $item): Response
    {
        return $item->collection()?->user_id === $user->id
            ? Response::allow()
            : Response::deny('Unauthorized. You can only manage scores for items in your own collection.');
    }
}
