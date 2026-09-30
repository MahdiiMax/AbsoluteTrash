<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_register_creates_user_and_redirects(): void
    {
        $response = $this->postForm('/register', [
            'name'                  => 'Bob',
            'email'                 => 'bob@example.com',
            'password'              => 'password1',
            'password_confirmation' => 'password1',
        ]);
        $this->assertRedirect($response, '/users');
        $this->assertSame(2, User::all()->count());
    }

    public function test_register_with_duplicate_email_redirects_back(): void
    {
        $response = $this->postForm('/register', [
            'name'                  => 'Clone',
            'email'                 => 'alice@example.com',
            'password'              => 'password1',
            'password_confirmation' => 'password1',
        ]);
        $this->assertRedirect($response, '/');
        $this->assertSame(1, User::all()->count());
    }

    public function test_login_success_returns_greeting(): void
    {
        $response = $this->postForm('/login', [
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ]);
        $this->assertOk($response);
        $this->assertSee($response, 'Logged in as Alice');
    }

    public function test_login_failure_redirects_back(): void
    {
        $response = $this->postForm('/login', [
            'email'    => 'alice@example.com',
            'password' => 'wrong',
        ]);
        $this->assertRedirect($response, '/');
    }

    public function test_logout_clears_session(): void
    {
        $this->postForm('/login', ['email' => 'alice@example.com', 'password' => 'secret']);
        $loggedIn = $this->sessionCookies();
        $response = $this->get('/logout', $loggedIn);
        $this->assertOk($response);
        $this->assertSee($response, 'Logged out');
        $afterLogout = $this->sessionCookies();
        $this->assertRedirect($this->get('/dashboard', $afterLogout), '/login');
    }
}