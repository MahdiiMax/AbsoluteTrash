<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Tests\TestCase;
use Trash\Support\Dotenv;

class DotenvTest extends TestCase
{
    public function test_load_parses_simple_and_quoted_values(): void
    {
        $dir = sys_get_temp_dir() . '/absolute_trash_dotenv_' . uniqid();
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/.env', "# comment\nFOO_TEST_BAR=hello\nFOO_TEST_QUOTED=\"world\"\n");
        (new Dotenv())->load($dir);
        $this->assertSame('hello', getenv('FOO_TEST_BAR'));
        $this->assertSame('world', getenv('FOO_TEST_QUOTED'));
        putenv('FOO_TEST_BAR');
        putenv('FOO_TEST_QUOTED');
        @rmdir($dir);
    }

    public function test_load_ignores_missing_file(): void
    {
        (new Dotenv())->load(sys_get_temp_dir() . '/does_not_exist_' . uniqid());
        $this->assertTrue(true);
    }
}