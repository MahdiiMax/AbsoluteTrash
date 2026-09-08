<?php

declare(strict_types=1);

// use App\Http\Controllers\HomeController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Trash\Auth\Middleware\Authenticate;
use Trash\Routing\Facades\Route;

// Route::get('/users/{id}', [HomeController::class, 'show'])->name('users.show');
Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware(Authenticate::class);
Route::get('users/{id}', [UserController::class, 'show'])->name('users.show')->middleware(Authenticate::class);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.post');
Route::get('logout', [LoginController::class, 'logout'])->name('logout')->middleware(Authenticate::class);

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->name('register.post');

Route::get('posts', [PostController::class, 'index'])->name('posts.index')->middleware(Authenticate::class);
Route::get('posts/create', [PostController::class, 'create'])->name('posts.create')->middleware(Authenticate::class);
Route::get('posts/{id}', [PostController::class, 'show'])->name('posts.show')->middleware(Authenticate::class);
Route::post('posts', [PostController::class, 'store'])->name('posts.store')->middleware(Authenticate::class);
Route::delete('posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware(Authenticate::class);
