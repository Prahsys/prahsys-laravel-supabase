# Prahsys Laravel Supabase Integration

A Laravel package for seamless integration with Supabase, providing PostgreSQL-specific features for Laravel applications.

## Development

This package can be developed locally within a Laravel application. The main project includes tooling to easily switch between local development and remote package installations.

### Working with Local Package Development

To switch between developing locally and using the published package:

1. **Use local development version:**
   ```bash
   composer packages:dev-mode
   composer update prahsys/laravel-supabase --prefer-source
   ```

2. **Use remote published version:**
   ```bash
   composer packages:deploy-mode
   composer update prahsys/laravel-supabase
   ```

The local development mode creates a symlink to the package in the `packages/` directory, allowing you to make changes directly to the package code while testing in your application.

## Features

### Supabase Database Driver

The package registers a dedicated `supabase` database driver that you can use in your Laravel database configuration. This driver extends the default PostgreSQL driver with automatic handling of UUID columns in:

- Where clauses
- Join conditions
- WhereIn statements

The driver works in two ways:
1. It automatically detects common UUID column patterns (like 'id', '*_id', etc.)
2. It works with the `CastsUuidColumns` trait to allow explicit UUID column definition

## UUID Handling in Supabase

When working with UUIDs in Supabase, comparing a UUID column with a string value requires explicit casting. This is especially important because **Supabase doesn't allow creating global implicit casts or custom operators** that would normally solve this issue in a standard PostgreSQL installation.

```sql
-- This fails in Supabase:
SELECT * FROM users WHERE id = '123e4567-e89b-12d3-a456-426614174000';

-- This works:
SELECT * FROM users WHERE CAST(id AS TEXT) = '123e4567-e89b-12d3-a456-426614174000';
```

With the Supabase driver, these casts are applied automatically, so you can write normal Laravel queries:

```php
// These work automatically with the Supabase driver
$user = User::find($uuidString);
$user = User::where('id', $uuidString)->first();
$users = User::whereIn('id', [$uuid1, $uuid2])->get();

// Joins also work automatically
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where('users.email', 'test@example.com')
    ->get();
```

### Explicit UUID Column Definition

For more precise control, you can use the `CastsUuidColumns` trait to explicitly define UUID columns in your models:

```php
use Prahsys\Supabase\Traits\CastsUuidColumns;

class Post extends Model
{
    use CastsUuidColumns;
    
    // Define additional UUID columns beyond the primary key
    protected $uuidColumns = [
        'user_id',
        'other_uuid_column',
    ];
}
```

The trait:
1. Automatically includes the primary key as a UUID column
2. Adds any columns specified in the `$uuidColumns` property
3. Communicates this information to the query builder for proper UUID handling

### Custom UUID Column Detection

For advanced use cases, you can also register a custom UUID column detector function:

```php
use Prahsys\Supabase\Database\Query\Grammars\PostgresGrammar;

// In a service provider or bootstrap file
PostgresGrammar::detectUuidColumnsWith(function ($columnName, $query) {
    // Your custom logic to determine if a column is a UUID
    // For example, use a specific naming pattern or check against a list
    return str_contains($columnName, 'uuid_') || in_array($columnName, ['custom_uuid_field']);
});
```

This allows for full customization of UUID column detection beyond the built-in naming conventions.

## Installation

```bash
composer require prahsys/laravel-supabase
```

### Database Configuration

You can use the custom `supabase` driver in your `config/database.php`:

```php
'connections' => [
    'supabase' => [
        'driver' => 'supabase',
        'url' => env('DATABASE_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
    ],
    
    // Your other connections...
],
```

The `supabase` driver is a PostgreSQL driver with additional configurations optimized for Supabase, so you can use it just like the standard `pgsql` driver.

## Development and Testing

### Running Tests

To run the tests:

```bash
composer test
```

This will run all tests using SQLite in-memory database for speed.

### Testing with a Real Supabase Connection

To test with a real Supabase database:

1. Edit the `.env.testing` file with your Supabase credentials
2. Run the special test command:

```bash
composer test-supabase
```

This is useful for verifying PostgreSQL functionality in an actual database environment.

## Compatibility

- Laravel 10.x, 11.x, and 12.x
- PHP 8.1 and above
- PostgreSQL databases including Supabase

## License

This package is open-sourced software licensed under the MIT license.
