<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Trash\Routing\Facades\Route;

Route::get('/users/{id}', [HomeController::class, 'show'])->name('users.show');