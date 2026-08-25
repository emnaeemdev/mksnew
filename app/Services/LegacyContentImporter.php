<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentFieldValue;
use App\Models\DocumentFile;
use App\Models\Nashra;
use App\Models\Post;
use App\Models\PostFile;
use Carbon\Carbon;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LegacyContentImporter
{
    protected OutputStyle $output;

    protected string $legacyDb;

    protected ?string $legacyStorageRoot;

    protected bool $dryRun;

    protected bool $fast;

    protected int $userId;

    /** @var array<string, true> */
    protected array $usedSlugs = [];

    protected int $missingFiles = 0;

    /** @var array<int, int> old blog category_id => new category id */
    protected array $blogCategoryMap = [
        7 => 3,  // أوراق بحثية
        13 => 1, // تقارير
        19 => 4, // أدلة قانونية
        20 => 2, // بيانات صحفية
        21 => 0, // مجلة نوافذ (resolved at runtime)
    ];

    /** @var array<int, array<string, int>> section_id => [field_name => field_id] */
    protected array $fieldIdsBySection = [];

    /** @var array<int, array<int, string>> field_id => select options */
    protected array $selectOptions = [];

    public function __construct(protected HtmlSanitizer $htmlSanitizer)
    {
    }

    public function run(OutputStyle $output, array $options): int
    {
        $this->output = $output;
        $this->legacyDb = (string) ($options['legacy_db'] ?? 'mksnow');
        $this->legacyStorageRoot = filled($options['legacy_storage'] ?? null)
            ? rtrim(str_replace('\\', '/', (string) $options['legacy_storage']), '/')
            : null;
        $this->dryRun = (bool) ($options['dry_run'] ?? false);
        $this->fast = (bool) ($options['fast'] ?? true);
        $this->userId = (int) DB::table('users')->orderBy('id')->value('id');

        if ($this->userId <= 0) {
            $this->output->error('No users found in the database. Create an admin user first.');

            return 1;
        }

        $this->configureLegacyConnection();
        $this->loadFieldMaps();

        if ($options['purge'] ?? false) {
            $this->purgeTrialContent();
        }

        $this->ensureNawafezCategory();

        if (! ($options['skip_documents'] ?? false)) {
            $this->importDocuments();
        }

        if (! ($options['skip_posts'] ?? false)) {
            $this->importBlogs();
            $this->importDidYouKnows();
        }

        if (! ($options['skip_nashras'] ?? false)) {
            $this->importNashras();
        }

        if ($this->missingFiles > 0) {
            $this->output->writeln("<comment>{$this->missingFiles} media file(s) were not found on disk. Paths were saved; copy files to storage/app/public or pass --legacy-storage.</comment>");
        }

        if (! $this->dryRun && ! ($options['no_search_rebuild'] ?? false) && ! ($options['skip_documents'] ?? false)) {
            $this->output->info('Rebuilding document search index...');
            $this->output->write('');
            \Artisan::call('documents:rebuild-search-text', ['--chunk' => 200]);
            $this->output->write(Artisan::output());
        }

        $this->output->info('Legacy import finished.');

        return 0;
    }

    protected function configureLegacyConnection(): void
    {
        $default = config('database.connections.mysql');

        config([
            'database.connections.legacy' => array_merge($default, [
                'database' => $this->legacyDb,
            ]),
        ]);

        DB::purge('legacy');
        DB::connection('legacy')->getPdo();
    }

    protected function legacy(): \Illuminate\Database\Connection
    {
        return DB::connection('legacy');
    }

    protected function loadFieldMaps(): void
    {
        $fields = DB::table('document_custom_fields')
            ->select('id', 'section_id', 'name', 'type', 'options')
            ->whereNull('deleted_at')
            ->get();

        foreach ($fields as $field) {
            $this->fieldIdsBySection[(int) $field->section_id][$field->name] = (int) $field->id;

            if (in_array($field->type, ['select', 'radio', 'multiselect'], true) && $field->options) {
                $options = json_decode($field->options, true);
                if (is_array($options)) {
                    $this->selectOptions[(int) $field->id] = array_map(
                        fn ($opt) => is_string($opt) ? $opt : (string) $opt,
                        $options
                    );
                }
            }
        }
    }

    protected function fieldId(int $sectionId, string $name): ?int
    {
        return $this->fieldIdsBySection[$sectionId][$name] ?? null;
    }

    protected function purgeTrialContent(): void
    {
        $this->output->info('Purging trial content (keeping podcasts)...');

        if ($this->dryRun) {
            $this->output->writeln(sprintf(
                'Would delete: %d documents, %d nashras, posts in target categories.',
                Document::withTrashed()->count(),
                Nashra::count(),
                Post::count()
            ));

            return;
        }

        DB::transaction(function () {
            $documentIds = Document::withTrashed()->pluck('id');

            if ($documentIds->isNotEmpty()) {
                DB::table('document_search_tokens')->whereIn('document_id', $documentIds)->delete();
                DB::table('document_files')->whereIn('document_id', $documentIds)->delete();
                DB::table('document_field_values')->whereIn('document_id', $documentIds)->delete();
                DB::table('keywordables')
                    ->where('keywordable_type', Document::class)
                    ->whereIn('keywordable_id', $documentIds)
                    ->delete();
                Document::withTrashed()->forceDelete();
            }

            Nashra::query()->delete();

            $categoryIds = Category::query()
                ->whereIn('slug', ['report', 'relase', 'research-papers', 'legal-evidence', 'did-you-know', 'nawafez'])
                ->pluck('id');

            if ($categoryIds->isNotEmpty()) {
                $postIds = Post::whereIn('category_id', $categoryIds)->pluck('id');

                if ($postIds->isNotEmpty()) {
                    PostFile::whereIn('post_id', $postIds)->delete();
                    DB::table('comments')->whereIn('post_id', $postIds)->delete();
                    DB::table('keywordables')
                        ->where('keywordable_type', Post::class)
                        ->whereIn('keywordable_id', $postIds)
                        ->delete();
                    Post::whereIn('id', $postIds)->delete();
                }
            }
        });

        $this->usedSlugs = [];
        $this->output->info('Trial content purged.');
    }

    protected function ensureNawafezCategory(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'nawafez'],
            [
                'name_ar' => 'مجلة نوافذ',
                'name_en' => 'nawafez',
                'description_ar' => null,
                'description_en' => null,
                'is_active' => true,
                'sort_order' => 5,
                'show_in_menu' => true,
                'show_in_menu_ar' => true,
                'show_in_menu_en' => true,
            ]
        );

        $this->blogCategoryMap[21] = (int) $category->id;
    }

    protected function importDocuments(): void
    {
        $sources = [
            ['table' => 'lawegs', 'section_id' => 1, 'mapper' => 'mapLaweg'],
            ['table' => 'decisions', 'section_id' => 2, 'mapper' => 'mapDecision'],
            ['table' => 'edaryas', 'section_id' => 3, 'mapper' => 'mapEdarya'],
            ['table' => 'destoryas', 'section_id' => 4, 'mapper' => 'mapDestorya'],
            ['table' => 'naqds', 'section_id' => 5, 'mapper' => 'mapNaqd'],
        ];

        foreach ($sources as $source) {
            $this->importDocumentTable($source['table'], (int) $source['section_id'], $source['mapper']);
        }
    }

    protected function importDocumentTable(string $table, int $sectionId, string $mapperMethod): void
    {
        $total = (int) $this->legacy()->table($table)->whereNull('deleted_at')->count();
        $this->output->info("Importing {$total} records from {$table} → document section {$sectionId}...");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $this->legacy()->table($table)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($sectionId, $mapperMethod, $bar) {
                foreach ($rows as $row) {
                    if (! $this->dryRun) {
                        $this->createDocument($sectionId, $row, $mapperMethod);
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->output->newLine();
    }

    protected function createDocument(int $sectionId, object $row, string $mapperMethod): void
    {
        [$content, $excerpt, $fieldValues, $title, $slugBase] = $this->{$mapperMethod}($row, $sectionId);

        $slug = $this->uniqueSlug($slugBase ?: $title, (int) $row->id, Document::class);
        $published = ($row->status ?? '') === 'published';
        $content = $this->prepareHtml($content);

        $document = Document::withoutEvents(function () use ($sectionId, $row, $content, $excerpt, $title, $slug, $published) {
            return Document::create([
                'section_id' => $sectionId,
                'legacy_id' => (int) $row->id,
                'title' => Str::limit($title, 255, ''),
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt ? Str::limit(strip_tags($excerpt), 500, '') : null,
                'featured_image' => $this->migrateMediaPath($row->main_image ?? null),
                'is_published' => $published,
                'is_featured' => false,
                'published_at' => $published ? ($row->created_at ?? now()) : null,
                'views_count' => (int) ($row->total_views ?? 0),
                'sort_order' => 0,
                'user_id' => $this->userId,
            ]);
        });

        $fieldRows = [];
        $now = now();
        foreach ($fieldValues as $fieldId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $fieldRows[] = [
                'document_id' => $document->id,
                'field_id' => $fieldId,
                'value' => is_string($value) ? $value : (string) $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($fieldRows !== []) {
            foreach (array_chunk($fieldRows, 100) as $chunk) {
                DB::table('document_field_values')->insert($chunk);
            }
        }

        if (! empty($row->file)) {
            $path = $this->migrateMediaPath($row->file);
            if ($path) {
                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_path' => $path,
                    'original_name' => basename($path),
                    'display_name' => basename($path),
                    'file_size' => $this->fileSize($path),
                    'mime_type' => $this->guessMimeType($path),
                    'sort_order' => 0,
                    'download_count' => (int) ($row->total_downloads ?? 0),
                ]);
            }
        }
    }

    /** @return array{0:string,1:?string,2:array<int,string>,3:string,4:string} */
    protected function mapLaweg(object $row, int $sectionId): array
    {
        $content = $this->mergeBilingualHtml($row->description_ar ?? '', $row->description_en ?? null);

        return [
            $content,
            $row->brief_ar ?? null,
            $this->fieldValues($sectionId, [
                'law_number' => $row->laweg_number ?? null,
                'law_subject' => $this->mapSelect($this->fieldId($sectionId, 'law_subject'), $row->laweg_subject ?? null),
                'cat_file1' => $this->mapFileClassification($sectionId, $row->file_classification ?? null),
                'law_type' => $this->mapSelect($this->fieldId($sectionId, 'law_type'), $row->laweg_type ?? null, 'ساري'),
                'law_issuer_pub' => $this->mapSelect($this->fieldId($sectionId, 'law_issuer_pub'), $row->laweg_issuer_pub ?? null),
                'cont_law' => $row->description_ar ?? null,
                'law_date' => $this->normalizeDate($row->laweg_release_date ?? null),
                'title' => $row->title_ar ?? null,
            ]),
            (string) ($row->title_ar ?? ''),
            (string) ($row->slug_ar ?? $row->title_ar ?? ''),
        ];
    }

    /** @return array{0:string,1:?string,2:array<int,string>,3:string,4:string} */
    protected function mapDecision(object $row, int $sectionId): array
    {
        $content = $this->mergeBilingualHtml($row->description_ar ?? '', $row->description_en ?? null);

        return [
            $content,
            $row->brief_ar ?? null,
            $this->fieldValues($sectionId, [
                'decisions_issuer_pub' => $this->mapSelect($this->fieldId($sectionId, 'decisions_issuer_pub'), $row->decisions_issuer_pub ?? null),
                'decisions_type' => $this->mapSelect($this->fieldId($sectionId, 'decisions_type'), $row->decisions_type ?? null, 'ساري'),
                'decisions_subject' => $this->mapSelect($this->fieldId($sectionId, 'decisions_subject'), $row->decisions_subject ?? null),
                'cat_file1' => $this->mapFileClassification($sectionId, $row->file_classification ?? null),
                'decisions_number' => $row->decisions_number ?? null,
                'decisions_release_date' => $this->normalizeDate($row->decisions_release_date ?? null),
                '_' => $row->title_ar ?? null,
                'con_dfsf' => $row->description_ar ?? null,
            ]),
            (string) ($row->title_ar ?? ''),
            (string) ($row->slug_ar ?? $row->title_ar ?? ''),
        ];
    }

    /** @return array{0:string,1:?string,2:array<int,string>,3:string,4:string} */
    protected function mapEdarya(object $row, int $sectionId): array
    {
        return [
            (string) ($row->edarya_dec ?? ''),
            null,
            $this->fieldValues($sectionId, [
                'edarya_year' => $row->edarya_year ?? null,
                'edarya_type' => $this->mapSelect($this->fieldId($sectionId, 'edarya_type'), $row->edarya_type ?? null),
                'cat_file1' => $this->mapFileClassification($sectionId, $row->file_classification ?? null),
                'edarya_number' => $row->edarya_number ?? null,
                'edarya_date' => $this->normalizeDate($row->edarya_date ?? null),
            ]),
            (string) ($row->title_ar ?? ''),
            (string) ($row->slug_ar ?? $row->title_ar ?? ''),
        ];
    }

    /** @return array{0:string,1:?string,2:array<int,string>,3:string,4:string} */
    protected function mapDestorya(object $row, int $sectionId): array
    {
        return [
            (string) ($row->destorya_dec ?? ''),
            null,
            $this->fieldValues($sectionId, [
                'destorya_number' => $row->destorya_number ?? null,
                'destorya_date' => $this->normalizeDate($row->destorya_date ?? null),
                'destorya_type' => $this->mapSelect($this->fieldId($sectionId, 'destorya_type'), $row->destorya_type ?? null),
                'cat_file1' => $this->mapFileClassification($sectionId, $row->file_classification ?? null),
                'destorya_year' => $row->destorya_year ?? null,
            ]),
            (string) ($row->title_ar ?? ''),
            (string) ($row->slug_ar ?? $row->title_ar ?? ''),
        ];
    }

    /** @return array{0:string,1:?string,2:array<int,string>,3:string,4:string} */
    protected function mapNaqd(object $row, int $sectionId): array
    {
        return [
            (string) ($row->naqd_dec ?? ''),
            null,
            $this->fieldValues($sectionId, [
                'naqd_number' => $row->naqd_number ?? null,
                'naqd_year' => $row->naqd_year ?? null,
                'naqd_type' => $this->mapSelect($this->fieldId($sectionId, 'naqd_type'), $row->naqd_type ?? null),
                'naqd_date' => $this->normalizeDate($row->naqd_date ?? null),
                'cat_file1' => $this->mapFileClassification($sectionId, $row->file_classification ?? null),
            ]),
            (string) ($row->title_ar ?? ''),
            (string) ($row->slug_ar ?? $row->title_ar ?? ''),
        ];
    }

    /** @param array<string, mixed> $valuesByName */
    protected function fieldValues(int $sectionId, array $valuesByName): array
    {
        $mapped = [];

        foreach ($valuesByName as $name => $value) {
            $fieldId = $this->fieldId($sectionId, $name);
            if ($fieldId === null || $value === null || $value === '') {
                continue;
            }

            $mapped[$fieldId] = $value;
        }

        return $mapped;
    }

    protected function importBlogs(): void
    {
        $total = (int) $this->legacy()->table('blogs')->whereNull('deleted_at')->count();
        $this->output->info("Importing {$total} blogs → posts...");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $this->legacy()->table('blogs')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunk(50, function ($rows) use ($bar) {
                foreach ($rows as $row) {
                    if (! $this->dryRun) {
                        $this->createPostFromBlog($row);
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->output->newLine();
    }

    protected function importDidYouKnows(): void
    {
        if (! Schema::connection('legacy')->hasTable('didyouknows')) {
            return;
        }

        $total = (int) $this->legacy()->table('didyouknows')->whereNull('deleted_at')->count();
        $this->output->info("Importing {$total} didyouknows → posts (هل تعلم)...");

        if ($total === 0) {
            return;
        }

        $didYouKnowCategoryId = Category::where('slug', 'did-you-know')->value('id');
        if (! $didYouKnowCategoryId) {
            $this->output->error('Category did-you-know not found.');

            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $this->legacy()->table('didyouknows')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunk(50, function ($rows) use ($bar, $didYouKnowCategoryId) {
                foreach ($rows as $row) {
                    if (! $this->dryRun) {
                        $this->createPostFromDidYouKnow($row, (int) $didYouKnowCategoryId);
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->output->newLine();
    }

    protected function createPostFromBlog(object $row): void
    {
        $categoryId = $this->blogCategoryMap[(int) ($row->category_id ?? 0)] ?? null;
        if (! $categoryId) {
            return;
        }

        $this->createPostRecord(
            $row,
            $categoryId,
            (string) ($row->slug_ar ?? $row->slug_en ?? $row->title_ar ?? ''),
            (string) ($row->title_ar ?? ''),
            $row->title_en ?? null,
            (string) ($row->description_ar ?? ''),
            $row->description_en ?? null,
            $row->cover ?? null,
            $row->cover_image_en ?? null,
            $row->main_image ?? null,
            $row->thumb_image_en ?? $row->main_image ?? null,
            $row->pdf ?? null,
            $row->pdf_en ?? null,
            (bool) ($row->is_home_slider ?? false),
            $row->post_date ?? null
        );
    }

    protected function createPostFromDidYouKnow(object $row, int $categoryId): void
    {
        $this->createPostRecord(
            $row,
            $categoryId,
            (string) ($row->slug_ar ?? $row->title_ar ?? ''),
            (string) ($row->title_ar ?? ''),
            $row->title_en ?? null,
            (string) ($row->description_ar ?? ''),
            $row->description_en ?? null,
            null,
            null,
            $row->main_image ?? null,
            $row->main_image ?? null,
            $row->file ?? null,
            null,
            false,
            $row->post_date ?? null
        );
    }

    protected function createPostRecord(
        object $row,
        int $categoryId,
        string $slugBase,
        string $titleAr,
        ?string $titleEn,
        string $contentAr,
        ?string $contentEn,
        ?string $backgroundAr,
        ?string $backgroundEn,
        ?string $featuredAr,
        ?string $featuredEn,
        ?string $pdfAr,
        ?string $pdfEn,
        bool $showInSlider,
        mixed $postDate
    ): void {
        $published = ($row->status ?? '') === 'published';
        $slug = $this->uniqueSlug($slugBase ?: $titleAr, (int) $row->id, Post::class);

        $post = Post::withoutEvents(function () use ($categoryId, $row, $slug, $titleAr, $titleEn, $contentAr, $contentEn, $backgroundAr, $backgroundEn, $featuredAr, $featuredEn, $showInSlider, $postDate, $published) {
            return Post::create([
                'category_id' => $categoryId,
                'user_id' => $this->userId,
                'title_ar' => Str::limit($titleAr, 255, ''),
                'title_en' => $titleEn ? Str::limit($titleEn, 255, '') : null,
                'slug' => $slug,
                'content_ar' => $this->prepareHtml($contentAr) ?: '<p></p>',
                'content_en' => filled($contentEn) ? $this->prepareHtml($contentEn) : null,
                'background_image_ar' => $this->migrateMediaPath($backgroundAr),
                'background_image_en' => $this->migrateMediaPath($backgroundEn),
                'featured_image_ar' => $this->migrateMediaPath($featuredAr),
                'featured_image_en' => $this->migrateMediaPath($featuredEn),
                'status' => $published ? 'published' : 'draft',
                'show_in_slider_ar' => $showInSlider,
                'show_in_slider' => $showInSlider,
                'published_at' => $published ? $this->normalizeDateTime($postDate ?? $row->created_at ?? now()) : null,
                'views_count' => (int) ($row->total_views ?? 0),
                'sort_order' => (int) ($row->blog_order_home ?? 0),
            ]);
        });

        $this->attachPostPdf($post->id, $pdfAr, 'ar');
        $this->attachPostPdf($post->id, $pdfEn, 'en');
    }

    protected function attachPostPdf(int $postId, ?string $legacyPath, string $language): void
    {
        if (! filled($legacyPath)) {
            return;
        }

        $path = $this->migrateMediaPath($legacyPath);
        if (! $path) {
            return;
        }

        PostFile::create([
            'post_id' => $postId,
            'file_path' => $path,
            'original_name' => basename($path),
            'display_name' => basename($path),
            'file_size' => (string) $this->fileSize($path),
            'mime_type' => $this->guessMimeType($path),
            'sort_order' => 0,
            'language' => $language,
            'download_count' => 0,
        ]);
    }

    protected function importNashras(): void
    {
        $total = (int) $this->legacy()->table('nashras')->whereNull('deleted_at')->count();
        $this->output->info("Importing {$total} nashras...");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $this->legacy()->table('nashras')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunk(50, function ($rows) use ($bar) {
                foreach ($rows as $row) {
                    if (! $this->dryRun) {
                        $this->createNashra($row);
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->output->newLine();
    }

    protected function createNashra(object $row): void
    {
        $parsed = Nashra::parseGoogleSheetReference($row->url ?? null);

        Nashra::create([
            'title_ar' => Str::limit((string) ($row->title_ar ?? ''), 255, ''),
            'subtitle_ar' => filled($row->sub_title ?? null) ? Str::limit((string) $row->sub_title, 255, '') : null,
            'google_drive_url' => $parsed['url'],
            'google_sheet_id' => $parsed['id'],
            'published_at' => $this->normalizeDateTime($row->post_date ?? $row->created_at ?? now()),
            'content_ar' => filled($row->description_ar ?? null) ? $this->prepareHtml((string) $row->description_ar) : null,
            'featured_image' => $this->migrateMediaPath($row->main_image ?? null),
            'pdf_file' => $this->migrateMediaPath($row->file ?? null),
            'download_count' => (int) ($row->total_downloads ?? 0),
            'status' => ($row->status ?? '') === 'published',
            'sort_order' => 0,
        ]);
    }

    protected function uniqueSlug(string $base, int $legacyId, string $modelClass): string
    {
        if ($this->usedSlugs === [] && $modelClass === Document::class) {
            foreach (Document::pluck('slug') as $existing) {
                $this->usedSlugs[(string) $existing] = true;
            }
        }

        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = 'item-' . $legacyId;
        }

        $candidate = $slug;
        $suffix = 0;

        while (isset($this->usedSlugs[$candidate])) {
            $suffix++;
            $candidate = $slug . '-' . $suffix;
        }

        $this->usedSlugs[$candidate] = true;

        return $candidate;
    }

    protected function mergeBilingualHtml(?string $arabic, ?string $english): string
    {
        $html = trim((string) $arabic);

        if (filled($english) && trim(strip_tags($english)) !== '') {
            $html .= '<hr><div dir="ltr" lang="en">' . trim($english) . '</div>';
        }

        return $html;
    }

    protected function prepareHtml(?string $html): string
    {
        if (! filled($html)) {
            return '';
        }

        if ($this->fast) {
            return trim($html);
        }

        return $this->htmlSanitizer->clean($html);
    }

    protected function cleanHtml(?string $html): string
    {
        return $this->prepareHtml($html);
    }

    public static function normalizeFileClassificationValue(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === 'غير معرف') {
            return null;
        }

        $aliases = [
            'جامعات' => 'الجامعة',
            'التراث والآثار' => 'الآثار',
        ];

        return $aliases[$value] ?? $value;
    }

    protected function mapFileClassification(int $sectionId, ?string $value): ?string
    {
        $normalized = self::normalizeFileClassificationValue($value);

        return $this->mapSelect($this->fieldId($sectionId, 'cat_file1'), $normalized);
    }

    protected function mapSelect(?int $fieldId, ?string $value, ?string $default = null): ?string
    {
        if ($fieldId === null) {
            return null;
        }

        $options = $this->selectOptions[$fieldId] ?? [];
        $value = trim((string) $value);

        if ($value === '' || $value === 'غير معرف') {
            return $default;
        }

        if (in_array($value, $options, true)) {
            return $value;
        }

        $aliases = [
            'جامعات' => 'الجامعة',
            'التراث والآثار' => 'الآثار',
        ];

        if (isset($aliases[$value]) && in_array($aliases[$value], $options, true)) {
            return $aliases[$value];
        }

        foreach ($options as $option) {
            if (str_contains($value, $option) || str_contains($option, $value)) {
                return $option;
            }
        }

        return $default ?? ($options[0] ?? $value);
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function normalizeDateTime(mixed $value): Carbon
    {
        if (! filled($value)) {
            return now();
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return now();
        }
    }

    protected function migrateMediaPath(?string $legacyPath): ?string
    {
        if (! filled($legacyPath)) {
            return null;
        }

        $legacyPath = ltrim(str_replace('\\', '/', $legacyPath), '/');
        $destination = storage_path('app/public/' . $legacyPath);

        if (is_file($destination)) {
            return $legacyPath;
        }

        if ($this->legacyStorageRoot) {
            $candidates = [
                $this->legacyStorageRoot . '/' . $legacyPath,
                $this->legacyStorageRoot . '/public/' . $legacyPath,
                $this->legacyStorageRoot . '/storage/app/public/' . $legacyPath,
                $this->legacyStorageRoot . '/public/storage/' . $legacyPath,
            ];

            foreach ($candidates as $source) {
                if (is_file($source)) {
                    if (! is_dir(dirname($destination))) {
                        mkdir(dirname($destination), 0775, true);
                    }
                    copy($source, $destination);

                    return $legacyPath;
                }
            }
        }

        $this->missingFiles++;

        return $legacyPath;
    }

    protected function fileSize(string $relativePath): int
    {
        $full = storage_path('app/public/' . ltrim($relativePath, '/'));

        return is_file($full) ? (int) filesize($full) : 0;
    }

    protected function guessMimeType(string $relativePath): string
    {
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
