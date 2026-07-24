<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

test('admin bisa melihat semua post termasuk draft milik orang lain', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->author()->create();
    $category = Category::factory()->create();

    $draftByOther = Post::factory()->for($author)->for($category)->draft()->create();
    $publishedByOther = Post::factory()->for($author)->for($category)->published()->create();

    $response = $this->actingAs($admin)->get(route('admin.posts.index'));

    $response->assertOk()
        ->assertSee($draftByOther->title)
        ->assertSee($publishedByOther->title);
});

test('editor bisa melihat semua post termasuk draft milik orang lain', function () {
    $editor = User::factory()->editor()->create();
    $author = User::factory()->author()->create();
    $category = Category::factory()->create();

    $draftByOther = Post::factory()->for($author)->for($category)->draft()->create();

    $response = $this->actingAs($editor)->get(route('admin.posts.index'));

    $response->assertOk()->assertSee($draftByOther->title);
});

test('author hanya bisa melihat post miliknya sendiri di dashboard', function () {
    $author = User::factory()->author()->create();
    $otherAuthor = User::factory()->author()->create();
    $category = Category::factory()->create();

    $ownDraft = Post::factory()->for($author)->for($category)->draft()->create(['title' => 'Draft Saya Sendiri']);
    $otherDraft = Post::factory()->for($otherAuthor)->for($category)->draft()->create(['title' => 'Draft Orang Lain']);
    $otherPublished = Post::factory()->for($otherAuthor)->for($category)->published()->create(['title' => 'Published Orang Lain']);

    $response = $this->actingAs($author)->get(route('admin.posts.index'));

    $response->assertOk()
        ->assertSee('Draft Saya Sendiri')
        ->assertDontSee('Draft Orang Lain')
        ->assertDontSee('Published Orang Lain');
});