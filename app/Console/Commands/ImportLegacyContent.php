<?php

namespace App\Console\Commands;

use App\Services\LegacyContentImporter;
use Illuminate\Console\Command;

class ImportLegacyContent extends Command
{
    protected $signature = 'legacy:import-content
        {--purge : Delete trial posts, documents, and nashras before import (keeps podcasts)}
        {--dry-run : Show counts without writing to the database}
        {--legacy-db=mksnow : Source MySQL database name}
        {--legacy-storage= : Path to old project root or storage folder for media files}
        {--skip-documents : Skip document import}
        {--skip-posts : Skip posts import (blogs + didyouknows)}
        {--skip-nashras : Skip nashras import}
        {--no-search-rebuild : Skip document search index rebuild after import}
        {--sanitize : Sanitize HTML during import (slower)}';

    protected $description = 'Purge trial content and import legacy data from mksnow into mks';

    public function handle(LegacyContentImporter $importer): int
    {
        if (! $this->option('purge')) {
            $this->warn('Tip: use --purge to delete trial content before import (recommended).');
        }

        return $importer->run($this->output, [
            'purge' => (bool) $this->option('purge'),
            'dry_run' => (bool) $this->option('dry-run'),
            'legacy_db' => (string) $this->option('legacy-db'),
            'legacy_storage' => $this->option('legacy-storage'),
            'skip_documents' => (bool) $this->option('skip-documents'),
            'skip_posts' => (bool) $this->option('skip-posts'),
            'skip_nashras' => (bool) $this->option('skip-nashras'),
            'no_search_rebuild' => (bool) $this->option('no-search-rebuild'),
            'fast' => ! (bool) $this->option('sanitize'),
        ]);
    }
}
