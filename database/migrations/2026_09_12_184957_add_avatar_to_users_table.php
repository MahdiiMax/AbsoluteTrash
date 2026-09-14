<?php

use Trash\Database\Schema;

return function (Schema $schema) {
    $schema->statement(
        config('database.default') === 'sqlite'
            ? 'ALTER TABLE users ADD COLUMN avatar TEXT NULL'
            : 'ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL'
    );
};
