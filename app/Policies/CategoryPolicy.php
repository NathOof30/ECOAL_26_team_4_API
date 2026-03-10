<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->user_type, ['admin', 'editor'], true);
    }

    public function update(User $user, Category $category): bool
    {
        return in_array($user->user_type, ['admin', 'editor'], true);
    }

    public function delete(User $user, Category $category): bool
    {
        return in_array($user->user_type, ['admin', 'editor'], true);
    }
}
