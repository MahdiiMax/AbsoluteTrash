<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Support\Str;

class MakeControllerCommand extends Command
{
    protected string $signature = 'make:controller';
    protected string $description = 'Create a new controller class';

    #[Override]
    public function handle(): int
    {
        $name = Str::studly($this->argument(0) ?? '');
        if ($name === '') {
            $this->error('Controller name is required.');
            return 1;
        }
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }
        $path = app_path(fixPathSeparator("Http/Controllers/{$name}.php"));
        if (is_file($path)) {
            $this->error("Controller already exists: {$name}");
            return 1;
        }
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Http\\Controllers;\n\nclass {$name}\n{\n    public function index(): array\n    {\n        return [];\n    }\n}\n";
        $this->ensureDir(dirname($path));
        file_put_contents($path, $content);
        $this->success("Controller [{$name}] created.");
        return 0;
    }
}
