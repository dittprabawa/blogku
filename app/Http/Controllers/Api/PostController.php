<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Daftar semua post (dengan pagination).
     */
    public function index(): JsonResponse
    {
        $posts = Post::with(['user', 'category', 'tags'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => PostResource::collection($posts->items()),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Simpan post baru.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $post = Post::create($validated);

        if (!empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        $post->load(['user', 'category', 'tags']);

        return response()->json([
            'message' => 'Post berhasil dibuat.',
            'data' => new PostResource($post),
        ], 201);
    }

    /**
     * Detail satu post.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load(['user', 'category', 'tags']);

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Update post.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();

        $post->update($validated);
        $post->tags()->sync($validated['tags'] ?? []);
        $post->load(['user', 'category', 'tags']);

        return response()->json([
            'message' => 'Post berhasil diperbarui.',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Hapus post.
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post berhasil dihapus.',
        ]);
    }
}