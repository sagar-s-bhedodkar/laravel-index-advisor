<?php

namespace SagarSBhedodkar\IndexAdvisor\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use SagarSBhedodkar\IndexAdvisor\Services\Advisor;

class GenerateMigrationCommand extends Command
{
    protected $signature = 'index-advisor:generate 
                            {--path= : Custom path to write migrations (defaults to database/migrations)} 
                            {--clear : Clear stored queries after generate}';

    protected $description = 'Generate migration(s) for suggested indexes detected by Index Advisor.';

    protected Filesystem $files;

    protected Advisor $advisor;

    public function __construct(Filesystem $files, Advisor $advisor)
    {
        parent::__construct();
        $this->files = $files;
        $this->advisor = $advisor;
    }

    public function handle()
    {
        $this->info('Analyzing collected queries...');
        $suggestions = $this->advisor->analyse();

        if (empty($suggestions)) {
            $this->info('No index suggestions detected.');
            return 0;
        }

        $path = $this->option('path') ?: database_path('migrations');
        if (!is_dir($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }

        foreach ($suggestions as $suggestion) {
            $migrationContent = $this->advisor->generateMigrationStub([$suggestion]);
            $filename = $this->generateMigrationFilename($suggestion);

            $fullPath = $path . '/' . $filename;
            $this->files->put($fullPath, $migrationContent);

            $this->info("Migration generated: {$fullPath}");
        }

        if ($this->option('clear')) {
            $this->advisor->clearStored();
            $this->info('Stored queries cleared.');
        }

        $this->info('Done.');
        return 0;
    }

    protected function generateMigrationFilename(array $suggestion): string
    {
        $ts = now()->format('Y_m_d_His');
        $table = $suggestion['table'] ?? 'table';
        $cols = implode('_', $suggestion['columns'] ?? ['col']);
        $slug = preg_replace('/[^a-z0-9_]+/i', '_', strtolower("add_{$cols}_index_to_{$table}"));
        return "{$ts}_{$slug}.php";
    }
}
