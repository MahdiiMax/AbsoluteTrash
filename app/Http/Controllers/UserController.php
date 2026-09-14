<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Trash\View\View;

class UserController
{
    public function index(): View
    {
        return view('users.index', ['users' => User::all()]);
    }

    public function show(int $id): View
    {
        return view('users.show', ['user' => User::findOrFail($id)]);
    }
}
