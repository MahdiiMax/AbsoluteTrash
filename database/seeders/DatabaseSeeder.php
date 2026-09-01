<?php

declare(strict_types=1);

namespace Database;

use Trash\Database\Connection;
use Trash\Support\Hash;

class DatabaseSeeder
{
    public function run(Connection $connection): void
    {
        $now = date('Y-m-d H:i:s');
        $userIds = [];
        foreach (['Alice', 'Bob'] as $name) {
            $userId = $connection->insert('users', [
                'name'     => $name,
                'email'    => strtolower($name) . '+' . substr(md5((string) mt_rand()), 0, 8) . '@example.com',
                'password' => Hash::make('secret'),
                'active'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $userIds[] = $userId;
        }
        $titles = ['Hello World', 'Second Post', 'Third Post'];
        foreach ($titles as $i => $title) {
            $connection->insert('posts', [
                'user_id'    => $userIds[$i % 2],
                'title'      => $title,
                'body'       => "Post #{$i}.",
                'published'  => $i % 2 === 0 ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
