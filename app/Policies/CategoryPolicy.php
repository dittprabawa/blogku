<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
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

    public function update(User $user, Category $category): bool
    {
        return $user->role === 'editor';
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->role === 'editor';
    }
}
