<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Tampilkan daftar semua post.
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->published()
            ->with(['user', 'category', 'tags'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($searchQuery) use ($request) {
                    $searchQuery->where('title', 'like', '%' . $request->q . '%')
                        ->orWhere('excerpt', 'like', '%' . $request->q . '%')
                        ->orWhere('body', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('slug', $request->category);
                });
            })
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', function ($tagQuery) use ($request) {
                    $tagQuery->where('slug', $request->tag);
                });
            })
            ->latest('published_at')
            ->paginate(10);

        $categories = Category::query()
            ->withCount(['posts' => fn ($query) => $query->where('status', 'published')])
            ->get();

        $tags = Tag::query()
            ->withCount(['posts' => fn ($query) => $query->where('status', 'published')])
            ->get();

        return view('posts.index', compact('posts', 'categories', 'tags'));
    }

    /**
     * Form untuk membuat post baru.
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('posts.create', compact('categories', 'tags'));
    }

    /**
     * Simpan post baru ke database.
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $post = Post::create($validated);

        if (!empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post berhasil dibuat.');
    }

    /**
     * Tampilkan detail satu post.
     */
    public function show(Post $post)
    {
        abort_unless($post->status === 'published', 404);

        $post->load(['user', 'category', 'tags']);

        $related = Post::query()
            ->published()
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->getKey())
            ->with(['user', 'category', 'tags'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('posts.show', compact('post', 'related'));
    }

    /**
     * Form untuk edit post.
     */
    public function edit(Post $post)
    {
        $categories = Category::all();
        $tags = Tag::all();
        $post->load('tags');

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update post yang sudah ada.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();

        $post->update($validated);

        $post->tags()->sync($validated['tags'] ?? []);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post berhasil diperbarui.');
    }

    /**
     * Hapus post.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post berhasil dihapus.');
    }
}