<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Trash\Routing\Attributes\Route;

class HomeController
{
    #[Route('GET', '/', name: 'home')]
    public function index(): string
    {
        return 'Absolute Trash From HomeController.';
    }

    public function show(int $id): array
    {
        return ['id' => $id, 'name' => "user-{$id}"];
    }
}
