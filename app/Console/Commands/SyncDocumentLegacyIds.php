<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDocumentLegacyIds extends Command
{
    protected $signature = 'documents:sync-legacy-ids
        {--legacy-db=mksnow : Source MySQL database name}
        {--dry-run : Show counts without writing}';

    protected $description = 'Map documents.legacy_id from old mksnow table IDs (by section + title)';

    /** @var array<int, string> */
    protected array $sectionTables = [
        1 => 'lawegs',
        2 => 'decisions',
        3 => 'edaryas',
        4 => 'destoryas',
        5 => 'naqds',
    ];

    public function handle(): int
    {
        $legacyDb = (string) $this->option('legacy-db');
        $dryRun = (bool) $this->option('dry-run');

        config([
            'database.connections.legacy' => array_merge(config('database.connections.mysql'), [
                'database' => $legacyDb,
            ]),
        ]);
        DB::purge('legacy');

        $matched = 0;
        $missing = 0;
        $ambiguous = 0;

        foreach ($this->sectionTables as $sectionId => $table) {
            $this->info("Syncing section {$sectionId} from {$table}...");

            $legacyByTitle = [];
            DB::connection('legacy')
                ->table($table)
                ->whereNull('deleted_at')
                ->select('id', 'title_ar')
                ->orderBy('id')
                ->chunk(1000, function ($rows) use (&$legacyByTitle) {
                    foreach ($rows as $row) {
                        $title = trim((string) $row->title_ar);
                        if ($title === '') {
                            continue;
                        }
                        $legacyByTitle[$title][] = (int) $row->id;
                    }
                });

            // Track which legacy ids we assign in this section
            $usedLegacyIds = [];
            $updates = [];

            $documents = DB::table('documents')
                ->where('section_id', $sectionId)
                ->select('id', 'title', 'legacy_id')
                ->orderBy('id')
                ->get();

            foreach ($documents as $doc) {
                $title = trim((string) $doc->title);
                $candidates = $legacyByTitle[$title] ?? [];

                if ($candidates === []) {
                    $missing++;
                    continue;
                }

                $legacyId = null;
                foreach ($candidates as $candidateId) {
                    if (!isset($usedLegacyIds[$candidateId])) {
                        $legacyId = $candidateId;
                        break;
                    }
                }

                if ($legacyId === null) {
                    $ambiguous++;
                    continue;
                }

                $usedLegacyIds[$legacyId] = true;

                if ((int) $doc->legacy_id === $legacyId) {
                    $matched++;
                    continue;
                }

                $updates[$doc->id] = $legacyId;
                $matched++;
            }

            if (!$dryRun && $updates !== []) {
                foreach (array_chunk($updates, 500, true) as $chunk) {
                    $cases = [];
                    $ids = [];
                    foreach ($chunk as $docId => $legacyId) {
                        $docId = (int) $docId;
                        $legacyId = (int) $legacyId;
                        $cases[] = "WHEN {$docId} THEN {$legacyId}";
                        $ids[] = $docId;
                    }
                    $idList = implode(',', $ids);
                    $caseSql = implode(' ', $cases);
                    DB::update("UPDATE documents SET legacy_id = CASE id {$caseSql} END, updated_at = NOW() WHERE id IN ({$idList})");
                }
            }

            $this->line("  section {$sectionId}: pending_updates=" . count($updates));
        }

        $withLegacy = DB::table('documents')->whereNotNull('legacy_id')->count();
        $this->info("matched={$matched} missing={$missing} ambiguous={$ambiguous} with_legacy_id={$withLegacy}");

        return self::SUCCESS;
    }
}
