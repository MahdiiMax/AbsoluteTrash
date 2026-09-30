<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;

class PostsTest extends TestCase
{
    public function test_index_lists_seeded_post(): void
    {
        $response = $this->get('/posts', $this->loginAs());
        $this->assertOk($response);
        $this->assertSee($response, 'First Post');
    }

    public function test_create_page_renders(): void
    {
        $this->assertOk($this->get('/posts/create', $this->loginAs()));
    }

    public function test_store_creates_post(): void
    {
        $response = $this->postForm('/posts', [
            'title' => 'Second Post',
            'body'  => 'More content.',
        ], $this->loginAs());
        $this->assertRedirect($response, '/posts');
        $this->assertSame(2, Post::all()->count());
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postForm('/posts', ['title' => '', 'body' => ''], $this->loginAs());
        $this->assertRedirect($response, '/');
        $this->assertSame(1, Post::all()->count());
    }

    public function test_show_displays_post(): void
    {
        $response = $this->get('/posts/1', $this->loginAs());
        $this->assertOk($response);
        $this->assertSee($response, 'Hello world.');
    }

    public function test_show_missing_post_is_404(): void
    {
        $this->assertStatus($this->get('/posts/999', $this->loginAs()), 404);
    }

    public function test_owner_can_delete_post(): void
    {
        $response = $this->postForm('/posts/1', ['_method' => 'DELETE'], $this->loginAs());
        $this->assertRedirect($response, '/posts');
        $this->assertSame(0, Post::all()->count());
    }

    public function test_non_owner_cannot_delete_post(): void
    {
        $this->postForm('/register', [
            'name'                  => 'Bob',
            'email'                 => 'bob@example.com',
            'password'              => 'password1',
            'password_confirmation' => 'password1',
        ]);
        $response = $this->postForm('/posts/1', ['_method' => 'DELETE'], $this->sessionCookies());
        $this->assertStatus($response, 403);
        $this->assertSame(1, Post::all()->count());
    }

    private function loginAs(): array
    {
        $this->postForm('/login', ['email' => 'alice@example.com', 'password' => 'secret']);
        return $this->sessionCookies();
    }
}