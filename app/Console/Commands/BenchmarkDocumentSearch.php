<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentSection;
use App\Services\DocumentSearchService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BenchmarkDocumentSearch extends Command
{
    protected $signature = 'documents:benchmark-search {section_slug} {search}';

    protected $description = 'Benchmark categorized document search performance';

    public function handle(DocumentSearchService $searchService): int
    {
        $section = DocumentSection::where('slug', $this->argument('section_slug'))->firstOrFail();
        $searchTerm = $this->argument('search');

        $baseQuery = Document::query()
            ->published()
            ->where('section_id', $section->id);

        $applySort = fn ($q) => $q->latest('published_at');
        $request = Request::create('/', 'GET', [
            'search' => $searchTerm,
            'sort' => 'latest',
            'per_page' => 12,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $start = microtime(true);
        $results = $searchService->searchCategorized(
            clone $baseQuery,
            $searchTerm,
            $request,
            $applySort,
            withAlVariant: true,
            includeAnyTab: false
        );
        $elapsedMs = round((microtime(true) - $start) * 1000, 2);
        $queries = count(DB::getQueryLog());

        $parsed = $searchService->parseSearchQuery($searchTerm);
        $counts = $results['tab_counts'] ?? $searchService->computeTabCounts(clone $baseQuery, $parsed, true);

        $this->info("Section: {$section->name} ({$section->id})");
        $this->info("Search: {$searchTerm}");
        $this->line('---');
        $this->info("Phrase matches: {$counts['phrase']}");
        $this->info("All words matches: {$counts['all']}");
        foreach ($counts['per_word'] as $word => $count) {
            $this->info("Word [{$word}] matches: {$count}");
        }
        $this->info("Unique total: {$results['unique_total']}");
        $this->info("Active tab: {$results['active_tab']}");
        $this->line('---');
        $this->info("Time: {$elapsedMs} ms");
        $this->info("SQL queries: {$queries}");

        return self::SUCCESS;
    }
}
