# Laravel Index Advisor

Monitor Eloquent/DB queries in your Laravel application and get index suggestions (and scaffold migration stubs) based on actual query patterns.

**Package:** `sagar-s-bhedodkar/laravel-index-advisor`  
**License:** MIT

---

## Features

- Listen to DB queries (via `DB::listen`) and record queries in cache.
- Heuristic analysis to detect slow queries and frequently used WHERE columns.
- Suggest index(s) (single-column and simple composite support).
- Generate migration stubs you can review and run.
- Inject or call from controllers/services, or let it run automatically in dev/staging.
- Safe default: disabled in production by configuration; opt-in behavior.

---

## Installation

From your Laravel app root:

```bash
# require the package locally or via Packagist
composer require --dev sagar-s-bhedodkar/laravel-index-advisor
