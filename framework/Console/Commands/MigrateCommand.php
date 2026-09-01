<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Database\Migrator;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate';
    protected string $description = 'Run database migrations';

    #[Override]
    public function handle(): int
    {
        app(Migrator::class)->migrate();
        $this->success('Migrated successfully.');
        return 0;
    }
}
