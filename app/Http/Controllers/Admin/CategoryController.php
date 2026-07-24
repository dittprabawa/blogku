<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::withCount('posts')->latest()->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        Category::create($request->only('name'));

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $category->update($request->only('name'));

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->posts()->exists()) {
            $count = $category->posts()->count();
            return redirect()->route('admin.categories.index')
                ->with('error', "Kategori tidak dapat dihapus karena masih memiliki {$count} post yang terhubung.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
