<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;

class DbSeedCommand extends Command
{
    protected string $signature = 'db:seed';
    protected string $description = 'Seed the database';

    #[Override]
    public function handle(): int
    {
        $seeder = database_path('seeders/DatabaseSeeder.php');
        if (!is_file($seeder)) {
            $this->comment('No seeder found at database/seeders/DatabaseSeeder.php');
            return 0;
        }
        require $seeder;
        $class = 'Database\DatabaseSeeder';
        if (class_exists($class)) {
            (new $class())->run(app(\Trash\Database\Connection::class));
            $this->success('Database seeded.');
        } else {
            $this->error("Seeder class [{$class}] not found.");
            return 1;
        }
        return 0;
    }
}