@extends('layouts.app')

@section('title', 'Create Post')

@section('content')
    <h2>Create a Post</h2>

    @if(session('errors'))
        <ul style="color: red;">
            @foreach(session('errors') as $field => $messages)
                @foreach($messages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/posts">
        <div>
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required>
        </div>
        <div>
            <label for="body">Body</label>
            <textarea name="body" id="body" rows="6" required></textarea>
        </div>
        <button type="submit">Create</button>
    </form>

    <p><a href="/posts">&larr; Back to posts</a></p>
@endsection