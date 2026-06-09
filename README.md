# postgres-database

A lightweight native PHP package for interacting with PostgreSQL databases. It provides a simple connection wrapper, an environment variable loader, and an Active Record-style base model for building data-driven applications with minimal overhead.

## Features

- **Environment Configuration**: A minimal `Dotenv` class to securely load database credentials (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) from `.env` files.
- **PostgreSQL Connection Wrapper**: The `Postgres` class provides static methods to easily execute queries and perform `insert`, `update`, `delete`, and `fetchAll` operations using associative arrays.
- **Active Record Base Model**: An abstract `Model` class that simplifies CRUD operations, supporting soft deletes, state management (`EDITABLE`), and standardized response formats.
- **Data Type Casting**: Built-in support for mapping request payloads to specific data types (int, float, string, boolean, utf-8, etc.) using the `DataType` constants.

## Directory Structure (`src/`)

- `Dotenv.php`: Loads database connection settings and generates the PostgreSQL connection string.
- `Postgres.php`: Core singleton wrapper over native PHP `pg_*` functions.
- `Models/Model.php`: Abstract class providing high-level data access methods (like `save`, `find`, `findAll`, `delete`, `restore`) to be inherited by your application's entities.
- `Models/DataType.php`: Defines constants for data types used in request data extraction and casting.
