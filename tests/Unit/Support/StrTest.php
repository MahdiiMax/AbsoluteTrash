<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Tests\TestCase;
use Trash\Support\Str;

class StrTest extends TestCase
{
    public function test_upper_and_lower(): void
    {
        $this->assertSame('HELLO', Str::upper('hello'));
        $this->assertSame('hello', Str::lower('HeLLo'));
    }

    public function test_title(): void
    {
        $this->assertSame('Hello World', Str::title('hello world'));
    }

    public function test_studly(): void
    {
        $this->assertSame('HelloWorld', Str::studly('hello_world'));
        $this->assertSame('HelloWorld', Str::studly('hello_ world'));
    }

    public function test_camel(): void
    {
        $this->assertSame('helloWorld', Str::camel('hello_world'));
    }

    public function test_snake(): void
    {
        $this->assertSame('hello_world', Str::snake('HelloWorld'));
        $this->assertSame('hello-world', Str::snake('HelloWorld', '-'));
    }

    public function test_kebab(): void
    {
        $this->assertSame('hello-world', Str::kebab('HelloWorld'));
    }

    public function test_plural_and_singular(): void
    {
        $this->assertSame('users', Str::plural('user'));
        $this->assertSame('posts', Str::plural('post'));
        $this->assertSame('user', Str::singular('users'));
    }

    public function test_limit(): void
    {
        $this->assertSame('Hello', Str::limit('Hello', 5));
        $this->assertSame('Hello...', Str::limit('Hello World', 5));
    }

    public function test_contains_starts_ends(): void
    {
        $this->assertTrue(Str::contains('hello world', 'world'));
        $this->assertFalse(Str::contains('hello world', 'xyz'));
        $this->assertFalse(Str::contains('hello', ''));
        $this->assertTrue(Str::startsWith('hello', 'he'));
        $this->assertTrue(Str::endsWith('hello', 'lo'));
    }

    public function test_replace_before_after(): void
    {
        $this->assertSame('Hi World', Str::replace('Hello', 'Hi', 'Hello World'));
        $this->assertSame('Hello', Str::before('Hello World', ' '));
        $this->assertSame('World', Str::after('Hello World', ' '));
    }

    public function test_uuid_format(): void
    {
        $uuid = Str::uuid();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );
    }

    public function test_random_is_hex_of_requested_length(): void
    {
        $this->assertSame(16, strlen(Str::random(16)));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', Str::random(16));
    }
}