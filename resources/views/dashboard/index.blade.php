@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h2>Dashboard</h2>

    @if(session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if(session('errors'))
        <ul style="color: red;">
            @foreach(session('errors') as $field => $messages)
                @foreach($messages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif

    <p>Welcome, <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>

    @if($user->avatar)
        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" width="100" height="100">
    @endif

    <form method="POST" action="{{ route('dashboard.avatar') }}" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="avatar">Avatar</label>
            <input type="file" name="avatar" id="avatar" accept="image/*" required>
        </div>
        <button type="submit">Upload</button>
    </form>
@endsection