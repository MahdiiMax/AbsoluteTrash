# Absolute Trash

> A hand-built PHP 8.4 MVC framework: attribute-based routing, PSR-7/PSR-15 HTTP
> layer, Eloquent-style ORM, Blade-like view engine, migrations, and a full CLI —
> with zero runtime dependencies.

Built from scratch as a portfolio piece and an ongoing exercise in modern PHP
architecture. **This is a work in progress.**

## Status

- [x] Foundation (container, facades, helpers)
- [x] HTTP messages (PSR-7): Stream, Uri, Request, Response, ServerRequest, UploadedFile
- [x] Attribute-based routing
- [x] Middleware pipeline (PSR-15)
- [x] View engine
- [x] Database / ORM / migrations
- [x] Auth, validation, sessions
- [x] Mail / storage
- [x] Console CLI
- [x] Demo app
- [x] Tests & docs

## Requirements

- PHP 8.4 or newer
- `ext-mbstring` (multibyte string extension)
- Composer 2

## Installation

1. Clone the repository.
2. `composer install`
3. `cp .env.example .env` and configure your database.
4. `php bin/trash migrate`
5. `php bin/trash serve`

Full guide: [docs/installation.md](docs/installation.md)

## Documentation

- [Installation](docs/installation.md)
- [Basics](docs/basics.md)

## License

[MIT](LICENSE) © 2026 Mahdi Sadeghi
