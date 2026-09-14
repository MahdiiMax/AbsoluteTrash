@extends('layouts.app')

@section('title', 'Posts')

@section('content')
    <h2>Posts ({{ $total }} total)</h2>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if (session('errors'))
        <ul style="color: red;">
            @foreach (session('errors') as $field => $messages)
                @foreach ($messages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif

    <p><a href="{{ route('posts.create') }}">Create a post</a></p>

    <ul>
        @foreach ($posts as $post)
            <li>
                <strong><a href="{{ route('posts.show', ['id' => $post->id]) }}">{{ $post->title }}</a></strong>
                <span>by {{ $post->author()?->name ?? 'Unknown' }} ({{ $post->created_at }})</span>
            </li>
        @endforeach
    </ul>

    @if ($pages > 1)
        <div>
            Page {{ $page }} of {{ $pages }}

            @if ($page > 1)
                <a href="{{ route('posts.index') . '?page=' . ($page - 1) }}">&laquo; Prev</a>
            @endif

            @for ($i = 1; $i <= $pages; $i++)
                @if ($i === $page)
                    <strong>{{ $i }}</strong>
                @else
                    <a href="{{ route('posts.index') . '?page=' . $i }}">{{ $i }}</a>
                @endif
            @endfor

            @if ($page < $pages)
                <a href="{{ route('posts.index') . '?page=' . ($page + 1) }}">Next &raquo;</a>
            @endif
        </div>
    @endif
@endsection
