<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable the index advisor. Default true, but you may want to toggle it
    | on only in local/staging environments.
    |
    */

    'enabled' => env('INDEX_ADVISOR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold (ms)
    |--------------------------------------------------------------------------
    |
    | Queries slower than this threshold (in milliseconds) become candidates
    | for analysis and index suggestions.
    |
    */

    'slow_query_threshold_ms' => env('INDEX_ADVISOR_SLOW_QUERY_MS', 200),

    /*
    |--------------------------------------------------------------------------
    | Ignore Tables
    |--------------------------------------------------------------------------
    |
    | Tables that should be ignored by the advisor (e.g., migrations, jobs).
    |
    */

    'ignore_tables' => [
        'migrations',
        'jobs',
        'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore specific columns (global)
    |--------------------------------------------------------------------------
    */

    'ignore_columns' => [
        'id',
        'created_at',
        'updated_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore columns by table
    |--------------------------------------------------------------------------
    |
    | e.g. 'users' => ['password', 'remember_token']
    |
    */

    'ignore_by_table' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache key & TTL
    |--------------------------------------------------------------------------
    */

    'cache_key' => env('INDEX_ADVISOR_CACHE_KEY', 'index_advisor:queries'),
    'cache_ttl' => env('INDEX_ADVISOR_CACHE_TTL', 60 * 60 * 24),

    /*
    |--------------------------------------------------------------------------
    | Maximum stored query events
    |--------------------------------------------------------------------------
    */

    'max_storage' => env('INDEX_ADVISOR_MAX_STORAGE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Usage threshold
    |--------------------------------------------------------------------------
    |
    | Number of times a column appears in queries before suggesting an index
    | (if slow queries are not detected).
    |
    */

    'usage_threshold' => env('INDEX_ADVISOR_USAGE_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Whether to auto generate migrations (disabled by default)
    |--------------------------------------------------------------------------
    */

    'auto_generate_migrations' => env('INDEX_ADVISOR_AUTO_GENERATE', false),

];
