<?php

use Trash\Database\Blueprint;
use Trash\Database\Schema;

return function (Schema $schema) {
    $schema->create('users', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
};
