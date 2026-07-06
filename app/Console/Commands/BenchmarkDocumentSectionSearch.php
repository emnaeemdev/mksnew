<?php

namespace App\Console\Commands;

use App\Http\Controllers\Frontend\DocumentController;
use App\Models\DocumentSection;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BenchmarkDocumentSectionSearch extends Command
{
    protected $signature = 'documents:benchmark-section {section_slug} {search}';

    protected $description = 'Benchmark full section search endpoint logic';

    public function handle(DocumentController $controller): int
    {
        app()->setLocale('ar');

        $section = DocumentSection::where('slug', $this->argument('section_slug'))->firstOrFail();
        $search = $this->argument('search');

        $request = Request::create(
            '/ar/documents/section/' . $section->slug,
            'GET',
            [
                'search' => $search,
                'sort' => 'latest',
                'per_page' => 12,
                'section_select' => (string) $section->id,
            ]
        );
        $request->setRouteResolver(function () use ($section) {
            $route = new \Illuminate\Routing\Route('GET', '/ar/documents/section/{section}', []);
            $route->bind($section);

            return $route;
        });

        DB::flushQueryLog();
        DB::enableQueryLog();

        $start = microtime(true);
        $response = $controller->section('ar', $request, $section);
        $elapsedMs = round((microtime(true) - $start) * 1000, 2);
        $queries = count(DB::getQueryLog());

        $data = $response->getData();
        $categorized = $data['categorizedResults'] ?? null;

        $this->info("Full section() time: {$elapsedMs} ms");
        $this->info("SQL queries: {$queries}");

        if ($categorized) {
            $this->info('Phrase: ' . ($categorized['phrase']->total() ?? 0));
            $this->info('All: ' . ($categorized['all']->total() ?? 0));
            foreach ($categorized['per_word'] as $word => $page) {
                $this->info("Word [{$word}]: " . $page->total());
            }
            $this->info('Unique total: ' . ($categorized['unique_total'] ?? 0));
        }

        return self::SUCCESS;
    }
}
