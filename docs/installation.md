# Installation

## Requirements

- PHP 8.4 or newer
- `ext-mbstring` (multibyte string extension)
- Composer 2

## Setup

1. Install dependencies:

   ```bash
   composer install
   ```

2. Create the environment file:

   ```bash
   cp .env.example .env
   ```

3. Configure the database in `.env`. For SQLite:

   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database.sqlite
   ```

   (SQLite needs an empty database file; use `touch database/database.sqlite`.)

4. Run migrations:

   ```bash
   php bin/trash migrate
   ```

## Serving

```bash
php bin/trash serve
```

The app is then available at `http://localhost:8000` (or `APP_URL`).

## Useful commands

```bash
php bin/trash migrate          # run pending migrations
php bin/trash migrate:fresh    # drop all tables and re-run migrations
php bin/trash db:seed          # seed the database
php bin/trash route:list       # list registered routes
php bin/trash storage:link     # symlink public/storage to storage/app
php bin/trash cache:clear      # clear the view cache
```

## Running tests

```bash
vendor/bin/phpunit
```

Migrations, seeds, and the full test suite use the in-memory SQLite
configuration automatically.