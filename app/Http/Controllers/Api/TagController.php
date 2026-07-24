<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::withCount('posts')->get();

        return response()->json([
            'data' => TagResource::collection($tags),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Tag::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'message' => 'Tag berhasil dibuat.',
            'data' => new TagResource($tag),
        ], 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        $tag->loadCount('posts');

        return response()->json([
            'data' => new TagResource($tag),
        ]);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tag->update($validated);

        return response()->json([
            'message' => 'Tag berhasil diperbarui.',
            'data' => new TagResource($tag),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return response()->json([
            'message' => 'Tag berhasil dihapus.',
        ]);
    }
}
