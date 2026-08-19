<?php

declare(strict_types=1);

namespace App\Models;

use Trash\Database\Model;

class Post extends Model
{
    protected static array $fillable = ['user_id', 'title', 'body', 'published', 'created_at', 'updated_at'];
}