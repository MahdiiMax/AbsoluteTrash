<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;

class CacheClearCommand extends Command
{
    protected string $signature = 'cache:clear';
    protected string $description = 'Clear the compiled view cache';

    #[Override]
    public function handle(): int
    {
        $dir = storage_path(fixPathSeparator('framework/views'));
        $count = 0;
        foreach (glob($dir . '/*.php') as $file) {
            unlink($file);
            $count++;
        }
        $this->success("View cache cleared ({$count} files).");
        return 0;
    }
}
