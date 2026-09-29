<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class NotFoundTest extends TestCase
{
    public function test_unknown_route_returns_404(): void
    {
        $response = $this->get('/this-route-does-not-exist');
        $this->assertStatus($response, 404);
        $this->assertSee($response, 'Not Found');
    }
}