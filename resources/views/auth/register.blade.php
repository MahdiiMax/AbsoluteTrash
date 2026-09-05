@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <h2>Register</h2>

    @if(session('errors'))
        <ul style="color: red;">
            @foreach(session('errors') as $field => $messages)
                @foreach($messages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/register">
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>
        <button type="submit">Register</button>
    </form>
@endsection