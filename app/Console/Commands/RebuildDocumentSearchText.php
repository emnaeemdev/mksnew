<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentSearchService;
use Illuminate\Console\Command;

class RebuildDocumentSearchText extends Command
{
    protected $signature = 'documents:rebuild-search-text {--chunk=100 : Number of documents per batch}';

    protected $description = 'Rebuild pre-normalized search_text for all documents';

    public function handle(DocumentSearchService $searchService): int
    {
        $chunk = max(1, (int) $this->option('chunk'));
        $total = Document::count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Document::query()
            ->with('plainFieldValues')
            ->orderBy('id')
            ->chunkById($chunk, function ($documents) use ($searchService, $bar) {
                foreach ($documents as $document) {
                    $document->search_text = $searchService->buildSearchText($document);
                    $document->saveQuietly();
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Document search_text rebuild completed.');

        return self::SUCCESS;
    }
}
