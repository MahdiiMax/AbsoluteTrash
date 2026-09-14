@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <h2>{{ $user->name }}</h2>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <a href="{{ route('users.index') }}">&larr; Back to users</a>
@endsection
