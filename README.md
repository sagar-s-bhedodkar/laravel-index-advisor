# Laravel Index Advisor

**Monitor your Eloquent/DB queries in Laravel and get actionable index suggestions to optimize performance.**

[![Latest Version](https://img.shields.io/packagist/v/sagar-s-bhedodkar/laravel-index-advisor.svg)](https://packagist.org/packages/sagar-s-bhedodkar/laravel-index-advisor)
[![License](https://img.shields.io/packagist/l/sagar-s-bhedodkar/laravel-index-advisor.svg)](https://packagist.org/packages/sagar-s-bhedodkar/laravel-index-advisor)

---

## Features

* Record all executed queries in your application (Eloquent & Query Builder).
* Analyse queries for **slow execution** or **frequent usage**.
* Suggest database indexes per table/column automatically.
* Optionally generate **migration stubs** for easy index creation.
* Configurable cache driver and storage limits.
* Lightweight and developer-friendly; works only in non-production environments by default.

---

## Installation

Install via Composer:

```bash
composer require sagar-s-bhedodkar/laravel-index-advisor
```

If using **local path during development**:

```json
"repositories": [
    {
        "type": "path",
        "url": "../laravel-index-advisor"
    }
]
```

Then require it via Composer:

```bash
composer require sagar-s-bhedodkar/laravel-index-advisor:@dev
```

---

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="SagarSBhedodkar\IndexAdvisor\IndexAdvisorServiceProvider" --tag=config
```

This creates:

```bash
config/index-advisor.php
```

### Example `config/index-advisor.php`

```php
return [
    'enabled' => true,                        // Enable/disable advisor
    'slow_query_threshold_ms' => 200,         // Query time threshold for slow queries
    'ignore_tables' => ['migrations', 'jobs', 'failed_jobs'],
    'ignore_columns' => ['id', 'created_at', 'updated_at'],
    'ignore_by_table' => [],
    'cache_key' => 'index_advisor:queries',
    'cache_ttl' => 86400,                     // Cache TTL in seconds
    'max_storage' => 1000,                     // Max queries to store
    'usage_threshold' => 5,                    // Minimum repeat usage for suggestion
    'auto_generate_migrations' => false,      // Generate migration stub automatically
    'cache_driver' => 'file',                 // Cache driver for storing queries
    'min_rows' => 50,                          // Minimum rows in table to consider indexing
];
```

---

## Usage

### 1. Analyse queries

After running your application and executing queries, retrieve suggestions:

```php
use SagarSBhedodkar\IndexAdvisor\Facades\IndexAdvisor;

$suggestions = IndexAdvisor::analyse();
```

Example output:

```json
[
  {
    "table": "users",
    "columns": ["email"],
    "reason": "slow_query_21.17ms",
    "sql_examples": [
      "select * from `users` where `email` like 'user%@example.com' order by `name` asc"
    ]
  }
]
```

---

### 2. Generate Migration Stubs

```php
$migrationCode = IndexAdvisor::generateMigrationStub($suggestions);
file_put_contents(database_path('migrations/' . now()->format('Y_m_d_His') . '_add_indexes.php'), $migrationCode);
```

This creates a migration file with `up()` and `down()` methods for each suggested index.

---

### 3. Clear Stored Queries

To reset analysis cache:

```php
IndexAdvisor::clearStored();
```

---

## Facade

You can use the Facade for convenience:

```php
use SagarSBhedodkar\IndexAdvisor\Facades\IndexAdvisor;

$suggestions = IndexAdvisor::analyse();
```

---

## Commands

If needed, you can add custom Artisan commands for your workflow (like generating migration automatically).
These are registered automatically when running in console mode.

---

## Notes

* Advisor is **designed for development and staging environments** — do **not enable in production** unless you fully understand implications.
* Works with any database supported by Laravel (MySQL, PostgreSQL, SQLite, etc.).
* Cache driver can be configured in `config/index-advisor.php`.

---

## Contributing

1. Fork the repository.
2. Make your changes.
3. Submit a Pull Request.

---

## License

MIT License © Sagar Sunil Bhedodkar
