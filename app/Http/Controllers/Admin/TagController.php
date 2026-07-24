<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Tag::class);

        $tags = Tag::withCount('posts')->latest()->get();

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tag::class);

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        Tag::create($request->only('name'));

        return redirect()->route('admin.tags.index')->with('success', 'Tag berhasil dibuat.');
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $tag->update($request->only('name'));

        return redirect()->route('admin.tags.index')->with('success', 'Tag berhasil diperbarui.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Tag berhasil dihapus.');
    }
}
