<?php

namespace SagarSBhedodkar\IndexAdvisor\Helpers;

use Illuminate\Support\Facades\DB;

class ExplainQuery
{
    /**
     * Run EXPLAIN for a given SQL (MySQL/MariaDB/Postgres compatible best-effort).
     *
     * @param string $sql
     * @param array $bindings
     * @param string|null $connection
     * @return array|null
     */
    public static function explain(string $sql, array $bindings = [], ?string $connection = null): ?array
    {
        try {
            $conn = $connection ? DB::connection($connection) : DB::connection();
            // For MySQL/Postgres, prefix EXPLAIN. For some drivers, this may vary.
            $explainSql = 'EXPLAIN ' . $sql;
            $results = $conn->select($explainSql, $bindings);
            return json_decode(json_encode($results), true);
        } catch (\Throwable $e) {
            // Not fatal — return null if EXPLAIN cannot be run
            return null;
        }
    }
}
