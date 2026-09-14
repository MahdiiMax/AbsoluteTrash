<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Absolute Trash')</title>
</head>

<body>
    <header>
        <h1>{{ $framework }}</h1>
        <nav>
            @if (auth()->check())
                <a href="{{ route('dashboard.index') }}">Dashboard</a>
                <a href="{{ route('posts.index') }}">Posts</a>
                <a href="{{ route('users.index') }}">Users</a>
                <a href="{{ route('logout') }}">Logout</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endif
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        @yield('footer', '© Absolute Trash')
    </footer>
</body>

</html>
