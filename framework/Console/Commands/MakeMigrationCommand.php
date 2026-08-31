<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Support\Str;

class MakeMigrationCommand extends Command
{
    protected string $signature = 'make:migration';
    protected string $description = 'Create a new migration file';

    #[Override]
    public function handle(): int
    {
        $name = $this->argument(0) ?? '';
        if ($name === '') {
            $this->error('Migration name is required.');
            return 1;
        }
        $snake = Str::snake($name);
        $table = preg_replace('/^(create|add|drop|alter)_/', '', $snake);
        $action = preg_match('/^create_/', $snake) ? 'create' : 'modify';
        $timestamp = date('Y_m_d_His');
        $file = "{$timestamp}_{$snake}.php";
        $path = database_path(fixPathSeparator("migrations/{$file}"));
        if (is_file($path)) {
            $this->error("Migration already exists: {$file}");
            return 1;
        }
        $content = "<?php\n\nuse Trash\\Database\\Blueprint;\nuse Trash\\Database\\Schema;\n\nreturn function (Schema \$schema) {\n    \$schema->{$action}('{$table}', function (Blueprint \$table) {\n        \$table->increments('id');\n        \$table->timestamps();\n    });\n};\n";
        file_put_contents($path, $content);
        $this->success("Migration [{$file}] created.");
        return 0;
    }
}
