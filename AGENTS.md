## Commands

```bash
composer phpstan       # PHPStan level 6 (uses --xdebug flag)
composer psalm         # Psalm level 8
composer phpcs         # PSR-12 summary
composer phpcs-fixer   # Auto-fix PSR-12
composer test          # PHPUnit 9.5
```

## Test quirks

- **PostgresTest** runs offline — forces `DB_SECURITY_ENVIROMENT=true`, `DB_HOST=127.0.0.1` (not `localhost`) to get instant "Connection refused" instead of OS timeout.
- **DotenvTest** isolates each test by `chdir()` into a temp dir; cleans up with `rmdir()`. The `setUp()` clears `DB_SECURITY_ENVIROMENT`.

## Architecture

```
PGDatabase\
├── Dotenv       — static `connection_string_pg()`; loads from getenv() or ../.env or ./.env
├── Postgres     — singleton, methods are static; wraps pg_connect/pg_query/pg_update/pg_delete/pg_query_params
└── Models\
    ├── Model    — abstract; uses $this->id !== 0 to decide insert vs update
    └── DataType — constants only
```

## Non-obvious

- **`Model::extractDataValues`** uses PHP 8.0 `match()` — but `composer.json` claims `^7.4|^8.3`. Building for PHP 7.4 will fail.
- **`DataType::DATE/DATETIME/ARRAY/JSON/OBJECT`** are defined but **not handled** in `extractDataValues` — they fall through to `(string)$rawValue`.
- **`Model::buildConditions()`** auto-appends `soft_delete=false` and `editable=true` to WHERE conditions. All `update()`, `save()`, `saveById()`, `updateAndFind()` calls get these appended silently.
- **`pg_update()` / `pg_delete()`** (native PHP functions) are used instead of raw SQL for `Postgres::update()` and `Postgres::delete()`.
- **`Postgres::close()`** does not reset the singleton — subsequent calls operate on a closed connection.
- **PHPStan config** sets `phpVersion: 70100` and `reportUnmatchedIgnoredErrors: false`; ignores a block of "no value type in iterable array" and "never read, only written" errors.
- **Psalm config** uses `errorLevel="8"` (very permissive) and `phpVersion="7.1"` — different from composer.json's `^7.4`.
