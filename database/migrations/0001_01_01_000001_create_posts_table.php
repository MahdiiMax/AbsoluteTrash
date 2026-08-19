<?php

use Trash\Database\Blueprint;
use Trash\Database\Connection;
use Trash\Database\Schema;

return function (Schema $schema) {
    $schema->create('posts', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id');
        $table->string('title');
        $table->text('body');
        $table->boolean('published')->default(false);
        $table->timestamps();
    });

    $conn = app(Connection::class);
    $now = date('Y-m-d H:i:s');
    $conn->insert('posts', ['user_id' => 1, 'title' => 'Hello World', 'body' => 'First post.', 'published' => 1, 'created_at' => $now, 'updated_at' => $now]);
    $conn->insert('posts', ['user_id' => 1, 'title' => 'Second Post', 'body' => 'Another one.', 'published' => 0, 'created_at' => $now, 'updated_at' => $now]);
    $conn->insert('posts', ['user_id' => 2, 'title' => 'Third Post', 'body' => 'Final post.', 'published' => 1, 'created_at' => $now, 'updated_at' => $now]);
};
