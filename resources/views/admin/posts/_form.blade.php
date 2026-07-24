@php
    $selectedTags = old('tags', $post?->tags->pluck('id')->toArray() ?? []);
@endphp

<div class="field">
    <label>Judul</label>
    <input type="text" name="title" value="{{ old('title', $post?->title) }}">
    @error('title') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Ringkasan (excerpt)</label>
    <input type="text" name="excerpt" value="{{ old('excerpt', $post?->excerpt) }}">
    @error('excerpt') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Isi konten <span style="font-weight:400; color:#78716c;">(mendukung Markdown &mdash; ```kode``` untuk blok kode, `kode` untuk inline)</span></label>
    <textarea name="body">{{ old('body', $post?->body) }}</textarea>
    @error('body') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Kategori</label>
    <select name="category_id">
        <option value="">-- pilih kategori --</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Status</label>
    <select name="status">
        <option value="draft" @selected(old('status', $post?->status) === 'draft')>Draft</option>
        <option value="published" @selected(old('status', $post?->status) === 'published')>Published</option>
    </select>
    @error('status') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Gambar utama</label>
    @if ($post?->featured_image)
        <div style="margin-bottom:8px">
            <img src="{{ $post->featured_image_url }}" style="max-width:200px; border-radius:6px">
        </div>
    @endif
    <input type="file" name="featured_image" accept="image/*">
    @error('featured_image') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Meta title (SEO)</label>
    <input type="text" name="meta_title" value="{{ old('meta_title', $post?->meta_title) }}">
    @error('meta_title') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Meta description (SEO)</label>
    <input type="text" name="meta_description" value="{{ old('meta_description', $post?->meta_description) }}">
    @error('meta_description') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label>Tags</label>
    @foreach ($tags as $tag)
        <label style="display:inline-block; margin-right:12px; font-weight:400;">
            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags))>
            {{ $tag->name }}
        </label>
    @endforeach
    @error('tags') <div class="error">{{ $message }}</div> @enderror
</div>
