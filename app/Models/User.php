<?php

declare(strict_types=1);

namespace App\Models;

use Trash\Database\Model;

class User extends Model
{
    protected static array $fillable = ['name', 'email', 'password', 'active', 'created_at', 'updated_at'];
}
