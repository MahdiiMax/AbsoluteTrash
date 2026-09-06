@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <h2>{{ $post->title }}</h2>
    <p>By <strong>{{ $post->author()?->name ?? 'Unknown' }}</strong></p>
    <p><small>{{ $post->created_at }}</small></p>

    <div>
        {{ $post->body }}
    </div>

    <p><a href="/posts">&larr; Back to posts</a></p>
@endsection