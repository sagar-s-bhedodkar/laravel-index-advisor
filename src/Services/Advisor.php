<?php

namespace SagarSBhedodkar\IndexAdvisor\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Str;

class Advisor
{
    protected array $config;
    protected CacheRepository $cache;
    protected string $cacheKey;

    /**
     * Advisor constructor.
     *
     * @param array $config
     * @param \Illuminate\Contracts\Cache\Repository $cache
     */
    public function __construct(array $config = [], CacheRepository $cache = null)
    {
        $this->config = $config ?: config('index-advisor', []);
        $this->cache = $cache ?: app('cache');
        $this->cacheKey = $this->config['cache_key'] ?? 'index_advisor:queries';
    }

    /**
     * Record a query event.
     *
     * @param string $sql
     * @param array $bindings
     * @param float $time  milliseconds
     * @param string $connection
     * @return void
     */
    public function recordQuery(string $sql, array $bindings, float $time, string $connection): void
    {
        if (!($this->config['enabled'] ?? true)) {
            return;
        }

        // Ignore queries on ignored tables
        foreach ($this->config['ignore_tables'] ?? [] as $table) {
            if (stripos($sql, "`{$table}`") !== false
                || stripos($sql, " {$table} ") !== false
                || stripos($sql, "{$table}.") !== false
            ) {
                return;
            }
        }

        $stored = $this->cache->get($this->cacheKey, []);
        $stored[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'connection' => $connection,
            'recorded_at' => now()->toDateTimeString(),
        ];

        // trim stored size
        $max = $this->config['max_storage'] ?? 1000;
        if (count($stored) > $max) {
            $stored = array_slice($stored, -$max);
        }

        $this->cache->put($this->cacheKey, $stored, $this->config['cache_ttl'] ?? 60 * 60 * 24);
    }

    /**
     * Analyse stored queries and return suggestions.
     *
     * @return array
     */
    public function analyse(): array
    {
        $stored = $this->cache->get($this->cacheKey, []);
        if (empty($stored)) {
            return [];
        }

        $threshold = $this->config['slow_query_threshold_ms'] ?? 200;
        $usageThreshold = $this->config['usage_threshold'] ?? 5;
        $counts = [];
        $suggestions = [];

        foreach ($stored as $entry) {
            $sql = $entry['sql'];
            $time = floatval($entry['time']);
            $tables = $this->extractTables($sql);
            $columns = $this->extractWhereColumns($sql);

            foreach ($tables as $table) {
                foreach ($columns as $col) {
                    if ($this->shouldIgnoreColumn($table, $col)) continue;

                    $key = strtolower("{$table}::{$col}");
                    $counts[$key] = ($counts[$key] ?? 0) + 1;

                    $reason = $time >= $threshold ? "slow_query_" . round($time,2) . "ms"
                        : ($counts[$key] >= $usageThreshold ? "used_{$counts[$key]}_times" : null);

                    if ($reason) {
                        if (!isset($suggestions[$key])) {
                            $suggestions[$key] = [
                                'table' => $table,
                                'columns' => [$col],
                                'reason' => $reason,
                                'sql_examples' => [$sql],
                            ];
                        } else {
                            $suggestions[$key]['sql_examples'][] = $sql;
                            $suggestions[$key]['sql_examples'] = array_values(array_unique($suggestions[$key]['sql_examples']));
                        }
                    }
                }
            }
        }

        // Clean and reindex final output
        $final = [];
        foreach ($suggestions as $s) {
            $final[] = [
                'table' => $s['table'],
                'columns' => array_values(array_unique($s['columns'])),
                'reason' => $s['reason'],
                'sql_examples' => array_values(array_unique($s['sql_examples'])),
            ];
        }

        return $final;
    }

    /**
     * Generate a migration stub PHP content for given suggestions.
     *
     * @param array $suggestions
     * @return string
     */
    public function generateMigrationStub(array $suggestions): string
    {
        // Build migration content with up/down methods adding indexes for each suggestion
        $timestamp = now()->format('Y_m_d_His');
        $className = 'AddIndexesForIndexAdvisor' . now()->format('YmdHis');

        $up = [];
        $down = [];

        foreach ($suggestions as $i => $s) {
            $table = $s['table'];
            $cols = $s['columns'];
            $colsPhp = var_export($cols, true);

            // create index name
            $columnsForName = implode('_', $cols);
            $indexName = Str::snake("{$table}_{$columnsForName}_idx");

            $up[] = "            if (!Schema::hasColumn('{$table}', '{$cols[0]}')) {\n                // column doesn't exist; skip creating index for now\n            } else {\n                Schema::table('{$table}', function (Blueprint \$table) {\n                    \$table->index({$colsPhp}, '{$indexName}');\n                });\n            }";

            $down[] = "            Schema::table('{$table}', function (Blueprint \$table) {\n                \$table->dropIndex('{$indexName}');\n            });";
        }

        $upStr = implode("\n\n", $up);
        $downStr = implode("\n\n", $down);

        $migration = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {$className} extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
{$upStr}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
{$downStr}
    }
}

PHP;

        return $migration;
    }

    /**
     * Clear stored queries / suggestions.
     *
     * @return void
     */
    public function clearStored(): void
    {
        $this->cache->forget($this->cacheKey);
    }

    /**
     * Extract candidate table names with a best-effort regex.
     *
     * @param string $sql
     * @return array
     */
    protected function extractTables(string $sql): array
    {
        $tables = [];

        // Match FROM `table` or FROM table
        if (preg_match_all('/FROM\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Match JOIN `table`
        if (preg_match_all('/JOIN\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Remove any trailing fragments from previous regex issues
        $tables = array_map(fn($t) => trim($t, " `"), $tables);

        // Deduplicate
        return array_values(array_unique($tables));
    }

    /**
     * Extract candidate WHERE columns from a SQL string (very best-effort).
     *
     * @param string $sql
     * @return array
     */
    protected function extractWhereColumns(string $sql): array
    {
        $cols = [];

        // matches patterns like `table`.`column` or `column` or table.column
        if (preg_match_all('/WHERE\s+(.*?)(GROUP BY|ORDER BY|LIMIT|$)/is', $sql, $m)) {
            $where = $m[1][0];

            // split by AND/OR
            $parts = preg_split('/\s+(AND|OR)\s+/i', $where);

            foreach ($parts as $p) {
                // match column operator value
                if (preg_match('/`?([a-zA-Z0-9_]+)`?\s*(=|IN|LIKE|>|<|>=|<=|IS\s+NULL|IS\s+NOT\s+NULL)/i', $p, $mm)) {
                    $cols[] = $mm[1];
                } elseif (preg_match('/([a-zA-Z0-9_]+\.)?`?([a-zA-Z0-9_]+)`?\s*(=|IN|LIKE)/i', $p, $mm2)) {
                    $cols[] = $mm2[2];
                }
            }
        }

        // dedupe
        return array_values(array_unique(array_filter($cols)));
    }

    /**
     * Return whether a column should be ignored based on config.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function shouldIgnoreColumn(string $table, string $column): bool
    {
        $ignoreColumns = $this->config['ignore_columns'] ?? [];
        $ignoreByTable = $this->config['ignore_by_table'] ?? [];

        if (in_array($column, $ignoreColumns, true)) {
            return true;
        }

        if (isset($ignoreByTable[$table]) && in_array($column, (array)$ignoreByTable[$table], true)) {
            return true;
        }

        return false;
    }
}
