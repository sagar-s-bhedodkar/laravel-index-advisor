# 🧱 Laravel Index Advisor

> **Monitor your Eloquent/DB queries in Laravel and get actionable index suggestions to optimize performance.**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-10.x%20|%2011.x%20|%2012.x-red.svg)](https://laravel.com)
[![Packagist](https://img.shields.io/packagist/v/sagar-s-bhedodkar/laravel-index-advisor.svg)](https://packagist.org/packages/sagar-s-bhedodkar/laravel-index-advisor)

---

## 📘 Table of Contents

* [Introduction](#-introduction)
* [Features](#-features)
* [Installation](#-installation)
* [Configuration](#-configuration)
* [Usage](#-usage)
* [Artisan Commands](#-artisan-commands)
* [Contributing](#-contributing)
* [License](#-license)
* [Author](#-author)

---

## 🚀 Introduction

**Laravel Index Advisor** is a Laravel package that **monitors your database queries** and provides **intelligent suggestions for missing indexes**. It helps developers identify slow or frequently-used queries and optimize database performance with actionable recommendations.

---

## ✨ Features

* 📊 Tracks Eloquent and DB queries automatically
* ⏱️ Detects slow queries using a configurable threshold
* 🛠️ Suggests indexes for columns used in WHERE clauses
* 💾 Caches queries to analyze repeated usage
* 🔄 Optional migration generation for suggested indexes
* ⚙️ Artisan commands for query management
* ✅ Production-ready and configurable

---

## ⚙️ Installation

Require the package via Composer:

```bash
composer require sagar-s-bhedodkar/laravel-index-advisor:@dev
```

The package **auto-discovers** itself; no manual registration is required.

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=config
```

---

## 🛠 Configuration

The configuration file `config/index-advisor.php` allows you to customize the behavior:

```php
return [
    'enabled' => true,
    'slow_query_threshold_ms' => 200,
    'ignore_tables' => ['migrations', 'jobs', 'failed_jobs'],
    'ignore_columns' => ['id', 'created_at', 'updated_at'],
    'cache_key' => 'index_advisor:queries',
    'cache_ttl' => 86400,
    'max_storage' => 1000,
    'usage_threshold' => 5,
    'auto_generate_migrations' => false,
    'cache_driver' => 'file',
    'min_rows' => 50,
];
```

* `slow_query_threshold_ms`: Threshold for marking a query as slow
* `usage_threshold`: Number of times a column must be used to trigger an index suggestion
* `auto_generate_migrations`: If `true`, automatically generate migration stubs for missing indexes

---

## 🧠 Usage

### Record Queries

Laravel Index Advisor automatically records queries during runtime.

### Analyze Queries

To get index suggestions:

```php
use SagarSBhedodkar\IndexAdvisor\Facades\IndexAdvisor;

$suggestions = IndexAdvisor::analyse();
return response()->json($suggestions);
```

### Generate Migration Stub

```php
$stub = IndexAdvisor::generateMigrationStub($suggestions);
file_put_contents(database_path('migrations/' . now()->format('Y_m_d_His') . '_add_indexes.php'), $stub);
```

### Clear Stored Queries

```php
IndexAdvisor::clearStored();
```

---

## 🧰 Artisan Commands

* `php artisan index-advisor:generate-migration` — Generate a migration for suggested indexes
* Additional commands may be added for managing cache and recorded queries

---

## 🤝 Contributing

Contributions are welcome!

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Commit your changes: `git commit -m "Add new feature"`
4. Push to your fork: `git push origin feature/new-feature`
5. Submit a Pull Request 🎉

---

## 📄 License

This package is open-sourced software licensed under the **MIT license**.

---

## 👨‍💻 Author

**Sagar Sunil Bhedodkar**
📧 [sagarbhedodkar456@gmail.com](mailto:sagarbhedodkar456@gmail.com)
🌐 [GitHub Profile](https://github.com/sagar-s-bhedodkar)

---

> Made with ❤️ for Laravel developers who want actionable index suggestions and optimized database performance.
