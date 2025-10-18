<?php

namespace SagarSBhedodkar\IndexAdvisor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void recordQuery(string $sql, array $bindings, float $time, string $connection)
 * @method static array analyse()
 * @method static string generateMigrationStub(array $suggestions)
 * @method static void clearStored()
 *
 * @see \SagarSBhedodkar\IndexAdvisor\Services\Advisor
 */
class IndexAdvisor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SagarSBhedodkar\IndexAdvisor\Services\Advisor::class;
    }
}
