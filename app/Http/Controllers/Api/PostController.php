<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected ImageUploadService $imageUploadService)
    {
    }

    /**
     * Daftar post, bisa dicari & difilter.
     *
     * Query string yang didukung:
     * - q          : cari di title, excerpt, body
     * - category_id: filter berdasarkan ID kategori
     * - tag_id     : filter berdasarkan ID tag
     * - status     : filter berdasarkan status (draft/published)
     * - per_page   : jumlah item per halaman (default 10, maksimal 50)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 10), 50) ?: 10;

        $posts = Post::query()
            ->with(['user', 'category', 'tags'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($searchQuery) use ($request) {
                    $searchQuery->where('title', 'like', '%'.$request->input('q').'%')
                        ->orWhere('excerpt', 'like', '%'.$request->input('q').'%')
                        ->orWhere('body', 'like', '%'.$request->input('q').'%');
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->filled('tag_id'), function ($query) use ($request) {
                $query->whereHas('tags', function ($tagQuery) use ($request) {
                    $tagQuery->where('tags.id', $request->input('tag_id'));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => PostResource::collection($posts->items()),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
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
