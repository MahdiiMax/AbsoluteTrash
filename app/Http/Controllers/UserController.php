<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

class UserController
{
    public function index(): array
    {
        return User::all()->toArray();
    }

    public function show(int $id): array
    {
        return User::findOrFail($id)->toArray();
    }
}
