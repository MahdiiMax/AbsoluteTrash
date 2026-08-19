<?php

declare(strict_types=1);

// use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Trash\Routing\Facades\Route;

// Route::get('/users/{id}', [HomeController::class, 'show'])->name('users.show');
Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
