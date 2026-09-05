<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Trash\Auth\Facades\Auth;
use Trash\Http\RedirectResponse;
use Trash\Support\Hash;
use Trash\View\View;

class RegisterController
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        Auth::login($user);
        return redirect()->route('users.index');
    }
}
