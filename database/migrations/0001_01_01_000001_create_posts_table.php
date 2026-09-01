<?php

use Trash\Database\Blueprint;
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
};
