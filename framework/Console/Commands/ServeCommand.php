<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;

class ServeCommand extends Command
{
    protected string $signature = 'serve';
    protected string $description = 'Serve the application on the PHP development server';

    #[Override]
    public function handle(): int
    {
        $host = (string) $this->option('host', '127.0.0.1');
        $port = (string) $this->option('port', $this->defaultPort());
        $public = public_path();
        $index = $public . '/index.php';
        if (!is_file($index)) {
            $this->error('public/index.php not found.');
            return 1;
        }
        $this->info("Server running at http://{$host}:{$port}");
        passthru(PHP_BINARY . " -S {$host}:{$port} -t {$public} {$index}");
        return 0;
    }

    private function defaultPort(): string
    {
        $url = config('app.url', 'http://localhost');
        $port = (int) parse_url($url, PHP_URL_PORT);
        return $port > 0 ? (string) $port : '8000';
    }
}