<?php

namespace App\Console\Commands;

use App\Models\Nashra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNashraGoogleUrls extends Command
{
    protected $signature = 'nashras:fix-google-urls
        {--legacy-db=mksnow : Source MySQL database for fallback URLs}
        {--dry-run : Show changes without writing}';

    protected $description = 'Fix duplicated or malformed Google Sheets URLs on nashras';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $legacyDb = (string) $this->option('legacy-db');

        config([
            'database.connections.legacy' => array_merge(config('database.connections.mysql'), [
                'database' => $legacyDb,
            ]),
        ]);
        DB::purge('legacy');

        $legacyUrls = DB::connection('legacy')
            ->table('nashras')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('url', 'id');

        $fixed = 0;
        $unchanged = 0;

        Nashra::query()->orderBy('id')->each(function (Nashra $nashra) use ($legacyUrls, $dryRun, &$fixed, &$unchanged) {
            $source = $nashra->google_drive_url ?: $nashra->google_sheet_id;

            if (! $source && isset($legacyUrls[$nashra->id])) {
                $source = (string) $legacyUrls[$nashra->id];
            }

            $parsed = Nashra::parseGoogleSheetReference($source);

            if ($parsed['url'] === (string) $nashra->google_drive_url
                && $parsed['id'] === $nashra->google_sheet_id) {
                $unchanged++;

                return;
            }

            $this->line("Nashra #{$nashra->id}:");
            $this->line('  was: ' . ($nashra->google_drive_url ?: '[empty]'));
            $this->line('  now: ' . ($parsed['url'] ?: '[empty]'));
            $this->line('  id:  ' . ($parsed['id'] ?: '[empty]'));

            if (! $dryRun) {
                $nashra->google_drive_url = $parsed['url'];
                $nashra->google_sheet_id = $parsed['id'];
                $nashra->saveQuietly();
            }

            $fixed++;
        });

        $this->newLine();
        $this->info(($dryRun ? '[dry-run] Would fix' : 'Fixed') . " {$fixed} nashra(s), {$unchanged} unchanged.");

        return self::SUCCESS;
    }
}
