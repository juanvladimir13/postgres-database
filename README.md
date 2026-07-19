# postgres-database

A lightweight native PHP package for interacting with PostgreSQL databases. It provides a simple connection wrapper, an environment variable loader, and an Active Record-style base model for building data-driven applications with minimal overhead.

## Features

- **Environment Configuration**: `Dotenv` class to securely load database credentials (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) from `.env` files or environment variables.
- **PostgreSQL Connection Wrapper**: `Postgres` singleton class providing static methods for queries, inserts, updates, deletes, and fetch operations using associative arrays.
- **Active Record Base Model**: Abstract `Model` class that simplifies CRUD operations, supporting soft deletes, state management (`EDITABLE`), and standardized response formats. `find()`, `findAll()`, and `delete()` are unified — their behavior adapts based on the `SOFT_DELETE` flag.
- **Data Type Casting**: `DataType` constants for mapping request payloads to specific data types (int, float, string, boolean, utf-8, etc.).

## Architecture

```
src/
├── Dotenv.php              # Environment variable loader & connection string builder
├── Postgres.php            # Singleton wrapper over native PHP pg_* functions
├── Models/
│   ├── Model.php           # Abstract Active Record base class
│   └── DataType.php        # Data type constants for casting
├── Views/                  # Reserved for future view renderers
```

## Quick Start

```php
use PGDatabase\Postgres;
use PGDatabase\Dotenv;

// Connection string is auto-generated from .env or environment
$connectionString = Dotenv::connection_string_pg();

// Fetch all rows
$users = Postgres::fetchAll('SELECT * FROM users');

// Fetch with parameters (safe from SQL injection)
$user  = Postgres::fetchAllParams('SELECT * FROM users WHERE id = $1', [1]);

// Insert and get the new ID
$id = Postgres::insert('users', ['name' => 'John', 'email' => 'john@example.com']);

// Update
Postgres::update('users', ['name' => 'Jane'], ['id' => 1]);

// Delete
Postgres::delete('users', ['id' => 1]);
```

## Working with Models

Create a model extending `PGDatabase\Models\Model`:

```php
use PGDatabase\Models\Model;

class User extends Model
{
    protected $TABLE_NAME = 'users';
    protected $ORDER_BY_COLUMNS = 'id DESC';
    protected $EDITABLE = true;
    protected $SOFT_DELETE = true;

    public function setRequest(array $request): void
    {
        $this->id = (int)($request['id'] ?? 0);
        $this->setValues(self::extractDataValues([
            'name'  => ['datatype' => DataType::STRING],
            'email' => ['datatype' => DataType::STRING],
            'age'   => ['datatype' => DataType::INT],
        ], $request));
    }

    public function getData(): array
    {
        return $this->getValues();
    }
}

$user = new User();
$user->setRequest(['name' => 'John', 'email' => 'john@test.com', 'age' => 30]);
$result = $user->save(); // Insert
$result = $user->save(); // Update (id is set after first save)
```

## Available Commands

```bash
composer phpstan       # PHPStan static analysis (level 6)
composer psalm         # Psalm static analysis (level 8)
composer phpcs         # Code style check (PSR-12, summary)
composer phpcs-detail  # Code style check (PSR-12, detailed)
composer phpcs-fixer   # Auto-fix code style to PSR-12
composer test          # Run PHPUnit tests
```

## Requirements

- PHP ^7.4|^8.3
- ext-pgsql (native PostgreSQL extension)

## License

MIT
