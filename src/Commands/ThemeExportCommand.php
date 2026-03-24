<?php

namespace Nlk\Theme\Commands;

use Illuminate\Console\Command;
use Nlk\Theme\FlexPage\SectionRegistry;
use Nlk\Theme\PageBuilder\PageBuilder;

class ThemeExportCommand extends Command
{
    protected $signature = 'theme:export
                            {page : Page key to export (e.g. home)}
                            {--tenant= : Tenant ID}
                            {--out= : Output file path (optional)}';

    protected $description = 'Export a page layout as FlexPage JSON';

    public function handle(PageBuilder $builder): int
    {
        $pageKey  = $this->argument('page');
        $tenantId = $this->option('tenant') ?? config('theme.default_tenant', 'default');
        $outFile  = $this->option('out');

        $json = $builder->exportJson($pageKey, $tenantId);

        if ($outFile) {
            file_put_contents($outFile, $json);
            $this->info("Export saved to: {$outFile}");
        } else {
            $this->line($json);
        }

        return Command::SUCCESS;
    }
}
