<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('user bukan admin tidak bisa mengakses halaman tag', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get(route('admin.tags.index'))->assertForbidden();
});

test('guest diarahkan ke login saat mengakses halaman tag', function () {
    $this->get(route('admin.tags.index'))->assertRedirect(route('login'));
});

test('admin bisa melihat daftar tag beserta jumlah post', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $post = Post::factory()->for($admin)->create();
    $post->tags()->attach($tag);

    $response = $this->actingAs($admin)->get(route('admin.tags.index'));

    $response->assertOk()->assertSee('Laravel');
});

test('admin bisa membuat tag baru', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.tags.store'), [
        'name' => 'PHP',
    ]);

    $response->assertRedirect(route('admin.tags.index'));
    $this->assertDatabaseHas('tags', [
        'name' => 'PHP',
        'slug' => 'php',
    ]);
});

test('nama tag wajib diisi', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.tags.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseCount('tags', 0);
});

test('admin bisa memperbarui nama tag', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create(['name' => 'Lama']);

    $response = $this->actingAs($admin)->put(route('admin.tags.update', $tag), [
        'name' => 'Baru',
    ]);

    $response->assertRedirect(route('admin.tags.index'));
    expect($tag->refresh()->name)->toBe('Baru');
});

test('admin bisa menghapus tag', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.tags.destroy', $tag));

    $response->assertRedirect(route('admin.tags.index'));
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});
