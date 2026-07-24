<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Admin selalu boleh melakukan apapun — cek ini jalan duluan
     * sebelum method lain di bawah dievaluasi.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    /**
     * Semua role yang terautentikasi boleh membuat post baru.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'editor', 'author']);
    }

    /**
     * Editor boleh edit post siapa saja.
     * Author cuma boleh edit post miliknya sendiri.
     */
    public function update(User $user, Post $post): bool
    {
        if ($user->role === 'editor') {
            return true;
        }

        return $post->user_id === $user->id;
    }

    /**
     * Aturan hapus post sama seperti aturan edit.
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}
