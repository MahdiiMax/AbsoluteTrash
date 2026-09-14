<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;

class StorageLinkCommand extends Command
{
    protected string $signature = 'storage:link';
    protected string $description = 'Create a symbolic link from public/storage to storage/app';

    #[Override]
    public function handle(): int
    {
        $link = public_path(fixPathSeparator('storage'));
        if (file_exists($link)) {
            $this->error("The [{$link}] link already exists.");
            return 1;
        }
        if (symlink(storage_path('app'), $link)) {
            $this->info('The [public/storage] link has been created.');
            return 0;
        }
        $this->error('Could not create the symlink. This may require elevated permissions.');
        return 1;
    }
}