<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->role === 'editor';
    }

    public function create(User $user): bool
    {
        return $user->role === 'editor';
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->role === 'editor';
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->role === 'editor';
    }
}