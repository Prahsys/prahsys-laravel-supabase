# Prahsys Laravel Supabase Integration

A Laravel package for seamless integration with Supabase, providing automatic UUID handling and PostgreSQL-specific features for Laravel applications.

## Features

### UUID Handling

When working with UUIDs in Supabase, comparing a UUID column with a string value requires explicit casting. For example:

```sql
-- This fails:
SELECT * FROM users WHERE id = '123e4567-e89b-12d3-a456-426614174000';

-- This works:
SELECT * FROM users WHERE CAST(id AS TEXT) = '123e4567-e89b-12d3-a456-426614174000';
```

This package automatically handles the casting for you, solving issues with:
- Route model binding with UUIDs
- Joining tables with UUID foreign keys
- Using `whereIn` and other query methods with UUIDs
- Working with models that use UUID primary keys

## Installation

```bash
composer require prahsys/laravel-supabase
```

Add the service provider to your `config/app.php` file:

```php
'providers' => [
    // ...
    Prahsys\Supabase\SupabaseServiceProvider::class,
],
```

## Usage

### Basic Usage

For most applications, just installing the package is enough. It will handle common UUID patterns automatically.

### Model-Specific UUID Configuration

For more control, add the `CastsUuidColumns` trait to your models:

```php
use Prahsys\Supabase\Traits\CastsUuidColumns;

class User extends Model
{
    use CastsUuidColumns;
    
    // Optionally specify additional UUID columns
    protected $uuidColumns = [
        'custom_uuid_column',
        'another_uuid_field',
    ];
}
```

### How It Works

The package:

1. Automatically detects UUID columns based on naming patterns
2. Adds `CAST(column AS TEXT)` to SQL queries when comparing UUID columns
3. Works seamlessly with Laravel's query builder and Eloquent
4. Handles joins, where clauses, and other query types

## Compatibility

- Laravel 8.x and above
- PostgreSQL databases including Supabase
- Works with Laravel's UUID support and custom UUID implementations

## License

This package is open-sourced software licensed under the MIT license.