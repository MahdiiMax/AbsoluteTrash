<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Tests\TestCase;
use Trash\Support\Hash;

class HashTest extends TestCase
{
    public function test_make_and_check_round_trip(): void
    {
        $hash = Hash::make('secret123');
        $this->assertNotSame('secret123', $hash);
        $this->assertTrue(Hash::check('secret123', $hash));
        $this->assertFalse(Hash::check('wrong', $hash));
    }
}