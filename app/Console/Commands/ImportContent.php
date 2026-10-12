<?php

namespace App\Console\Commands;

use App\Support\ContentImporter;
use Illuminate\Console\Command;

class ImportContent extends Command
{
    protected $signature = 'content:import
        {--force : Overwrite existing content with the original text (admin edits will be lost)}
        {--yes : Skip the confirmation question}';

    protected $description = 'Load the original website copy, lists, menus, offices and default settings into the database.';

    public function handle(): int
    {
        if ($this->option('force') && !$this->option('yes') && !$this->confirm('This replaces every page text, list, menu and setting with the original values. Continue?')) {
            return self::FAILURE;
        }
        $stats = (new ContentImporter((bool) $this->option('force')))->run();
        foreach ($stats as $what => $count) {
            $this->line(sprintf('  %-9s %d', $what, $count));
        }
        $this->info('Content imported.');
        return self::SUCCESS;
    }
}
