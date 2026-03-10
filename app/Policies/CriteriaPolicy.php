<?php

namespace App\Policies;

use App\Models\Criteria;
use App\Models\User;

class CriteriaPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->user_type, ['admin', 'editor'], true);
    }

    public function update(User $user, Criteria $criteria): bool
    {
        return in_array($user->user_type, ['admin', 'editor'], true);
    }

    public function delete(User $user, Criteria $criteria): bool
    {
        return in_array($user->user_type, ['admin', 'editor'], true);
    }
}
