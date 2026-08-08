<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|max:2000',
        ]);

        $post->comments()->create([
            'user_id' => auth()->id(),
            'name' => auth()->check() ? auth()->user()->name : $validated['name'],
            'email' => auth()->check() ? auth()->user()->email : $validated['email'],
            'comment' => $validated['comment'],
            'is_approved' => true,
        ]);

        return back()->with('success_comment', 'Terima kasih! Komentar Anda telah berhasil dipublikasikan.');
    }
}
