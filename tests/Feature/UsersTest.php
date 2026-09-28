<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class UsersTest extends TestCase
{
    public function test_index_lists_users(): void
    {
        $response = $this->get('/users', $this->loginAs());
        $this->assertOk($response);
        $this->assertSee($response, 'Alice');
        $this->assertSee($response, 'alice@example.com');
    }

    public function test_show_displays_user(): void
    {
        $response = $this->get('/users/1', $this->loginAs());
        $this->assertOk($response);
        $this->assertSee($response, 'alice@example.com');
    }

    public function test_show_missing_user_is_404(): void
    {
        $this->assertStatus($this->get('/users/999', $this->loginAs()), 404);
    }

    private function loginAs(): array
    {
        $this->postForm('/login', ['email' => 'alice@example.com', 'password' => 'secret']);
        return $this->sessionCookies();
    }
}
