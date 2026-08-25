<?php

namespace App\Console\Commands;

use App\Services\LegacyContentImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDocumentFileClassification extends Command
{
    protected $signature = 'documents:sync-file-classification
        {--legacy-db=mksnow : Source MySQL database name}
        {--dry-run : Show counts without writing}';

    protected $description = 'Backfill «التصنيف وفقًا للملف» (cat_file1) from legacy file_classification';

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

        $fieldIdsBySection = DB::table('document_custom_fields')
            ->where('name', 'cat_file1')
            ->whereNull('deleted_at')
            ->pluck('id', 'section_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($fieldIdsBySection === []) {
            $this->error('No cat_file1 fields found. Run migrations first.');

            return self::FAILURE;
        }

        $optionsByFieldId = DB::table('document_custom_fields')
            ->whereIn('id', array_values($fieldIdsBySection))
            ->get(['id', 'options'])
            ->mapWithKeys(function ($row) {
                $options = json_decode((string) $row->options, true) ?: [];

                return [(int) $row->id => $options];
            })
            ->all();

        $updated = 0;
        $skipped = 0;
        $missingLegacy = 0;

        foreach ($this->sectionTables as $sectionId => $table) {
            $fieldId = $fieldIdsBySection[$sectionId] ?? null;
            if ($fieldId === null) {
                $this->warn("Section {$sectionId}: no cat_file1 field, skipping.");

                continue;
            }

            $options = $optionsByFieldId[$fieldId] ?? [];
            $this->info("Syncing section {$sectionId} from {$table}...");

            $legacyRows = DB::connection('legacy')
                ->table($table)
                ->whereNull('deleted_at')
                ->whereNotNull('file_classification')
                ->where('file_classification', '!=', '')
                ->where('file_classification', '!=', 'غير معرف')
                ->select('id', 'file_classification')
                ->get()
                ->keyBy('id');

            $documents = DB::table('documents')
                ->where('section_id', $sectionId)
                ->whereNotNull('legacy_id')
                ->select('id', 'legacy_id')
                ->get();

            foreach ($documents as $document) {
                $legacyRow = $legacyRows->get((int) $document->legacy_id);
                if ($legacyRow === null) {
                    $missingLegacy++;

                    continue;
                }

                $mapped = $this->mapValue((string) $legacyRow->file_classification, $options);
                if ($mapped === null) {
                    $skipped++;

                    continue;
                }

                if ($dryRun) {
                    $updated++;

                    continue;
                }

                DB::table('document_field_values')->updateOrInsert(
                    [
                        'document_id' => $document->id,
                        'field_id' => $fieldId,
                    ],
                    [
                        'value' => $mapped,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $updated++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[dry-run] Would update' : 'Updated')." {$updated} document(s).");
        $this->line("Skipped (unmapped value): {$skipped}");
        $this->line("Missing legacy row: {$missingLegacy}");

        return self::SUCCESS;
    }

    /** @param list<string> $options */
    protected function mapValue(string $value, array $options): ?string
    {
        $normalized = LegacyContentImporter::normalizeFileClassificationValue($value);
        if ($normalized === null) {
            return null;
        }

        if (in_array($normalized, $options, true)) {
            return $normalized;
        }

        foreach ($options as $option) {
            if (str_contains($normalized, $option) || str_contains($option, $normalized)) {
                return $option;
            }
        }

        return null;
    }
}
