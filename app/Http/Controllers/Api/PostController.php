<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected ImageUploadService $imageUploadService)
    {
    }

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
        $this->authorize('create', Post::class);

        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $this->imageUploadService->store(
                $request->file('featured_image'),
                'posts'
            );
        }

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
     * Update post. Editor boleh edit post siapa saja,
     * author cuma boleh edit post miliknya sendiri.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $this->imageUploadService->replace(
                $post->featured_image,
                $request->file('featured_image'),
                'posts'
            );
        }

        $post->update($validated);
        $post->tags()->sync($validated['tags'] ?? []);
        $post->load(['user', 'category', 'tags']);

        return response()->json([
            'message' => 'Post berhasil diperbarui.',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Hapus post. Aturan sama seperti update.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $this->imageUploadService->delete($post->featured_image);

        $post->delete();

        return response()->json([
            'message' => 'Post berhasil dihapus.',
        ]);
    }
}
