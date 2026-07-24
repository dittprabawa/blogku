@extends('layouts.admin')

@section('title', 'Post baru')

@section('content')
<div class="card">
    <h1>Post baru</h1>
    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.posts._form', ['post' => null])
        <button type="submit" class="btn">Simpan</button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
