<?php

namespace SagarSBhedodkar\IndexAdvisor\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use SagarSBhedodkar\IndexAdvisor\Services\Advisor;

class QueryExecutedListener
{
    protected Advisor $advisor;

    public function __construct(Advisor $advisor)
    {
        $this->advisor = $advisor;
    }

    /**
     * Handle the event.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event): void
    {
        // Format SQL with bindings
        $sql = $this->formatSql($event->sql, $event->bindings);

        // time is in milliseconds sometimes as float/double depending on DB driver
        $time = isset($event->time) ? floatval($event->time) : 0.0;

        $connection = $event->connectionName ?? 'default';

        $this->advisor->recordQuery($sql, $event->bindings ?? [], $time, $connection);
    }

    protected function formatSql(string $sql, array $bindings): string
    {
        // naive but practical approach: replace ? with bindings
        foreach ($bindings as $binding) {
            $value = is_string($binding) ? "'".addslashes($binding)."'" : (is_null($binding) ? 'NULL' : $binding);
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }
}
