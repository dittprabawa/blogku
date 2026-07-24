@extends('layouts.admin')

@section('title', 'Posts')

@section('content')
<div class="card">
    <div class="top-bar">
        <h1>Posts</h1>
        <a href="{{ route('admin.posts.create') }}" class="btn">+ Post baru</a>
    </div>
    <table>
        <thead>
            <tr>
                <th></th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Penulis</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($posts as $post)
                <tr>
                    <td>
                        @if ($post->featured_image)
                            <img src="{{ $post->featured_image_url }}" style="width:48px; height:48px; object-fit:cover; border-radius:4px">
                        @endif
                    </td>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->category->name }}</td>
                    <td>
                        <span class="badge {{ $post->status === 'published' ? 'badge-published' : 'badge-draft' }}">
                            {{ $post->status }}
                        </span>
                    </td>
                    <td>{{ $post->user->name }}</td>
                    <td>
                        @can('update', $post)
                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-secondary">Edit</a>
                        @endcan
                        @can('delete', $post)
                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus post ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada post.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $posts->links() }}</div>
</div>
@endsection
