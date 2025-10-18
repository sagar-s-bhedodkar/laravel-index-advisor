<?php

namespace SagarSBhedodkar\IndexAdvisor\Tests;

use Orchestra\Testbench\TestCase;
use SagarSBhedodkar\IndexAdvisor\IndexAdvisorServiceProvider;
use SagarSBhedodkar\IndexAdvisor\Services\Advisor;

class AdvisorTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            IndexAdvisorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // use array cache driver for tests
        $app['config']->set('cache.default', 'array');
        $app['config']->set('index-advisor.enabled', true);
    }

    public function test_record_and_analyse_basic()
    {
        /** @var Advisor $advisor */
        $advisor = $this->app->make(Advisor::class);

        $sql = 'select * from `users` where `email` = ?';
        $bindings = ['test@example.com'];
        $advisor->recordQuery($sql, $bindings, 300.0, 'testing');

        $results = $advisor->analyse();

        $this->assertNotEmpty($results, 'Advisor should have at least one suggestion for slow query');
        $first = $results[0];
        $this->assertArrayHasKey('table', $first);
        $this->assertEquals('users', $first['table']);
    }

    public function test_generate_migration_stub_returns_php()
    {
        /** @var Advisor $advisor */
        $advisor = $this->app->make(Advisor::class);

        $suggestions = [
            [
                'table' => 'users',
                'columns' => ['email'],
                'reason' => 'slow_query_300ms',
                'sql_examples' => ['select * from `users` where `email` = ?']
            ]
        ];

        $php = $advisor->generateMigrationStub($suggestions);
        $this->assertStringContainsString('<?php', $php);
        $this->assertStringContainsString('class AddIndexesForIndexAdvisor', $php);
    }
}
