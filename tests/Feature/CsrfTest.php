<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CsrfTest extends TestCase
{
    public function test_post_without_token_is_rejected(): void
    {
        $response = $this->post('/login', [
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ]);
        $this->assertStatus($response, 419);
    }

    public function test_get_does_not_need_token(): void
    {
        $this->assertOk($this->get('/login'));
    }

    public function test_post_with_token_succeeds(): void
    {
        $response = $this->postForm('/login', [
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ]);
        $this->assertOk($response);
    }
}