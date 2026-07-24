@extends('layouts.admin')

@section('title', 'Edit post')

@section('content')
<div class="card">
    <h1>Edit post</h1>
    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.posts._form', ['post' => $post])
        <button type="submit" class="btn">Simpan perubahan</button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
