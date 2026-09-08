@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <h2>{{ $post->title }}</h2>
    <p>By <strong>{{ $post->author()?->name ?? 'Unknown' }}</strong></p>
    <p><small>{{ $post->created_at }}</small></p>

    <div>
        {{ $post->body }}
    </div>

    <p><a href="{{ back() }}">&larr; Back to posts</a></p>

    @if ($post->user_id === auth()->id())
        <form method="POST" action="{{ route('posts.destroy', ['id' => $post->id]) }}" onsubmit="return confirm('Delete this post?');">
            @method('DELETE')
            <button type="submit">Delete</button>
        </form>
    @endif
@endsection
