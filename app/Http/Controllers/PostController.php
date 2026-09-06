<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Trash\Auth\Facades\Auth;
use Trash\Http\RedirectResponse;
use Trash\View\View;

class PostController
{
    public function index(): View
    {
        $data = Post::paginate(5);
        return view('posts.index', [
            'posts' => $data['items'],
            'page'  => $data['page'],
            'pages' => $data['pages'],
            'total' => $data['total'],
        ]);
    }

    public function create(): View
    {
        return view('posts.create');
    }

    public function show(int $id): View
    {
        return view('posts.show', ['post' => Post::findOrFail($id)]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        Post::create([
            'user_id' => Auth::id(),
            'title'   => $request->input('title'),
            'body'    => $request->input('body'),
        ]);
        return redirect()->route('posts.index')->with('status', 'Post created successfully.');
    }
}
