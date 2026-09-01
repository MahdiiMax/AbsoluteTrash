<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Support\Str;

class MakeMiddlewareCommand extends Command
{
    protected string $signature = 'make:middleware';
    protected string $description = 'Create a new middleware class';

    #[Override]
    public function handle(): int
    {
        $name = Str::studly($this->argument(0) ?? '');
        if ($name === '') {
            $this->error('Middleware name is required.');
            return 1;
        }
        if (!str_ends_with($name, 'Middleware')) {
            $name .= 'Middleware';
        }
        $path = app_path(fixPathSeparator("Http/Middleware/{$name}.php"));
        if (is_file($path)) {
            $this->error("Middleware already exists: {$name}");
            return 1;
        }
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Http\\Middleware;\n\nuse Psr\\Http\\Message\\ServerRequestInterface;\nuse Psr\\Http\\Message\\ResponseInterface;\nuse Override;\nuse Psr\\Http\\Server\\MiddlewareInterface;\nuse Psr\\Http\\Server\\RequestHandlerInterface;\n\nclass {$name} implements MiddlewareInterface\n{\n    #[Override]\n    public function process(ServerRequestInterface \$request, RequestHandlerInterface \$handler): ResponseInterface\n    {\n        return \$handler->handle(\$request);\n    }\n}\n";
        $this->ensureDir(dirname($path));
        file_put_contents($path, $content);
        $this->success("Middleware [{$name}] created.");
        return 0;
    }
}
