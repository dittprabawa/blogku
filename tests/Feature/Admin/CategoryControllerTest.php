<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

test('user bukan admin tidak bisa mengakses halaman kategori', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get(route('admin.categories.index'))->assertForbidden();
});

test('guest diarahkan ke login saat mengakses halaman kategori', function () {
    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
});

test('admin bisa melihat daftar kategori beserta jumlah post', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Teknologi']);
    Post::factory()->for($admin)->for($category)->count(2)->create();

    $response = $this->actingAs($admin)->get(route('admin.categories.index'));

    $response->assertOk()->assertSee('Teknologi');
});

test('admin bisa membuat kategori baru', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => 'Kesehatan',
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Kesehatan',
        'slug' => 'kesehatan',
    ]);
});

test('nama kategori wajib diisi', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseCount('categories', 0);
});

test('admin bisa memperbarui nama kategori', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Lama']);

    $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
        'name' => 'Baru',
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    expect($category->refresh()->name)->toBe('Baru');
});

test('admin bisa menghapus kategori tanpa post', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('admin tidak bisa menghapus kategori yang masih memiliki post', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    Post::factory()->for($admin)->for($category)->create();

    $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
