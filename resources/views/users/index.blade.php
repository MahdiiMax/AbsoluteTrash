@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <h2>All Users</h2>
    <ul>
        @foreach($users as $user)
            <li>
                <a href="/users/{{ $user->id }}">{{ $user->name }}</a>
                &lt;{{ $user->email }}&gt;
            </li>
        @endforeach
    </ul>
@endsection