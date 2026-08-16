@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <p>Welcome to {{ $framework }}.</p>
    <ul>
        @foreach($items as $item)
            <li>{{ $loop->iteration }}. {{ $item }}</li>
        @endforeach
    </ul>
    @include('partials.card', ['title' => 'View Engine'])
@endsection