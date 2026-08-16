<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Trash\Routing\Attributes\Route;
use Trash\View\View;

class HomeController
{
    #[Route('GET', '/', name: 'home')]
    public function index(): View
    {
        return view('home', ['framework' => 'Absolute Trash', 'items' => ['PSR-7', 'Routing', 'Middleware']]);
    }

    public function show(int $id): array
    {
        return ['id' => $id, 'name' => "user-{$id}"];
    }
}
