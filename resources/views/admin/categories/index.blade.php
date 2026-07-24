@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
@can('create', App\Models\Category::class)
<div class="card" style="margin-bottom:20px">
    <h1>Kategori baru</h1>
    <form action="{{ route('admin.categories.store') }}" method="POST" style="display:flex; gap:10px; align-items:flex-start">
        @csrf
        <div style="flex:1">
            <input type="text" name="name" placeholder="Nama kategori" value="{{ old('name') }}">
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn">Tambah</button>
    </form>
</div>
@endcan

<div class="card">
    <h1>Daftar kategori</h1>
    <table>
        <thead>
            <tr><th>Nama</th><th>Jumlah post</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>
                        @can('update', $category)
                            <form action="{{ route('admin.categories.update', $category) }}" method="POST" style="display:flex; gap:8px">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $category->name }}" style="max-width:220px">
                                <button type="submit" class="btn btn-sm btn-secondary">Simpan</button>
                            </form>
                        @else
                            {{ $category->name }}
                        @endcan
                    </td>
                    <td>{{ $category->posts_count }}</td>
                    <td>
                        @can('delete', $category)
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ $category->posts_count > 0 ? 'PERINGATAN: Kategori ini memiliki ' . $category->posts_count . ' post yang terhubung. Kategori tidak dapat dihapus selama masih memiliki post.' : 'Yakin hapus kategori ini?' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada kategori.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
