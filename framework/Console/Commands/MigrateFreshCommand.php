<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Database\Migrator;

class MigrateFreshCommand extends Command
{
    protected string $signature = 'migrate:fresh';
    protected string $description = 'Drop all tables and re-run migrations';

    #[Override]
    public function handle(): int
    {
        if (!$this->confirm('This will drop all tables. Continue?', false)) {
            $this->comment('Cancelled.');
            return 0;
        }
        app(Migrator::class)->fresh();
        $this->success('Database refreshed successfully.');
        return 0;
    }
}
