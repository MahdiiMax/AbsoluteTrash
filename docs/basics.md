# Basics

## Routing

Routes live in `routes/web.php`:

```php
use Trash\Routing\Facades\Route;

Route::get('users', [UserController::class, 'index'])
    ->name('users.index')
    ->middleware(Authenticate::class);
```

Supported verbs: `get`, `post`, `put`, `patch`, `delete`, `options`.
Segments like `{id}` become controller method arguments; append `?` for
optional segments (`{id?}`).

## Controllers

Controllers are plain classes. The container resolves constructor and method
arguments automatically, so dependencies and `int $id` route parameters just
work:

```php
public function show(int $id): View
{
    return view('users.show', ['user' => User::findOrFail($id)]);
}
```

## Middleware

Global middleware (from `config/app.php`) runs in this order:

1. `AddHeaderMiddleware`
2. `StartSession`
3. `ConvertMethod` (supports `_method` spoofing for PUT/PATCH/DELETE)
4. `VerifyCsrfToken`

Route middleware — like `Authenticate` — is applied per route via `->middleware()`.

## Views

Render a view with `view('posts.index', [...])` using Blade-like syntax:
`{{ $var }}`, `@if`, `@foreach`, `@extends`/`@section`, and `@csrf` / `@method`.

## Sessions & auth

- `session()` — the session store; `session('key', $default)` reads, `flash()` sets one-time values.
- `auth()` / `Auth` facade — `check()`, `user()`, `id()`, `login()`, `logout()`.
- CSRF is enforced on all non-GET routes; the `@csrf` directive renders the token.