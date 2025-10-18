<?php

namespace SagarSBhedodkar\IndexAdvisor;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use SagarSBhedodkar\IndexAdvisor\Listeners\QueryExecutedListener;
use SagarSBhedodkar\IndexAdvisor\Services\Advisor;

class IndexAdvisorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/index-advisor.php', 'index-advisor');

        $this->app->singleton(Advisor::class, function ($app) {
            return new Advisor(
                $app['config']->get('index-advisor'),
                $app['cache.store'] ?? $app['cache']
            );
        });

        $this->app->alias(Advisor::class, 'index-advisor');
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/config/index-advisor.php' => config_path('index-advisor.php'),
        ], 'config');

        // Publish migration stubs (if you want to keep stubs)
        $this->publishes([
            __DIR__ . '/stubs' => base_path('stubs/index-advisor'),
        ], 'stubs');

        // Register console command
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\GenerateMigrationCommand::class,
            ]);
        }

        // Listen to DB queries only when enabled
        $config = $this->app['config']->get('index-advisor');
        if (($config['enabled'] ?? true) && $this->app->environment() !== 'production') {
            DB::listen(function (QueryExecuted $query) {
                $listener = new QueryExecutedListener(app(Advisor::class));
                $listener->handle($query);
            });
        }
    }
}
