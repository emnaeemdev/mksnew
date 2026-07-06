<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DocumentSearchService
{
    public function normalizeArabic(string $text): string
    {
        $text = trim(mb_strtolower($text, 'UTF-8'));
        $tashkeel = ["\u{064B}", "\u{064C}", "\u{064D}", "\u{064E}", "\u{064F}", "\u{0650}", "\u{0651}", "\u{0652}", "\u{0640}"];
        $text = str_replace($tashkeel, '', $text);
        $map = [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ؤ' => 'و', 'ئ' => 'ي',
            'ى' => 'ي',
            'ة' => 'ه',
        ];
        $text = strtr($text, $map);
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text ?? '';
    }

    public function tokenizeArabic(string $normalized): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return array_values($parts ?: []);
    }

    public function arabicStopWords(): array
    {
        return [
            'او', 'أو', 'على', 'الى', 'إلى', 'في', 'رقم', 'طعن', 'من', 'عن', 'ما', 'ماذا', 'هل', 'ثم', 'كما', 'بل', 'لكن', 'لم', 'لن', 'لا', 'أن', 'إن', 'اذا', 'إذا', 'قد', 'و', 'يا', 'ذلك', 'هذه', 'هذا', 'هناك', 'هنا', 'مع', 'كل', 'بعد', 'قبل', 'حتى', 'بين', 'أي', 'أى', 'اي', 'أين', 'هي', 'هو', 'هم', 'هن', 'أنا', 'نحن', 'انت', 'أنت', 'انتم', 'أنتم', 'كان', 'كانت', 'يكون', 'تكون', 'يكونون', 'قانون',
        ];
    }

    /**
     * @return array{
     *   normalizedPhrase: string,
     *   tokens: array<int, string>,
     *   tokensForAll: array<int, string>,
     *   tokensPerWord: array<int, string>,
     *   singleTokenExcluded: bool
     * }
     */
    /**
     * @param array<int, string> $tokens
     * @return array<int, string>
     */
    public function uniqueTokensPreserveOrder(array $tokens): array
    {
        $unique = [];
        $seen = [];

        foreach ($tokens as $token) {
            if (!in_array($token, $seen, true)) {
                $seen[] = $token;
                $unique[] = $token;
            }
        }

        return $unique;
    }

    public function parseSearchQuery(string $searchTerm): array
    {
        $normalizedPhrase = $this->normalizeArabic($searchTerm);
        $tokens = $this->tokenizeArabic($normalizedPhrase);
        $stopWords = $this->arabicStopWords();
        $isSingleToken = count($tokens) === 1;
        $singleTokenExcluded = $isSingleToken && (mb_strlen($tokens[0] ?? '') < 4 || in_array($tokens[0] ?? '', $stopWords, true));
        $tokensForAll = $singleTokenExcluded ? [] : $this->uniqueTokensPreserveOrder($tokens);
        $eligiblePerWord = array_values(array_filter($tokens, function ($t) use ($stopWords) {
            return mb_strlen($t) >= 4 && !in_array($t, $stopWords, true);
        }));
        $tokensPerWord = array_slice($this->uniqueTokensPreserveOrder($eligiblePerWord), 0, 4);

        return compact('normalizedPhrase', 'tokens', 'tokensForAll', 'tokensPerWord', 'singleTokenExcluded');
    }

    /**
     * @return array<int, string>
     */
    public function variantsFor(string $token): array
    {
        $vars = [$token];
        if (preg_match('/^ال/u', $token)) {
            $without = mb_substr($token, 2);
            if ($without !== '') {
                $vars[] = $without;
            }
        } else {
            $vars[] = 'ال' . $token;
        }

        return array_values(array_unique(array_filter($vars, fn ($v) => $v !== null && $v !== '')));
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * مطابقة كلمة كاملة عبر عمود الكلمات المفصولة بمسافات (أسرع بكثير من REGEXP).
     */
    protected function wholeWordLike(string $normalizedToken): string
    {
        return '% ' . $this->escapeLike($normalizedToken) . ' %';
    }

    protected function escapeMysqlRegexp(string $value): string
    {
        return preg_replace('/([.\\\\+*?\\[\\]^$(){}=!<>|:\\-])/', '\\\\$1', $value);
    }

    /**
     * @deprecated استُبدل بـ wholeWordLike على عمود search_words
     */
    public function mysqlWordBoundaryRegexp(string $normalizedToken): string
    {
        $escaped = $this->escapeMysqlRegexp($normalizedToken);

        return '(^|[^[:alpha:]])' . $escaped . '([^[:alpha:]]|$)';
    }

    protected function buildArabicCorePattern(string $normalizedToken): string
    {
        $map = [
            'ا' => '[اأإآٱ]',
            'أ' => '[اأإآٱ]',
            'إ' => '[اأإآٱ]',
            'آ' => '[اأإآٱ]',
            'ٱ' => '[اأإآٱ]',
            'ه' => '[هة]',
            'ة' => '[هة]',
            'ي' => '[يىئ]',
            'ى' => '[يىئ]',
            'ؤ' => '[وؤ]',
            'و' => '[وؤ]',
            'ئ' => '[يىئ]',
        ];
        $pat = '';
        foreach (preg_split('//u', $normalizedToken, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $pat .= ($map[$ch] ?? preg_quote($ch, '/')) . '[\x{064B}-\x{0652}\x{0640}]*';
        }

        return $pat;
    }

    public function buildArabicTokenPattern(string $normalizedToken, bool $withAlVariant = true, bool $wholeWord = true): string
    {
        $variants = $withAlVariant ? $this->variantsFor($normalizedToken) : [$normalizedToken];
        $parts = array_map(fn ($v) => $this->buildArabicCorePattern($v), $variants);
        $core = '(?:' . implode('|', $parts) . ')';

        if ($wholeWord) {
            return '/(?<![\p{Arabic}])(' . $core . ')(?![\p{Arabic}])/iu';
        }

        return '/(' . $core . ')/iu';
    }

    /**
     * @return array{before:string,match:string,after:string}|null
     */
    public function findTokenSnippet(string $text, string $normalizedToken, bool $withAlVariant = true, int $contextWords = 8): ?array
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        if (trim($text) === '') {
            return null;
        }

        $pattern = $this->buildArabicTokenPattern($normalizedToken, $withAlVariant, true);
        if (!preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $matchStr = $matches[1][0];
        $bytePos = $matches[1][1];
        $prefix = substr($text, 0, $bytePos);
        $pos = mb_strlen($prefix, 'UTF-8');
        $len = mb_strlen($matchStr, 'UTF-8');

        $beforeText = mb_substr($text, 0, $pos);
        $afterText = mb_substr($text, $pos + $len);
        $beforeTokens = preg_split('/[\s\x{00A0}]+/u', trim($beforeText), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $afterTokens = preg_split('/[\s\x{00A0}]+/u', trim($afterText), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return [
            'before' => implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - $contextWords, 0))),
            'match' => $matchStr,
            'after' => implode(' ', array_slice($afterTokens, 0, $contextWords)),
        ];
    }

    /**
     * @return array{before:string,match:string,after:string}|null
     */
    public function findSnippetInDocument($document, string $normalizedToken, bool $withAlVariant = true, int $contextWords = 8): ?array
    {
        foreach ([$document->content ?? '', $document->excerpt ?? '', $document->title ?? ''] as $source) {
            if (!is_string($source) || trim($source) === '') {
                continue;
            }
            $snippet = $this->findTokenSnippet($source, $normalizedToken, $withAlVariant, $contextWords);
            if ($snippet !== null) {
                return $snippet;
            }
        }

        return null;
    }

    public function highlightTokenInText(string $text, string $normalizedToken, bool $withAlVariant = true): string
    {
        $pattern = $this->buildArabicTokenPattern($normalizedToken, $withAlVariant, true);

        return preg_replace($pattern, '<mark>$1</mark>', $text) ?? $text;
    }

    /**
     * @param array<int, string> $normalizedTokens
     */
    public function highlightTokensInHtml(string $html, array $normalizedTokens, bool $withAlVariant = true): string
    {
        if ($html === '' || $normalizedTokens === []) {
            return $html;
        }

        $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $html;
        }

        foreach ($parts as $i => $part) {
            if ($part === '' || str_starts_with($part, '<')) {
                continue;
            }
            foreach ($normalizedTokens as $token) {
                if ($token === '') {
                    continue;
                }
                $parts[$i] = $this->highlightTokenInText($parts[$i], $token, $withAlVariant);
            }
        }

        return implode('', $parts);
    }

    /**
     * @return array{search_text: string, search_words: string}
     */
    public function buildSearchIndex(Document $document): array
    {
        $searchText = $this->buildSearchText($document);
        $tokens = $this->uniqueTokensPreserveOrder($this->tokenizeArabic($searchText));

        return [
            'search_text' => $searchText,
            'search_words' => $tokens === [] ? '' : ' ' . implode(' ', $tokens) . ' ',
        ];
    }

    public function buildSearchWordsFromText(string $searchText): string
    {
        $tokens = $this->uniqueTokensPreserveOrder($this->tokenizeArabic($searchText));

        return $tokens === [] ? '' : ' ' . implode(' ', $tokens) . ' ';
    }

    public function buildSearchText(Document $document): string
    {
        $parts = [
            $document->title,
            $document->excerpt,
            $document->content,
        ];

        $fieldValues = $document->relationLoaded('plainFieldValues')
            ? $document->plainFieldValues
            : $document->plainFieldValues()->get();

        foreach ($fieldValues as $fieldValue) {
            if (!empty($fieldValue->value)) {
                $parts[] = $fieldValue->value;
            }
        }

        $combined = implode(' ', array_filter(array_map(function ($part) {
            if (!is_string($part) || trim($part) === '') {
                return null;
            }

            return strip_tags(html_entity_decode($part, ENT_QUOTES, 'UTF-8'));
        }, $parts)));

        return $this->normalizeArabic($combined);
    }

    public function rebuildSearchText(Document $document): void
    {
        $index = $this->buildSearchIndex($document);
        $document->search_text = $index['search_text'];
        $document->search_words = $index['search_words'];
        $document->saveQuietly();
    }

    public function applyPhraseMatch(Builder $query, string $normalizedPhrase, bool $withAlVariant = false, ?array $parsed = null): Builder
    {
        if ($normalizedPhrase === '') {
            return $query;
        }

        if ($parsed !== null && count($parsed['tokens'] ?? []) === 1) {
            return $this->applyTokenOrVariants($query, $parsed['tokens'][0], $withAlVariant);
        }

        return $query->where('search_text', 'like', '%' . $normalizedPhrase . '%');
    }

    protected function applyPhraseMatchCondition(Builder $query, array $parsed, bool $withAlVariant): Builder
    {
        if (count($parsed['tokens'] ?? []) === 1) {
            return $this->applyTokenOrVariants($query, $parsed['tokens'][0], $withAlVariant);
        }

        return $query->where('search_text', 'like', '%' . $parsed['normalizedPhrase'] . '%');
    }

    protected function sqlPhraseMatch(string $table, array $parsed, bool $withAlVariant, array &$bindings): string
    {
        if (count($parsed['tokens'] ?? []) === 1) {
            return $this->sqlWordVariantsMatch($table, $parsed['tokens'][0], $withAlVariant, $bindings);
        }

        $bindings[] = '%' . $parsed['normalizedPhrase'] . '%';

        return $table . '.search_text LIKE ?';
    }

    public function applyAllTokensMatch(Builder $query, array $tokens, bool $withAlVariant = false): Builder
    {
        foreach ($tokens as $token) {
            $query->where(function (Builder $q) use ($token, $withAlVariant) {
                $this->applyTokenOrVariants($q, $token, $withAlVariant);
            });
        }

        return $query;
    }

    public function applyTokenOrVariants(Builder $query, string $token, bool $withAlVariant = false): Builder
    {
        $variants = $withAlVariant ? $this->variantsFor($token) : [$token];
        $column = $query->getModel()->getTable() . '.search_words';

        return $query->where(function (Builder $q) use ($variants, $column) {
            foreach ($variants as $variant) {
                $q->orWhere($column, 'like', $this->wholeWordLike($variant));
            }
        });
    }

    public function applySimpleSearchFilter($query, string $searchTerm)
    {
        return $this->applySearchResultsFilter($query, $searchTerm, false);
    }

    /**
     * يقيّد الاستعلام بنفس مجموعة الوثائق الظاهرة في نتائج البحث المصنّفة.
     */
    public function applySearchResultsFilter($query, string $searchTerm, bool $withAlVariant = false)
    {
        $parsed = $this->parseSearchQuery($searchTerm);
        if ($parsed['normalizedPhrase'] === '') {
            return $query;
        }

        if ($parsed['singleTokenExcluded']) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($parsed, $withAlVariant) {
            $q->where(function (Builder $inner) use ($parsed, $withAlVariant) {
                $this->applyPhraseMatchCondition($inner, $parsed, $withAlVariant);
            });

            if (!empty($parsed['tokensForAll'])) {
                $q->orWhere(function (Builder $inner) use ($parsed, $withAlVariant) {
                    $this->applyAllTokensMatch($inner, $parsed['tokensForAll'], $withAlVariant);
                    if (count($parsed['tokens']) > 1) {
                        $inner->where('search_text', 'not like', '%' . $parsed['normalizedPhrase'] . '%');
                    } else {
                        $inner->whereRaw('1 = 0');
                    }
                });
            }

            foreach ($parsed['tokensPerWord'] as $word) {
                $q->orWhere(function (Builder $inner) use ($word, $withAlVariant) {
                    $this->applyTokenOrVariants($inner, $word, $withAlVariant);
                });
            }
        });
    }

    protected function sqlWordVariantsMatch(string $table, string $token, bool $withAlVariant, array &$bindings): string
    {
        $variants = $withAlVariant ? $this->variantsFor($token) : [$token];
        $variantParts = [];
        foreach ($variants as $variant) {
            $variantParts[] = $table . '.search_words LIKE ?';
            $bindings[] = $this->wholeWordLike($variant);
        }

        return '(' . implode(' OR ', $variantParts) . ')';
    }

    /**
     * @return array{
     *   phrase: int,
     *   all: int,
     *   per_word: array<int, int>,
     *   unique: int
     * }
     */
    protected function uniqueMatchSqlParts(string $table, array $parsed, bool $withAlVariant, array &$bindings): array
    {
        $parts = [$this->sqlPhraseMatch($table, $parsed, $withAlVariant, $bindings)];

        if (count($parsed['tokens']) > 1 && !empty($parsed['tokensForAll'])) {
            $andParts = [];
            foreach ($parsed['tokensForAll'] as $token) {
                $andParts[] = $this->sqlWordVariantsMatch($table, $token, $withAlVariant, $bindings);
            }
            $parts[] = '(' . implode(' AND ', $andParts) . ')';
        }

        if (count($parsed['tokens']) > 1) {
            foreach ($parsed['tokensPerWord'] as $word) {
                $parts[] = $this->sqlWordVariantsMatch($table, $word, $withAlVariant, $bindings);
            }
        }

        return $parts;
    }

    public function computeTabCounts(Builder $baseQuery, array $parsed, bool $withAlVariant = false): array
    {
        if ($parsed['singleTokenExcluded']) {
            $perWordCounts = [];
            foreach ($parsed['tokensPerWord'] as $idx => $word) {
                $perWordCounts[$idx] = 0;
            }

            return ['phrase' => 0, 'all' => 0, 'per_word' => $perWordCounts, 'unique' => 0];
        }

        $table = $baseQuery->getModel()->getTable();
        $subAlias = 'search_docs';

        $innerQuery = (clone $baseQuery)->select([
            $table . '.search_text',
            $table . '.search_words',
        ]);

        $selects = [];
        $bindings = [];

        $phraseCondition = $this->sqlPhraseMatch($subAlias, $parsed, $withAlVariant, $bindings);
        $selects[] = 'SUM(CASE WHEN ' . $phraseCondition . ' THEN 1 ELSE 0 END) AS phrase_count';

        if (!empty($parsed['tokensForAll']) && count($parsed['tokens']) > 1) {
            $andParts = [];
            foreach ($parsed['tokensForAll'] as $token) {
                $andParts[] = $this->sqlWordVariantsMatch($subAlias, $token, $withAlVariant, $bindings);
            }
            $allCondition = '(' . implode(' AND ', $andParts) . ')';
            $selects[] = 'SUM(CASE WHEN ' . $allCondition . ' AND ' . $subAlias . '.search_text NOT LIKE ? THEN 1 ELSE 0 END) AS all_count';
            $bindings[] = '%' . $parsed['normalizedPhrase'] . '%';
        } else {
            $selects[] = '0 AS all_count';
        }

        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            $wordCondition = $this->sqlWordVariantsMatch($subAlias, $word, $withAlVariant, $bindings);
            $selects[] = 'SUM(CASE WHEN ' . $wordCondition . ' THEN 1 ELSE 0 END) AS word_' . $idx;
        }

        $uniqueParts = $this->uniqueMatchSqlParts($subAlias, $parsed, $withAlVariant, $bindings);
        $selects[] = 'SUM(CASE WHEN (' . implode(' OR ', $uniqueParts) . ') THEN 1 ELSE 0 END) AS unique_count';

        $row = DB::query()
            ->fromSub($innerQuery, $subAlias)
            ->selectRaw(implode(', ', $selects), $bindings)
            ->first();

        $perWordCounts = [];
        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            $alias = 'word_' . $idx;
            $perWordCounts[$idx] = (int) ($row->{$alias} ?? 0);
        }

        return [
            'phrase' => (int) ($row->phrase_count ?? 0),
            'all' => (int) ($row->all_count ?? 0),
            'per_word' => $perWordCounts,
            'unique' => (int) ($row->unique_count ?? 0),
        ];
    }

    protected function emptyPaginator(Request $request, string $pageName, int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage, (int) $request->input($pageName, 1), [
            'path' => $request->url(),
            'pageName' => $pageName,
        ]);
    }

    protected function paginateTab(Builder $query, callable $applySort, Request $request, string $pageName, int $perPage, int $total): LengthAwarePaginator
    {
        if ($total === 0) {
            return $this->emptyPaginator($request, $pageName, $perPage);
        }

        $page = max(1, (int) $request->input($pageName, 1));

        $items = $applySort(clone $query)
            ->forPage($page, $perPage)
            ->get();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => $pageName]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function searchCategorized(
        Builder $baseQuery,
        string $searchTerm,
        Request $request,
        callable $applySort,
        bool $withAlVariant = false,
        bool $includeAnyTab = false
    ): array {
        $parsed = $this->parseSearchQuery($searchTerm);
        $perPage = (int) $request->get('per_page', 6);
        $counts = $this->computeTabCounts($baseQuery, $parsed, $withAlVariant);
        $activeTab = $this->resolveActiveTab($request, $parsed, $counts);

        $eager = ['section', 'user', 'plainFieldValues.field'];
        $baseQuery->with($eager)->select([
            'documents.id',
            'documents.section_id',
            'documents.title',
            'documents.slug',
            'documents.excerpt',
            'documents.content',
            'documents.is_featured',
            'documents.views_count',
            'documents.published_at',
            'documents.user_id',
            'documents.search_text',
        ]);

        if ($parsed['singleTokenExcluded']) {
            $perWord = [];
            foreach ($parsed['tokensPerWord'] as $idx => $word) {
                $perWord[] = [
                    'word' => $word,
                    'page' => $this->emptyPaginator($request, 'page_w' . $idx, $perPage),
                ];
            }

            return [
                'phrase' => $this->emptyPaginator($request, $this->pageNameForTab('phrase', 0, $includeAnyTab), $perPage),
                'all' => $this->emptyPaginator($request, $this->pageNameForTab('all', 0, $includeAnyTab), $perPage),
                'any' => $includeAnyTab ? $this->emptyPaginator($request, 'page_any', $perPage) : null,
                'per_word' => $perWord,
                'tokens' => $parsed['tokensPerWord'],
                'raw' => $searchTerm,
                'unique_total' => 0,
                'tab_counts' => $counts,
                'active_tab' => $activeTab,
            ];
        }

        $phraseQuery = $this->applyPhraseMatch(clone $baseQuery, $parsed['normalizedPhrase'], $withAlVariant, $parsed);
        $allQuery = !empty($parsed['tokensForAll']) && count($parsed['tokens']) > 1
            ? $this->applyAllTokensMatch(clone $baseQuery, $parsed['tokensForAll'], $withAlVariant)
                ->where('search_text', 'not like', '%' . $parsed['normalizedPhrase'] . '%')
            : null;

        $perWordQueries = [];
        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            $perWordQueries[$idx] = tap(clone $baseQuery, function (Builder $q) use ($word, $withAlVariant) {
                $this->applyTokenOrVariants($q, $word, $withAlVariant);
            });
        }

        $phrasePageName = $this->pageNameForTab('phrase', 0, $includeAnyTab);
        $allPageName = $this->pageNameForTab('all', 0, $includeAnyTab);

        $phrasePaginated = $activeTab === 'phrase'
            ? $this->paginateTab($phraseQuery, $applySort, $request, $phrasePageName, $perPage, $counts['phrase'])
                ->appends($request->except($phrasePageName))
            : $this->makePaginatorFromCount($request, $phrasePageName, $perPage, $counts['phrase']);

        $allPaginated = $activeTab === 'all' && $allQuery
            ? $this->paginateTab($allQuery, $applySort, $request, $allPageName, $perPage, $counts['all'])
                ->appends($request->except($allPageName))
            : $this->makePaginatorFromCount($request, $allPageName, $perPage, $counts['all']);

        $perWord = [];
        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            $tabKey = 'word-' . $idx;
            $pageName = 'page_w' . $idx;
            $wordTotal = $counts['per_word'][$idx] ?? 0;
            $perWord[] = [
                'word' => $word,
                'page' => $activeTab === $tabKey
                    ? $this->paginateTab($perWordQueries[$idx], $applySort, $request, $pageName, $perPage, $wordTotal)
                        ->appends($request->except($pageName))
                    : $this->makePaginatorFromCount($request, $pageName, $perPage, $wordTotal),
            ];
        }

        $anyPaginated = null;
        if ($includeAnyTab && !empty($parsed['tokensForAll'])) {
            $anyQuery = tap(clone $baseQuery, function (Builder $q) use ($parsed, $withAlVariant) {
                $q->where(function (Builder $outer) use ($parsed, $withAlVariant) {
                    foreach ($parsed['tokensForAll'] as $token) {
                        $outer->orWhere(function (Builder $inner) use ($token, $withAlVariant) {
                            $this->applyTokenOrVariants($inner, $token, $withAlVariant);
                        });
                    }
                })->where('search_text', 'not like', '%' . $parsed['normalizedPhrase'] . '%');
            });
            $anyTotal = (clone $anyQuery)->count();
            $anyPaginated = $activeTab === 'any'
                ? $this->paginateTab($anyQuery, $applySort, $request, 'page_any', $perPage, $anyTotal)
                    ->appends($request->except('page_any'))
                : $this->makePaginatorFromCount($request, 'page_any', $perPage, $anyTotal);
        }

        $uniqueTotal = $counts['unique'] ?? 0;

        return [
            'phrase' => $phrasePaginated,
            'all' => $allPaginated,
            'any' => $anyPaginated,
            'per_word' => $perWord,
            'tokens' => $parsed['tokensPerWord'],
            'raw' => $searchTerm,
            'unique_total' => $uniqueTotal,
            'tab_counts' => $counts,
            'active_tab' => $activeTab,
        ];
    }

    protected function pageNameForTab(string $tab, int $wordIndex, bool $useLegacyPageNames): string
    {
        if ($useLegacyPageNames) {
            return match ($tab) {
                'phrase' => 'page_p',
                'all' => 'page_a',
                default => 'page_w' . $wordIndex,
            };
        }

        return match ($tab) {
            'phrase' => 'page_phrase',
            'all' => 'page_all',
            default => 'page_w' . $wordIndex,
        };
    }

    protected function makePaginatorFromCount(Request $request, string $pageName, int $perPage, int $total): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], $total, $perPage, (int) $request->input($pageName, 1), [
            'path' => $request->url(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * @param array{phrase:int,all:int,per_word:array<string,int>} $counts
     */
    protected function computeUniqueTotal(array $counts): int
    {
        return $counts['phrase'] + $counts['all'] + array_sum($counts['per_word']);
    }

    public function computeUniqueTotalFromQuery(Builder $baseQuery, array $parsed, bool $withAlVariant = false): int
    {
        if ($parsed['singleTokenExcluded']) {
            return 0;
        }

        $query = clone $baseQuery;

        return $query->where(function (Builder $q) use ($parsed, $withAlVariant) {
            $q->where(function (Builder $inner) use ($parsed, $withAlVariant) {
                $this->applyPhraseMatchCondition($inner, $parsed, $withAlVariant);
            });

            if (!empty($parsed['tokensForAll'])) {
                $q->orWhere(function (Builder $inner) use ($parsed, $withAlVariant) {
                    $this->applyAllTokensMatch($inner, $parsed['tokensForAll'], $withAlVariant);
                    if (count($parsed['tokens']) > 1) {
                        $inner->where('search_text', 'not like', '%' . $parsed['normalizedPhrase'] . '%');
                    } else {
                        $inner->whereRaw('1 = 0');
                    }
                });
            }

            foreach ($parsed['tokensPerWord'] as $word) {
                $q->orWhere(function (Builder $inner) use ($word, $withAlVariant) {
                    $this->applyTokenOrVariants($inner, $word, $withAlVariant);
                });
            }
        })->count();
    }

    protected function resolveActiveTab(Request $request, array $parsed, array $counts): string
    {
        $tab = (string) $request->get('tab', '');
        if ($tab !== '') {
            return $tab;
        }

        if (($counts['phrase'] ?? 0) > 0) {
            return 'phrase';
        }
        if (($counts['all'] ?? 0) > 0) {
            return 'all';
        }
        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            if (($counts['per_word'][$idx] ?? 0) > 0) {
                return 'word-' . $idx;
            }
        }

        return 'phrase';
    }

    /**
     * @return array{before:string,match:string,after:string}|null
     */
    public function extractSnippet(string $haystack, string $needle, int $contextWords = 8): ?array
    {
        $normalizedHaystack = $this->normalizeArabic(strip_tags(html_entity_decode($haystack, ENT_QUOTES, 'UTF-8')));
        $normalizedNeedle = $this->normalizeArabic($needle);
        if ($normalizedNeedle === '') {
            return null;
        }

        $pos = mb_stripos($normalizedHaystack, $normalizedNeedle);
        if ($pos === false) {
            return null;
        }

        $len = mb_strlen($normalizedNeedle);
        $beforeText = mb_substr($normalizedHaystack, 0, $pos);
        $afterText = mb_substr($normalizedHaystack, $pos + $len);
        $beforeTokens = preg_split('/[\s\x{00A0}]+/u', trim($beforeText), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $afterTokens = preg_split('/[\s\x{00A0}]+/u', trim($afterText), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return [
            'before' => implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - $contextWords, 0))),
            'match' => mb_substr($normalizedHaystack, $pos, $len),
            'after' => implode(' ', array_slice($afterTokens, 0, $contextWords)),
        ];
    }
}
