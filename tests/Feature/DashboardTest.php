<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_index_shows_user(): void
    {
        $response = $this->get('/dashboard', $this->loginAs());
        $this->assertOk($response);
        $this->assertSee($response, 'Alice');
    }

    public function test_avatar_without_file_redirects_back(): void
    {
        $response = $this->postForm('/dashboard/avatar', [], $this->loginAs());
        $this->assertRedirect($response, '/');
    }

    private function loginAs(): array
    {
        $this->postForm('/login', ['email' => 'alice@example.com', 'password' => 'secret']);
        return $this->sessionCookies();
    }
}