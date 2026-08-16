<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Absolute Trash')</title>
</head>
<body>
    <header>
        <h1>{{ $framework }}</h1>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        @yield('footer', '© Absolute Trash')
    </footer>
</body>
</html>