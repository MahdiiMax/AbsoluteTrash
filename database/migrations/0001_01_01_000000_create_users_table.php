<?php

use Trash\Database\Blueprint;
use Trash\Database\Connection;
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

    $conn = app(Connection::class);
    $now = date('Y-m-d H:i:s');
    $conn->insert('users', ['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'secret', 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);
    $conn->insert('users', ['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'secret', 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);
};