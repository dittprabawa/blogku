<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('halaman blog hanya menampilkan post yang published', function () {
    $user = User::factory()->create();
    Post::factory()->for($user)->published()->create(['title' => 'Post Terbit']);
    Post::factory()->for($user)->draft()->create(['title' => 'Post Draft']);

    $response = $this->get(route('blog.index'));

    $response->assertOk()
        ->assertSee('Post Terbit')
        ->assertDontSee('Post Draft');
});

test('halaman blog bisa dicari berdasarkan judul', function () {
    $user = User::factory()->create();
    Post::factory()->for($user)->published()->create(['title' => 'Belajar Laravel']);
    Post::factory()->for($user)->published()->create(['title' => 'Belajar Vue']);

    $response = $this->get(route('blog.index', ['q' => 'Laravel']));

    $response->assertOk()
        ->assertSee('Belajar Laravel')
        ->assertDontSee('Belajar Vue');
});

test('halaman blog bisa difilter berdasarkan kategori', function () {
    $user = User::factory()->create();
    $categoryA = Category::factory()->create(['name' => 'Kategori A']);
    $categoryB = Category::factory()->create(['name' => 'Kategori B']);

    Post::factory()->for($user)->for($categoryA)->published()->create(['title' => 'Post Kategori A']);
    Post::factory()->for($user)->for($categoryB)->published()->create(['title' => 'Post Kategori B']);

    $response = $this->get(route('blog.index', ['category' => $categoryA->slug]));

    $response->assertOk()
        ->assertSee('Post Kategori A')
        ->assertDontSee('Post Kategori B');
});

test('halaman blog bisa difilter berdasarkan tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $postWithTag = Post::factory()->for($user)->published()->create(['title' => 'Post Bertag']);
    $postWithTag->tags()->attach($tag);

    Post::factory()->for($user)->published()->create(['title' => 'Post Tanpa Tag']);

    $response = $this->get(route('blog.index', ['tag' => $tag->slug]));

    $response->assertOk()
        ->assertSee('Post Bertag')
        ->assertDontSee('Post Tanpa Tag');
});

test('detail post published bisa diakses', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->published()->create(['title' => 'Judul Detail Post']);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()->assertSee('Judul Detail Post');
});

test('detail post draft mengembalikan 404 di halaman publik', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->draft()->create();

    $this->get(route('blog.show', $post))->assertNotFound();
});

test('detail post menampilkan artikel terkait dari kategori yang sama', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = Post::factory()->for($user)->for($category)->published()->create();
    $related = Post::factory()->for($user)->for($category)->published()->create(['title' => 'Artikel Terkait Unik']);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()->assertSee('Artikel Terkait Unik');
});
