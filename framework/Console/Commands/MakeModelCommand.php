<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Support\Str;

class MakeModelCommand extends Command
{
    protected string $signature = 'make:model';
    protected string $description = 'Create a new model class';

    #[Override]
    public function handle(): int
    {
        $name = Str::studly($this->argument(0) ?? '');
        if ($name === '') {
            $this->error('Model name is required.');
            return 1;
        }
        $path = app_path(fixPathSeparator("Models/{$name}.php"));
        if (is_file($path)) {
            $this->error("Model already exists: {$name}");
            return 1;
        }
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Models;\n\nuse Trash\\Database\\Model;\n\nclass {$name} extends Model\n{\n    protected static array \$fillable = [];\n}\n";
        $this->ensureDir(dirname($path));
        file_put_contents($path, $content);
        $this->success("Model [{$name}] created.");
        return 0;
    }
}
