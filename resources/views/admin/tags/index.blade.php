@extends('layouts.admin')

@section('title', 'Tags')

@section('content')
@can('create', App\Models\Tag::class)
<div class="card" style="margin-bottom:20px">
    <h1>Tag baru</h1>
    <form action="{{ route('admin.tags.store') }}" method="POST" style="display:flex; gap:10px; align-items:flex-start">
        @csrf
        <div style="flex:1">
            <input type="text" name="name" placeholder="Nama tag" value="{{ old('name') }}">
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn">Tambah</button>
    </form>
</div>
@endcan

<div class="card">
    <h1>Daftar tag</h1>
    <table>
        <thead>
            <tr><th>Nama</th><th>Jumlah post</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($tags as $tag)
                <tr>
                    <td>
                        @can('update', $tag)
                            <form action="{{ route('admin.tags.update', $tag) }}" method="POST" style="display:flex; gap:8px">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $tag->name }}" style="max-width:220px">
                                <button type="submit" class="btn btn-sm btn-secondary">Simpan</button>
                            </form>
                        @else
                            {{ $tag->name }}
                        @endcan
                    </td>
                    <td>{{ $tag->posts_count }}</td>
                    <td>
                        @can('delete', $tag)
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" onsubmit="return confirm('Yakin hapus tag ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada tag.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
