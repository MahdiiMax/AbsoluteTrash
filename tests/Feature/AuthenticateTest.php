<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->assertRedirect($this->get('/dashboard'), '/login');
    }

    public function test_guest_is_redirected_from_posts(): void
    {
        $this->assertRedirect($this->get('/posts'), '/login');
    }

    public function test_guest_is_redirected_from_users(): void
    {
        $this->assertRedirect($this->get('/users'), '/login');
    }

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $this->postForm('/login', ['email' => 'alice@example.com', 'password' => 'secret']);
        $response = $this->get('/dashboard', $this->sessionCookies());
        $this->assertOk($response);
        $this->assertSee($response, 'Dashboard');
    }
}