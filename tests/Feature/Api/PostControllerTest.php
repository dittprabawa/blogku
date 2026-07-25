<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('endpoint post butuh autentikasi', function () {
    $this->getJson('/api/posts')->assertUnauthorized();
});

test('user terautentikasi bisa melihat daftar post lewat api', function () {
    $user = User::factory()->author()->create();
    Post::factory()->for($user)->count(3)->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/posts')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user bisa membuat post baru lewat api dengan gambar yang otomatis di-resize', function () {
    Storage::fake('public');

    $user = User::factory()->author()->create();
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('foto.jpg', 3000, 3000);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/posts', [
        'title' => 'Post dari API',
        'body' => 'Isi konten post.',
        'category_id' => $category->id,
        'status' => 'draft',
        'featured_image' => $image,
    ]);

    $response->assertCreated();

    $post = Post::first();
    expect($post->featured_image)->not->toBeNull();
    Storage::disk('public')->assertExists($post->featured_image);
});

test('user bisa melihat detail post lewat api', function () {
    $user = User::factory()->author()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/posts/{$post->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $post->id);
});

test('author bisa update post miliknya sendiri', function () {
    $author = User::factory()->author()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->for($author)->create();

    $this->actingAs($author, 'sanctum')->putJson("/api/posts/{$post->id}", [
        'title' => 'Judul diperbarui',
        'body' => $post->body,
        'category_id' => $category->id,
        'status' => 'draft',
    ])->assertOk();

    expect($post->fresh()->title)->toBe('Judul diperbarui');
});

test('author tidak bisa update post milik author lain', function () {
    $author = User::factory()->author()->create();
    $otherAuthor = User::factory()->author()->create();
    $category = Category::factory()->create();
    $foreignPost = Post::factory()->for($otherAuthor)->create();

    $this->actingAs($author, 'sanctum')->putJson("/api/posts/{$foreignPost->id}", [
        'title' => 'Coba dicuri',
        'body' => $foreignPost->body,
        'category_id' => $category->id,
        'status' => 'draft',
    ])->assertForbidden();
});

test('editor bisa update post siapa saja', function () {
    $editor = User::factory()->editor()->create();
    $author = User::factory()->author()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->for($author)->create();

    $this->actingAs($editor, 'sanctum')->putJson("/api/posts/{$post->id}", [
        'title' => 'Diedit editor',
        'body' => $post->body,
        'category_id' => $category->id,
        'status' => 'draft',
    ])->assertOk();
});

test('user bisa mengganti featured image lewat api dan gambar lama terhapus', function () {
    Storage::fake('public');
    Storage::disk('public')->put('posts/lama.jpg', 'isi lama');

    $user = User::factory()->author()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->for($user)->create(['featured_image' => 'posts/lama.jpg']);

    $newImage = UploadedFile::fake()->image('baru.jpg', 800, 800);

    $this->actingAs($user, 'sanctum')->putJson("/api/posts/{$post->id}", [
        'title' => $post->title,
        'body' => $post->body,
        'category_id' => $category->id,
        'status' => 'draft',
        'featured_image' => $newImage,
    ])->assertOk();

    Storage::disk('public')->assertMissing('posts/lama.jpg');
    Storage::disk('public')->assertExists($post->fresh()->featured_image);
});

test('author tidak bisa menghapus post milik author lain', function () {
    $author = User::factory()->author()->create();
    $otherAuthor = User::factory()->author()->create();
    $foreignPost = Post::factory()->for($otherAuthor)->create();

    $this->actingAs($author, 'sanctum')
        ->deleteJson("/api/posts/{$foreignPost->id}")
        ->assertForbidden();
});

test('user bisa menghapus post lewat api dan gambar ikut terhapus dari disk', function () {
    Storage::fake('public');
    Storage::disk('public')->put('posts/hapus.jpg', 'isi');

    $user = User::factory()->author()->create();
    $post = Post::factory()->for($user)->create(['featured_image' => 'posts/hapus.jpg']);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/posts/{$post->id}")
        ->assertOk();

    Storage::disk('public')->assertMissing('posts/hapus.jpg');
    expect(Post::find($post->id))->toBeNull();
});

test('validasi api menolak field wajib yang kosong', function () {
    $user = User::factory()->author()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/posts', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'body', 'category_id', 'status']);
});
