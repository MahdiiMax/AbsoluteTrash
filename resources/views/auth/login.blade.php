@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h2>Login</h2>
    @if($errors ?? false)
        <p style="color: red;">{{ $errors }}</p>
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