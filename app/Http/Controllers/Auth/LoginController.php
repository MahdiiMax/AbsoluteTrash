<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Trash\Auth\Facades\Auth;
use Trash\Support\Hash;
use Trash\View\View;

class LoginController
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): string|View
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return view('auth.login', ['errors' => 'Invalid credentials.']);
        }
        Auth::login($user);
        return 'Logged in as ' . $user->name;
    }

    public function logout(): string
    {
        Auth::logout();
        return 'Logged out';
    }
}
