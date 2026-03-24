<?php

namespace Nlk\Theme\Commands;

use Illuminate\Console\Command;
use Nlk\Theme\PageBuilder\PageBuilder;

class ThemeImportCommand extends Command
{
    protected $signature = 'theme:import
                            {file : Path to JSON file}
                            {--tenant= : Tenant ID}';

    protected $description = 'Import a FlexPage JSON page layout into the database';

    public function handle(PageBuilder $builder): int
    {
        $file     = $this->argument('file');
        $tenantId = $this->option('tenant') ?? config('theme.default_tenant', 'default');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $json = file_get_contents($file);

        try {
            $page = $builder->importJson($json, $tenantId);
            $this->info("Page [{$page->page_key}] imported for tenant [{$tenantId}]. ID: {$page->id}");
        } catch (\Throwable $e) {
            $this->error("Import failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
