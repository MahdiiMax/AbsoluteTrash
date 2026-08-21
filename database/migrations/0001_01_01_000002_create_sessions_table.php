<?php

declare(strict_types=1);

use Trash\Database\Blueprint;
use Trash\Database\Schema;

return function (Schema $schema) {
    $schema->create('sessions', function (Blueprint $table) {
        $table->string('id')->unique();
        $table->text('payload');
        $table->integer('last_activity');
        $table->integer('lifetime');
    });
};
