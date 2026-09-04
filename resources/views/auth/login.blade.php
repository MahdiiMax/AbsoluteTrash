@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h2>Login</h2>

    @if(session('errors'))
        <ul style="color: red;">
            @foreach(session('errors') as $field => $messages)
                @foreach($messages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/login">
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
@endsection