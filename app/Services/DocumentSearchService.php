<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentSearchService
{
    /** @var array<string, array{counts: array, scatteredPlan: array}> */
    protected array $searchScopeCache = [];

    /** @var array<string, array<int, int>> */
    protected array $rankedDocumentIdsCache = [];

    /** @var bool|null */
    protected static ?bool $tokenIndexReady = null;

    protected function useTokenIndex(): bool
    {
        if (!config('document_search.use_token_index', true)) {
            return false;
        }

        return $this->tokenIndexReady();
    }

    protected function tokenIndexReady(): bool
    {
        if (self::$tokenIndexReady !== null) {
            return self::$tokenIndexReady;
        }

        try {
            self::$tokenIndexReady = Schema::hasTable('document_search_tokens')
                && DB::table('document_search_tokens')->limit(1)->exists();
        } catch (\Throwable) {
            self::$tokenIndexReady = false;
        }

        return self::$tokenIndexReady;
    }

    /**
     * يعيد بناء search_text و search_words وفهرس الكلمات لوثيقة واحدة.
     */
    public function rebuildDocumentIndex(Document $document): void
    {
        $document->loadMissing('plainFieldValues');
        $index = $this->buildSearchIndex($document);
        $document->search_text = $index['search_text'];
        $document->search_words = $index['search_words'];
        $document->saveQuietly();
        $this->syncSearchTokensForDocument((int) $document->id, $index['search_words']);
    }

    /**
     * مزامنة جدول document_search_tokens من عمود search_words المفصول بمسافات.
     */
    public function syncSearchTokensForDocument(int $documentId, string $searchWords): void
    {
        if (!Schema::hasTable('document_search_tokens')) {
            return;
        }

        $tokens = array_values(array_unique(array_filter(
            explode(' ', trim($searchWords)),
            fn ($t) => is_string($t) && $t !== '' && mb_strlen($t) >= 2
        )));
        $tokens = array_keys(array_fill_keys($tokens, true));

        DB::table('document_search_tokens')->where('document_id', $documentId)->delete();

        if ($tokens === []) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($tokens as $token) {
            $rows[] = [
                'document_id' => $documentId,
                'token' => $token,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 400) as $chunk) {
            DB::table('document_search_tokens')->insertOrIgnore($chunk);
        }
    }

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
        $text = $this->normalizeDigitsToWestern($text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text ?? '';
    }

    protected function normalizeDigitsToWestern(string $text): string
    {
        static $maps = null;
        if ($maps === null) {
            $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $maps = [array_combine($eastern, $western), array_combine($persian, $western)];
        }

        foreach ($maps as $map) {
            $text = strtr($text, $map);
        }

        return $text;
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
        $rawNormalized = $this->normalizeArabic($searchTerm);
        $rawTokens = $this->tokenizeArabic($rawNormalized);
        // إزالة التكرار المتتالي: "حرية الصحافة الصحافة" → "حرية الصحافة"
        $tokens = $this->collapseConsecutiveDuplicates($rawTokens);
        $normalizedPhrase = implode(' ', $tokens);
        $stopWords = $this->arabicStopWords();
        $isSingleToken = count($tokens) === 1;
        $singleTokenExcluded = $isSingleToken && (mb_strlen($tokens[0] ?? '') < 4 || in_array($tokens[0] ?? '', $stopWords, true));
        $tokensForAll = $singleTokenExcluded ? [] : $this->uniqueTokensPreserveOrder($tokens);
        $eligiblePerWord = array_values(array_filter($tokens, function ($t) use ($stopWords) {
            return mb_strlen($t) >= 4 && !in_array($t, $stopWords, true);
        }));
        $tokensPerWord = array_slice($this->uniqueTokensPreserveOrder($eligiblePerWord), 0, 5);

        return compact('normalizedPhrase', 'tokens', 'tokensForAll', 'tokensPerWord', 'singleTokenExcluded');
    }

    /**
     * @param array<int, string> $tokens
     * @return array<int, string>
     */
    protected function collapseConsecutiveDuplicates(array $tokens): array
    {
        $collapsed = [];
        foreach ($tokens as $token) {
            if ($collapsed === [] || end($collapsed) !== $token) {
                $collapsed[] = $token;
            }
        }

        return $collapsed;
    }

    /**
     * يولّد صيغ الجملة مع/بدون «ال» لكل كلمة (حد أقصى عملي لسرعة البحث).
     *
     * @param array<int, string> $tokens
     * @return array<int, string>
     */
    public function phraseVariants(array $tokens, bool $withAlVariant = true): array
    {
        $tokens = array_values(array_filter($tokens, fn ($t) => is_string($t) && $t !== ''));
        if ($tokens === []) {
            return [];
        }

        if (!$withAlVariant) {
            return [implode(' ', $tokens)];
        }

        $variantSets = [];
        foreach ($tokens as $token) {
            $variantSets[] = $this->variantsFor($token);
        }

        $phrases = [''];
        foreach ($variantSets as $variants) {
            $next = [];
            foreach ($phrases as $prefix) {
                foreach ($variants as $variant) {
                    $next[] = $prefix === '' ? $variant : $prefix . ' ' . $variant;
                }
            }
            $phrases = $next;
            // حماية من الانفجار: 5 كلمات × صيغتين = 32 كحد أقصى طبيعي
            if (count($phrases) > 32) {
                $phrases = array_slice($phrases, 0, 32);
            }
        }

        return array_values(array_unique($phrases));
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
        $tokens = preg_split('/\s+/u', trim($normalizedToken), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($tokens) > 1) {
            return $this->buildArabicPhrasePattern($tokens, $withAlVariant);
        }

        $variants = $withAlVariant ? $this->variantsFor($normalizedToken) : [$normalizedToken];
        $parts = array_map(fn ($v) => $this->buildArabicCorePattern($v), $variants);
        $core = '(?:' . implode('|', $parts) . ')';

        if ($wholeWord) {
            return '/(?<![\p{Arabic}])(' . $core . ')(?![\p{Arabic}])/iu';
        }

        return '/(' . $core . ')/iu';
    }

    /**
     * نمط جملة متعددة الكلمات مع صيغ «ال» لكل كلمة على حدة.
     *
     * @param array<int, string> $tokens
     */
    public function buildArabicPhrasePattern(array $tokens, bool $withAlVariant = true): string
    {
        $wordPatterns = [];
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            $variants = $withAlVariant ? $this->variantsFor($token) : [$token];
            $parts = array_map(fn ($v) => $this->buildArabicCorePattern($v), $variants);
            $wordPatterns[] = '(?:' . implode('|', $parts) . ')';
        }

        if ($wordPatterns === []) {
            return '/(?!)/u';
        }

        $core = implode('[\s\x{00A0}]+', $wordPatterns);

        return '/(?<![\p{Arabic}])(' . $core . ')(?![\p{Arabic}])/iu';
    }

    /**
     * @return array{before:string,match:string,after:string}|null
     */
    public function findTokenSnippet(string $text, string $normalizedToken, bool $withAlVariant = true, int $contextWords = 8, array $hintTokens = []): ?array
    {
        $text = $this->normalizePreviewText($text, $hintTokens);
        if ($text === '') {
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
            'before' => $this->collapsePreviewSpaces(implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - $contextWords, 0)))),
            'match' => $matchStr,
            'after' => $this->collapsePreviewSpaces(implode(' ', array_slice($afterTokens, 0, $contextWords))),
        ];
    }

    public function plainTextForPreview(?string $source, int $limit = 220, array $hintTokens = []): string
    {
        if ($source === null || trim($source) === '') {
            return '';
        }

        $text = $this->normalizePreviewText($source, $hintTokens);

        return \Illuminate\Support\Str::limit($text, $limit);
    }

    protected function normalizePreviewText(string $source, array $hintTokens = []): string
    {
        $text = $this->htmlToPlainText($source);
        $text = str_replace(["\xc2\xa0", "\u{00A0}", '&nbsp;'], ' ', $text);
        $text = preg_replace('/&nbsp;/iu', ' ', $text) ?? $text;
        $text = $this->separateArabicGluedWords($text, $hintTokens);

        return $this->collapsePreviewSpaces($text);
    }

    /**
     * يفصل كلمات عربية ملتصقة في النص المعروض (شائع في الوثائق القانونية الممسوحة).
     *
     * @param array<int, string> $hintTokens
     */
    protected function separateArabicGluedWords(string $text, array $hintTokens = []): string
    {
        if ($text === '') {
            return '';
        }

        foreach ($hintTokens as $token) {
            if (!is_string($token) || trim($token) === '') {
                continue;
            }

            $normalized = $this->normalizeArabic($token);
            if ($normalized === '') {
                continue;
            }

            $core = $this->buildArabicCorePattern($normalized);
            $lookbehind = preg_match('/^ال/u', $normalized)
                ? '(?<=[\p{Arabic}])'
                : '(?<=[\p{Arabic}])(?<!ل)';
            $text = preg_replace('/' . $lookbehind . '(' . $core . ')/u', ' $1', $text) ?? $text;
        }

        $text = preg_replace('/(?<=[\p{Arabic}])(ال)(?=[\p{Arabic}]{3,})/u', ' $1', $text) ?? $text;

        $prefixes = [
            'مع', 'في', 'من', 'إلى', 'الى', 'على', 'عن', 'بعد', 'قبل', 'ضد', 'نحو', 'عند', 'حول', 'لدى',
            'حتى', 'منذ', 'خلال', 'ثم', 'بل', 'غير', 'كل', 'بعض', 'حيث', 'لئن', 'لان', 'لأن', 'اذا', 'إذا', 'كأن',
        ];
        foreach ($prefixes as $prefix) {
            $text = preg_replace(
                '/(?:^|(?<=[\p{Arabic}])|(?<=[.،؛:!?\s"\(]))(' . preg_quote($prefix, '/') . ')(?=[\p{Arabic}])/u',
                ' $1',
                $text
            ) ?? $text;
        }

        $text = preg_replace('/([.،؛:!?\)"»])(?=[\p{Arabic}])/u', '$1 ', $text) ?? $text;
        $text = preg_replace('/(?<=[\p{Arabic}])(["«(])/u', ' $1', $text) ?? $text;

        return $text;
    }

    /**
     * تحويل HTML إلى نص عرض مع الحفاظ على فواصل الكلمات (بدون تطبيع بحث يغيّر الحروف).
     */
    protected function htmlToPlainText(string $source): string
    {
        if ($source === '') {
            return '';
        }

        if (str_contains($source, '<')) {
            $source = preg_replace('/<(br|hr)\b[^>]*\/?>/iu', ' ', $source) ?? $source;
            $source = preg_replace('/<\/(p|div|li|h[1-6]|td|th|tr|blockquote|section|article|ul|ol)>/iu', ' ', $source) ?? $source;
            $source = preg_replace('/<[^>]+>/u', ' ', $source) ?? $source;
        }

        return html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * أفضل مصدر نص للعرض/المقتطفات (النص الأصلي وليس search_text المُطبَّع).
     */
    public function documentDisplaySource($document): string
    {
        if (!empty($document->id) && !$this->documentAttributeLoaded($document, 'content')) {
            $content = \App\Models\Document::query()->whereKey($document->id)->value('content');
            if (is_string($content) && trim(strip_tags($content)) !== '') {
                return $content;
            }
        }

        foreach ([$document->content ?? '', $document->excerpt ?? '', $document->title ?? ''] as $source) {
            if (is_string($source) && trim(strip_tags($source)) !== '') {
                return $source;
            }
        }

        return '';
    }

    protected function documentAttributeLoaded($document, string $attribute): bool
    {
        if (!is_object($document) || !method_exists($document, 'getAttributes')) {
            return false;
        }

        $attributes = $document->getAttributes();

        return array_key_exists($attribute, $attributes) && $attributes[$attribute] !== null && $attributes[$attribute] !== '';
    }

    /**
     * @param array{before?:string,match?:string,after?:string} $snippet
     */
    public function renderSnippetHtml(array $snippet): string
    {
        $before = (string) ($snippet['before'] ?? '');
        $match = (string) ($snippet['match'] ?? '');
        $after = (string) ($snippet['after'] ?? '');

        $html = e($before);
        if ($match !== '') {
            if ($html !== '' && !preg_match('/\s$/u', $before)) {
                $html .= ' ';
            }
            $html .= '<mark>' . e($match) . '</mark>';
        }
        if ($after !== '') {
            if ($match !== '' && !preg_match('/^\s/u', $after)) {
                $html .= ' ';
            } elseif ($match === '' && $html !== '' && !preg_match('/\s$/u', $before)) {
                $html .= ' ';
            }
            $html .= e($after);
        }

        return $html;
    }

    protected function collapsePreviewSpaces(string $text): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        return $text;
    }

    /**
     * @return array{before:string,match:string,after:string}|null
     */
    public function findSnippetInDocument($document, string $normalizedToken, bool $withAlVariant = true, int $contextWords = 8, array $hintTokens = []): ?array
    {
        $source = $this->documentDisplaySource($document);
        if ($source === '') {
            return null;
        }

        return $this->findTokenSnippet($source, $normalizedToken, $withAlVariant, $contextWords, $hintTokens);
    }

    /**
     * مقتطف لجملة بحث متعددة الكلمات (مطابقة تامة).
     *
     * @param array<int, string> $tokens
     * @return array{before:string,match:string,after:string}|null
     */
    public function findPhraseSnippetInDocument($document, array $tokens, bool $withAlVariant = true, int $contextWords = 8, array $hintTokens = []): ?array
    {
        $tokens = array_values(array_filter($tokens, fn ($t) => is_string($t) && $t !== ''));
        if ($tokens === []) {
            return null;
        }

        $source = $this->documentDisplaySource($document);
        if ($source === '') {
            return null;
        }

        $hints = $hintTokens !== [] ? $hintTokens : $tokens;
        $text = $this->normalizePreviewText($source, $hints);
        if ($text === '') {
            return null;
        }

        $pattern = $this->buildArabicPhrasePattern($tokens, $withAlVariant);
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
            'before' => $this->collapsePreviewSpaces(implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - $contextWords, 0)))),
            'match' => $matchStr,
            'after' => $this->collapsePreviewSpaces(implode(' ', array_slice($afterTokens, 0, $contextWords))),
        ];
    }

    /**
     * كلمات التظليل المناسبة لنوع التطابق.
     *
     * @param array<int, string> $entryTokens
     * @return array<int, string>
     */
    public function highlightTokensForSearch(string $rawSearch, string $matchType = 'any', array $entryTokens = []): array
    {
        $parsed = $this->parseSearchQuery($rawSearch);

        if ($matchType === 'exact') {
            return $parsed['tokensForAll'] !== [] ? $parsed['tokensForAll'] : $parsed['tokens'];
        }

        if ($entryTokens !== []) {
            return array_values(array_unique(array_filter($entryTokens)));
        }

        return $parsed['tokensPerWord'] !== [] ? $parsed['tokensPerWord'] : $parsed['tokens'];
    }

    /**
     * أفضل مقتطف/مقتطفات للعرض مع تسلسل محاولات (جملة كاملة ← جملة مختصرة ← كلمات منفصلة).
     *
     * @param array<int, string> $entryTokens
     * @return array<int, array{before:string,match:string,after:string}>
     */
    public function findDocumentSearchSnippets($document, string $rawSearch, string $matchType = 'any', array $entryTokens = []): array
    {
        $rawSearch = trim($rawSearch);
        if ($rawSearch === '') {
            return [];
        }

        $parsed = $this->parseSearchQuery($rawSearch);
        $hints = array_values(array_unique(array_merge($parsed['tokens'], $parsed['tokensPerWord'], $entryTokens)));

        $phraseCandidates = [];
        if ($matchType === 'exact' && count($parsed['tokens']) > 1) {
            $phraseCandidates[] = $parsed['tokens'];
        }
        if (count($parsed['tokens']) > 1) {
            $phraseCandidates[] = $parsed['tokens'];
        }
        if (count($parsed['tokensPerWord']) > 1) {
            $phraseCandidates[] = $parsed['tokensPerWord'];
        }
        if (count($entryTokens) > 1) {
            $phraseCandidates[] = $entryTokens;
        }

        $seenPhrases = [];
        foreach ($phraseCandidates as $phraseTokens) {
            if (count($phraseTokens) < 2) {
                continue;
            }
            $key = implode("\x1e", $phraseTokens);
            if (isset($seenPhrases[$key])) {
                continue;
            }
            $seenPhrases[$key] = true;

            $sn = $this->findPhraseSnippetInDocument($document, $phraseTokens, true, 8, $hints);
            if ($sn !== null) {
                return [$sn];
            }
        }

        $words = $parsed['tokensPerWord'] !== [] ? $parsed['tokensPerWord'] : $parsed['tokens'];
        if ($entryTokens !== []) {
            $words = array_values(array_unique(array_merge($entryTokens, $words)));
        }

        $snippets = [];
        foreach (array_slice($words, 0, 3) as $word) {
            $sn = $this->findSnippetInDocument($document, $this->normalizeArabic($word), true, 8, $hints);
            if ($sn !== null) {
                $snippets[] = $sn;
            }
        }

        return $snippets;
    }

    /**
     * تظليل كلمات/جملة البحث مع مراعاة «ال» لكل كلمة.
     *
     * @param array<int, string> $tokens
     */
    public function highlightSearchTokensInText(string $text, array $tokens, bool $withAlVariant = true): string
    {
        $tokens = array_values(array_filter($tokens, fn ($t) => is_string($t) && $t !== ''));
        if ($text === '' || $tokens === []) {
            return $text;
        }

        if (count($tokens) > 1) {
            $phrasePattern = $this->buildArabicPhrasePattern($tokens, $withAlVariant);
            $text = preg_replace($phrasePattern, '<mark>$1</mark>', $text) ?? $text;
        }

        foreach ($tokens as $token) {
            $text = $this->highlightTokenInText($text, $token, $withAlVariant);
        }

        return $text;
    }

    /**
     * يتجنب إعادة تظليل نص داخل <mark> موجودة.
     */
    public function highlightTokenInText(string $text, string $normalizedToken, bool $withAlVariant = true): string
    {
        $pattern = $this->buildArabicTokenPattern($normalizedToken, $withAlVariant, true);
        $parts = preg_split('/(<mark>.*?<\/mark>)/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return preg_replace($pattern, '<mark>$1</mark>', $text) ?? $text;
        }

        foreach ($parts as $i => $part) {
            if ($part === '' || str_starts_with($part, '<mark>')) {
                continue;
            }
            $parts[$i] = preg_replace($pattern, '<mark>$1</mark>', $part) ?? $part;
        }

        return implode('', $parts);
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

            return $this->htmlToPlainText($part);
        }, $parts)));

        return $this->normalizeArabic($combined);
    }

    public function rebuildSearchText(Document $document): void
    {
        $this->rebuildDocumentIndex($document);
    }

    public function applyPhraseMatch(Builder $query, string $normalizedPhrase, bool $withAlVariant = false, ?array $parsed = null): Builder
    {
        $tokens = $parsed['tokens'] ?? $this->tokenizeArabic($normalizedPhrase);
        if ($tokens === [] && $normalizedPhrase === '') {
            return $query;
        }

        if (count($tokens) === 1) {
            return $this->applyTokenOrVariants($query, $tokens[0], $withAlVariant);
        }

        return $this->applyMultiWordPhraseMatch($query, $tokens, $withAlVariant);
    }

    protected function applyPhraseMatchCondition(Builder $query, array $parsed, bool $withAlVariant): Builder
    {
        $tokens = $parsed['tokens'] ?? [];
        if (count($tokens) === 1) {
            return $this->applyTokenOrVariants($query, $tokens[0], $withAlVariant);
        }

        return $this->applyMultiWordPhraseMatch($query, $tokens, $withAlVariant);
    }

    /**
     * مطابقة جملة متعددة الكلمات مع مراعاة صيغ «ال».
     *
     * @param array<int, string> $tokens
     */
    protected function applyMultiWordPhraseMatch(Builder $query, array $tokens, bool $withAlVariant): Builder
    {
        if ($this->useTokenIndex()) {
            foreach ($tokens as $token) {
                $query->where(function (Builder $inner) use ($token, $withAlVariant) {
                    $this->applyTokenOrVariants($inner, $token, $withAlVariant);
                });
            }
        }

        $phrases = $this->phraseVariants($tokens, $withAlVariant);
        if ($phrases === []) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $outer) use ($phrases, $table) {
            foreach ($phrases as $phrase) {
                $outer->orWhere($table . '.search_text', 'like', '%' . $this->escapeLike($phrase) . '%');
            }
        });
    }

    protected function sqlPhraseMatch(string $table, array $parsed, bool $withAlVariant, array &$bindings): string
    {
        $tokens = $parsed['tokens'] ?? [];
        if (count($tokens) === 1) {
            return $this->sqlWordVariantsMatch($table, $tokens[0], $withAlVariant, $bindings);
        }

        $phrases = $this->phraseVariants($tokens, $withAlVariant);
        if ($phrases === []) {
            return '0';
        }

        $parts = [];
        foreach ($phrases as $phrase) {
            $parts[] = $table . '.search_text LIKE ?';
            $bindings[] = '%' . $this->escapeLike($phrase) . '%';
        }

        return '(' . implode(' OR ', $parts) . ')';
    }

    protected function sqlNotPhraseMatch(string $table, array $parsed, bool $withAlVariant, array &$bindings): string
    {
        $tokens = $parsed['tokens'] ?? [];
        if ($tokens === []) {
            return '1';
        }

        if (count($tokens) === 1) {
            $variants = $withAlVariant ? $this->variantsFor($tokens[0]) : [$tokens[0]];
            $parts = [];
            foreach ($variants as $variant) {
                $parts[] = $table . '.search_words NOT LIKE ?';
                $bindings[] = $this->wholeWordLike($variant);
            }

            return '(' . implode(' AND ', $parts) . ')';
        }

        $phrases = $this->phraseVariants($tokens, $withAlVariant);
        $parts = [];
        foreach ($phrases as $phrase) {
            $parts[] = $table . '.search_text NOT LIKE ?';
            $bindings[] = '%' . $this->escapeLike($phrase) . '%';
        }

        return '(' . implode(' AND ', $parts) . ')';
    }

    /**
     * يستبعد وثائق المطابقة التامة (بكل صيغ «ال») من تبويب/مجموعة أخرى.
     */
    protected function excludePhraseMatches(Builder $query, array $parsed, bool $withAlVariant): Builder
    {
        $tokens = $parsed['tokens'] ?? [];
        if ($tokens === []) {
            return $query;
        }

        if (count($tokens) === 1) {
            $variants = $withAlVariant ? $this->variantsFor($tokens[0]) : [$tokens[0]];
            $table = $query->getModel()->getTable();

            if ($this->useTokenIndex()) {
                return $query->whereNotExists(function ($sub) use ($variants, $table) {
                    $sub->from('document_search_tokens as dst')
                        ->selectRaw('1')
                        ->whereColumn('dst.document_id', $table . '.id')
                        ->whereIn('dst.token', $variants);
                });
            }

            return $query->where(function (Builder $outer) use ($variants, $table) {
                foreach ($variants as $variant) {
                    $outer->where($table . '.search_words', 'not like', $this->wholeWordLike($variant));
                }
            });
        }

        $phrases = $this->phraseVariants($tokens, $withAlVariant);
        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $outer) use ($phrases, $table) {
            foreach ($phrases as $phrase) {
                $outer->where($table . '.search_text', 'not like', '%' . $this->escapeLike($phrase) . '%');
            }
        });
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

        if ($this->useTokenIndex()) {
            $table = $query->getModel()->getTable();

            return $query->whereExists(function ($sub) use ($variants, $table) {
                $sub->from('document_search_tokens as dst')
                    ->selectRaw('1')
                    ->whereColumn('dst.document_id', $table . '.id')
                    ->whereIn('dst.token', $variants);
            });
        }

        $column = $query->getModel()->getTable() . '.search_words';

        return $query->where(function (Builder $q) use ($variants, $column) {
            foreach ($variants as $variant) {
                $q->orWhere($column, 'like', $this->wholeWordLike($variant));
            }
        });
    }

    protected function searchWordsContainsToken(string $searchWords, string $token, bool $withAlVariant = false): bool
    {
        $haystack = ' ' . trim($searchWords) . ' ';
        foreach ($withAlVariant ? $this->variantsFor($token) : [$token] as $variant) {
            if (str_contains($haystack, ' ' . $variant . ' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, int>
     */
    protected function getMatchedTokenIndices(string $searchWords, array $tokensPerWord, bool $withAlVariant = false): array
    {
        $matched = [];
        foreach ($tokensPerWord as $idx => $token) {
            if ($this->searchWordsContainsToken($searchWords, $token, $withAlVariant)) {
                $matched[] = $idx;
            }
        }

        return $matched;
    }

    /**
     * @return array<int, int>
     */
    protected function parseScatteredTabKeyIndices(string $tabKey): array
    {
        if (preg_match('/^(?:solo|pair|set|group)-(.+)$/', $tabKey, $matches)) {
            return array_map('intval', explode('-', $matches[1]));
        }

        return [];
    }

    /**
     * يحدد تبويبات الكلمات المتفرقة: تبويب لكل مجموعة كلمات تظهر معاً في الوثيقة (2 إلى 5 كلمات)،
     * أو تبويب لكل كلمة على حدة إن لم توجد وثائق بكلمتين فأكثر معاً.
     *
     * @return array{mode: string, tabs: array<int, array<string, mixed>>}
     */
    protected function buildScatteredTabPlan(Builder $baseQuery, array $parsed, bool $withAlVariant = false): array
    {
        $tokens = $parsed['tokensPerWord'];
        if (count($tokens) <= 1) {
            return ['mode' => 'per_word', 'tabs' => [], 'word_doc_ids' => []];
        }

        if ($this->useTokenIndex()) {
            return $this->buildScatteredTabPlanViaTokenIndex($baseQuery, $parsed, $withAlVariant);
        }

        return $this->buildScatteredTabPlanLegacy($baseQuery, $parsed, $withAlVariant);
    }

    /**
     * @return array{mode: string, tabs: array<int, array<string, mixed>>, word_doc_ids?: array<int, array<int, int>>}
     */
    protected function buildScatteredTabPlanViaTokenIndex(Builder $baseQuery, array $parsed, bool $withAlVariant = false): array
    {
        $tokens = $parsed['tokensPerWord'];
        $allVariants = [];
        $variantToIndices = [];

        foreach ($tokens as $idx => $word) {
            foreach (($withAlVariant ? $this->variantsFor($word) : [$word]) as $variant) {
                $allVariants[$variant] = true;
                $variantToIndices[$variant][] = $idx;
            }
        }

        $table = $baseQuery->getModel()->getTable();
        $scopeSub = (clone $baseQuery)->select($table . '.id as document_id');

        $rows = DB::table('document_search_tokens as dst')
            ->joinSub($scopeSub, 'scoped', 'scoped.document_id', '=', 'dst.document_id')
            ->whereIn('dst.token', array_keys($allVariants))
            ->select('dst.document_id', 'dst.token')
            ->limit((int) config('document_search.scattered_plan_max_docs', 15000))
            ->get();

        if ($rows->isEmpty()) {
            return ['mode' => 'per_word', 'tabs' => [], 'word_doc_ids' => []];
        }

        return $this->assembleScatteredPlanFromMatches($tokens, $rows, $variantToIndices);
    }

    /**
     * @param \Illuminate\Support\Collection<int, object{document_id:int|string, token:string}> $rows
     * @param array<string, array<int, int>> $variantToIndices
     * @return array{mode: string, tabs: array<int, array<string, mixed>>, word_doc_ids: array<int, array<int, int>>}
     */
    protected function assembleScatteredPlanFromMatches(array $tokens, $rows, array $variantToIndices): array
    {
        $setBuckets = [];
        $wordDocIds = array_fill(0, count($tokens), []);
        $docMatchedIndices = [];

        foreach ($rows as $row) {
            $docId = (int) $row->document_id;
            foreach ($variantToIndices[$row->token] ?? [] as $idx) {
                $docMatchedIndices[$docId][$idx] = true;
                $wordDocIds[$idx][$docId] = true;
            }
        }

        foreach ($docMatchedIndices as $docId => $indexSet) {
            $matched = array_keys($indexSet);
            sort($matched);
            $setKey = implode('-', $matched);
            $setBuckets[$setKey][] = $docId;
        }

        foreach ($wordDocIds as $idx => $idSet) {
            $wordDocIds[$idx] = array_map('intval', array_keys($idSet));
        }

        return $this->finalizeScatteredPlan($tokens, $setBuckets, $wordDocIds);
    }

    /**
     * @param array<int, string> $tokens
     * @param array<string, array<int, int>> $setBuckets
     * @param array<int, array<int, int>> $wordDocIds
     */
    protected function finalizeScatteredPlan(array $tokens, array $setBuckets, array $wordDocIds): array
    {
        $hasCoOccurrence = false;
        foreach (array_keys($setBuckets) as $setKey) {
            if (str_contains($setKey, '-')) {
                $hasCoOccurrence = true;
                break;
            }
        }

        if (count($tokens) === 2 || !$hasCoOccurrence) {
            return [
                'mode' => 'per_word',
                'tabs' => [],
                'word_doc_ids' => $wordDocIds,
            ];
        }

        $multiKeys = [];
        $soloKeys = [];
        foreach (array_keys($setBuckets) as $setKey) {
            if (str_contains($setKey, '-')) {
                $multiKeys[] = $setKey;
            } else {
                $soloKeys[] = $setKey;
            }
        }

        usort($multiKeys, function (string $a, string $b) use ($setBuckets): int {
            $aSize = substr_count($a, '-') + 1;
            $bSize = substr_count($b, '-') + 1;
            if ($aSize !== $bSize) {
                return $bSize <=> $aSize;
            }

            $countCmp = count($setBuckets[$b]) <=> count($setBuckets[$a]);
            if ($countCmp !== 0) {
                return $countCmp;
            }

            return strcmp($a, $b);
        });

        usort($soloKeys, fn (string $a, string $b): int => (int) $a <=> (int) $b);

        $hideSoloTabs = count($tokens) >= 3;
        $orderedKeys = array_merge($multiKeys, $hideSoloTabs ? [] : $soloKeys);

        $tabs = [];
        foreach ($orderedKeys as $setKey) {
            $indices = array_map('intval', explode('-', $setKey));
            $tabKey = count($indices) === 1
                ? 'solo-' . $indices[0]
                : 'group-' . $setKey;
            $words = array_map(fn (int $index) => $tokens[$index], $indices);

            $tabs[] = [
                'key' => $tabKey,
                'label' => count($words) > 1 ? implode(' + ', $words) : $words[0],
                'words' => $words,
                'indices' => $indices,
                'is_multi' => count($words) > 1,
                'word_count' => count($words),
                'count' => count($setBuckets[$setKey]),
                'doc_ids' => $setBuckets[$setKey],
                'page_name' => 'page_' . str_replace('-', '_', $tabKey),
            ];
        }

        return [
            'mode' => 'grouped',
            'tabs' => $tabs,
            'use_picker' => count($tabs) > 3,
            'word_doc_ids' => $wordDocIds,
        ];
    }

    /**
     * @return array{mode: string, tabs: array<int, array<string, mixed>>, word_doc_ids?: array<int, array<int, int>>}
     */
    protected function buildScatteredTabPlanLegacy(Builder $baseQuery, array $parsed, bool $withAlVariant = false): array
    {
        $tokens = $parsed['tokensPerWord'];
        if (count($tokens) <= 1) {
            return ['mode' => 'per_word', 'tabs' => [], 'word_doc_ids' => []];
        }

        // جلب خفيف مرة واحدة: id + search_words فقط (بدون hydrate لنماذج Eloquent)
        $candidateQuery = (clone $baseQuery)->select(['documents.id', 'documents.search_words']);
        $candidateQuery->where(function (Builder $q) use ($tokens, $withAlVariant) {
            foreach ($tokens as $word) {
                $q->orWhere(function (Builder $inner) use ($word, $withAlVariant) {
                    $this->applyTokenOrVariants($inner, $word, $withAlVariant);
                });
            }
        });

        $docs = $candidateQuery->toBase()->get();
        if ($docs->isEmpty()) {
            return ['mode' => 'per_word', 'tabs' => [], 'word_doc_ids' => []];
        }

        $setBuckets = [];
        $wordDocIds = array_fill(0, count($tokens), []);

        foreach ($docs as $doc) {
            $matched = $this->getMatchedTokenIndices($doc->search_words ?? '', $tokens, $withAlVariant);
            if ($matched === []) {
                continue;
            }

            sort($matched);
            $setKey = implode('-', $matched);
            $setBuckets[$setKey][] = (int) $doc->id;
            foreach ($matched as $idx) {
                $wordDocIds[$idx][(int) $doc->id] = true;
            }
        }

        foreach ($wordDocIds as $idx => $idSet) {
            $wordDocIds[$idx] = array_map('intval', array_keys($idSet));
        }

        return $this->finalizeScatteredPlan($tokens, $setBuckets, $wordDocIds);
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
                        $this->excludePhraseMatches($inner, $parsed, $withAlVariant);
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

    public function applySelectedMatchGroupFilter(Builder $query, string $searchTerm, ?string $matchGroup, bool $withAlVariant = false): Builder
    {
        $matchGroup = trim((string) $matchGroup);
        if ($matchGroup === '') {
            return $this->applySearchResultsFilter($query, $searchTerm, $withAlVariant);
        }

        $parsed = $this->parseSearchQuery($searchTerm);
        if ($parsed['normalizedPhrase'] === '' || $parsed['singleTokenExcluded']) {
            return $query->whereRaw('1 = 0');
        }

        if ($matchGroup === 'phrase') {
            return $this->applyPhraseMatch($query, $parsed['normalizedPhrase'], $withAlVariant, $parsed);
        }

        if ($matchGroup === 'all') {
            if (empty($parsed['tokensForAll']) || count($parsed['tokens']) <= 1) {
                return $query->whereRaw('1 = 0');
            }

            return $this->excludePhraseMatches(
                $this->applyAllTokensMatch($query, $parsed['tokensForAll'], $withAlVariant),
                $parsed,
                $withAlVariant
            );
        }

        if (preg_match('/^word-(\d+)$/', $matchGroup, $matches)) {
            $idx = (int) $matches[1];
            $word = $parsed['tokensPerWord'][$idx] ?? null;
            return $word ? $this->applyTokenOrVariants($query, $word, $withAlVariant) : $query->whereRaw('1 = 0');
        }

        $scatteredPlan = count($parsed['tokensPerWord']) > 1
            ? $this->buildScatteredTabPlan(clone $query, $parsed, $withAlVariant)
            : ['mode' => 'per_word', 'tabs' => []];

        foreach (($scatteredPlan['tabs'] ?? []) as $tab) {
            if (($tab['key'] ?? '') === $matchGroup) {
                $ids = $tab['doc_ids'] ?? [];
                return empty($ids) ? $query->whereRaw('1 = 0') : $query->whereIn('documents.id', $ids);
            }
        }

        return $query->whereRaw('1 = 0');
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

        if ($this->useTokenIndex()) {
            return $this->computeTabCountsViaTokenIndex($baseQuery, $parsed, $withAlVariant);
        }

        return $this->computeTabCountsLegacy($baseQuery, $parsed, $withAlVariant);
    }

    /**
     * @return array{phrase:int,all:int,per_word:array<int,int>,unique:int}
     */
    protected function computeTabCountsViaTokenIndex(Builder $baseQuery, array $parsed, bool $withAlVariant): array
    {
        $perWordCounts = [];
        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            $perWordCounts[$idx] = $this->countDocumentsWithToken($baseQuery, $word, $withAlVariant);
        }

        $phraseCount = $this->countPhraseMatches($baseQuery, $parsed, $withAlVariant);
        $allCount = $this->countAllWordsMatches($baseQuery, $parsed, $withAlVariant);
        $uniqueCount = $this->countUniqueSearchMatches($baseQuery, $parsed, $withAlVariant);

        return [
            'phrase' => $phraseCount,
            'all' => $allCount,
            'per_word' => $perWordCounts,
            'unique' => $uniqueCount,
        ];
    }

    protected function countDocumentsWithToken(Builder $baseQuery, string $token, bool $withAlVariant): int
    {
        $query = clone $baseQuery;

        return (int) $this->applyTokenOrVariants($query, $token, $withAlVariant)->count();
    }

    protected function countPhraseMatches(Builder $baseQuery, array $parsed, bool $withAlVariant): int
    {
        $query = clone $baseQuery;

        return (int) $this->applyPhraseMatchCondition($query, $parsed, $withAlVariant)->count();
    }

    protected function countAllWordsMatches(Builder $baseQuery, array $parsed, bool $withAlVariant): int
    {
        if (empty($parsed['tokensForAll']) || count($parsed['tokens']) <= 1) {
            return 0;
        }

        $query = clone $baseQuery;
        $this->applyAllTokensMatch($query, $parsed['tokensForAll'], $withAlVariant);
        $this->excludePhraseMatches($query, $parsed, $withAlVariant);

        return (int) $query->count();
    }

    protected function countUniqueSearchMatches(Builder $baseQuery, array $parsed, bool $withAlVariant): int
    {
        $query = clone $baseQuery;

        return (int) $query->where(function (Builder $outer) use ($parsed, $withAlVariant) {
            $outer->where(function (Builder $inner) use ($parsed, $withAlVariant) {
                $this->applyPhraseMatchCondition($inner, $parsed, $withAlVariant);
            });

            if (!empty($parsed['tokensForAll']) && count($parsed['tokens']) > 1) {
                $outer->orWhere(function (Builder $inner) use ($parsed, $withAlVariant) {
                    $this->applyAllTokensMatch($inner, $parsed['tokensForAll'], $withAlVariant);
                    $this->excludePhraseMatches($inner, $parsed, $withAlVariant);
                });
            }

            foreach ($parsed['tokensPerWord'] as $word) {
                $outer->orWhere(function (Builder $inner) use ($word, $withAlVariant) {
                    $this->applyTokenOrVariants($inner, $word, $withAlVariant);
                });
            }
        })->count();
    }

    /**
     * @return array{phrase:int,all:int,per_word:array<int,int>,unique:int}
     */
    protected function computeTabCountsLegacy(Builder $baseQuery, array $parsed, bool $withAlVariant): array
    {
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
            $notPhraseCondition = $this->sqlNotPhraseMatch($subAlias, $parsed, $withAlVariant, $bindings);
            $selects[] = 'SUM(CASE WHEN ' . $allCondition . ' AND ' . $notPhraseCondition . ' THEN 1 ELSE 0 END) AS all_count';
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
        $scope = $this->resolveSearchScope($baseQuery, $searchTerm, $withAlVariant, $request);
        $parsed = $scope['parsed'];
        $counts = $scope['counts'] ?? ['phrase' => 0, 'all' => 0, 'per_word' => [], 'unique' => 0];
        $scatteredPlan = $scope['scatteredPlan'] ?? ['mode' => 'per_word', 'tabs' => []];
        $multiWord = count($parsed['tokensPerWord']) > 1;
        $activeTab = $this->resolveActiveTab($request, $parsed, $counts, $scatteredPlan);

        $eager = ['section', 'user', 'plainFieldValues.field'];
        // content يُحمَّل لصفحة النتائج فقط (6–12 وثيقة) لعرض مقتطفات دقيقة
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
                'scattered_tabs' => [],
                'scattered_mode' => 'per_word',
                'multi_word' => count($parsed['tokensPerWord']) > 1,
                'tokens' => $parsed['tokensPerWord'],
                'raw' => $searchTerm,
                'unique_total' => 0,
                'tab_counts' => $counts,
                'active_tab' => $activeTab,
                'ranked' => $this->emptyPaginator($request, 'page_ranked', $perPage),
                'ranked_groups' => [],
                'selected_match_group' => '',
                'ranked_ids' => [],
            ];
        }

        // واجهة النتائج المرتبة فقط — نتخطى بناء صفحات التبويبات القديمة (تكلفة عالية بلا فائدة)
        $rankedBuckets = $this->buildRankedBuckets(
            $baseQuery,
            $parsed,
            $counts,
            $scatteredPlan,
            $applySort,
            $withAlVariant
        );

        $selectedMatchGroup = $this->sanitizeMatchGroup(
            (string) $request->input('match_group', ''),
            $rankedBuckets
        );
        if ($selectedMatchGroup !== (string) $request->input('match_group', '')) {
            $request->merge(['match_group' => $selectedMatchGroup === '' ? null : $selectedMatchGroup]);
        }

        $rankedResults = $this->buildRankedPaginator($baseQuery, $request, $perPage, $rankedBuckets);
        $uniqueTotal = $rankedResults['paginator']->total();
        $rankedIds = $this->extractRankedDocumentIds($request, $rankedBuckets);

        $scopeCacheKey = $this->searchScopeCacheKey($baseQuery, $searchTerm, $request) . '|ranked_ids';
        $this->rankedDocumentIdsCache[$scopeCacheKey] = $rankedIds;

        $phrasePageName = $this->pageNameForTab('phrase', 0, $includeAnyTab);
        $allPageName = $this->pageNameForTab('all', 0, $includeAnyTab);
        $perWord = [];
        foreach ($parsed['tokensPerWord'] as $idx => $word) {
            $perWord[] = [
                'word' => $word,
                'page' => $this->makePaginatorFromCount($request, 'page_w' . $idx, $perPage, $counts['per_word'][$idx] ?? 0),
            ];
        }

        return [
            'phrase' => $this->makePaginatorFromCount($request, $phrasePageName, $perPage, $counts['phrase']),
            'all' => $this->makePaginatorFromCount($request, $allPageName, $perPage, $counts['all']),
            'any' => $includeAnyTab ? $this->makePaginatorFromCount($request, 'page_any', $perPage, 0) : null,
            'per_word' => $perWord,
            'scattered_tabs' => [],
            'scattered_mode' => $scatteredPlan['mode'],
            'scattered_use_picker' => ($scatteredPlan['use_picker'] ?? false) && $scatteredPlan['mode'] === 'grouped',
            'multi_word' => $multiWord,
            'tokens' => $parsed['tokensPerWord'],
            'raw' => $searchTerm,
            'unique_total' => $uniqueTotal,
            'tab_counts' => $counts,
            'active_tab' => $activeTab,
            'ranked' => $rankedResults['paginator'],
            'ranked_groups' => $rankedResults['groups'],
            'selected_match_group' => $selectedMatchGroup,
            'ranked_ids' => $rankedIds,
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
     * @return array<int, int>
     */
    protected function collectOrderedIds(Builder $query, callable $applySort): array
    {
        return array_values(array_map('intval', $applySort(clone $query)->pluck('documents.id')->all()));
    }

    /**
     * يعيد معرّفات الوثائق الظاهرة في نتائج البحث المرتبة (مع احترام match_group والفلاتر على الاستعلام الأساسي).
     *
     * @return array<int, int>
     */
    public function collectRankedDocumentIds(
        Builder $baseQuery,
        string $searchTerm,
        Request $request,
        callable $applySort,
        bool $withAlVariant = false
    ): array {
        $scope = $this->resolveSearchScope($baseQuery, $searchTerm, $withAlVariant, $request);
        $parsed = $scope['parsed'];
        if ($parsed['singleTokenExcluded'] || $parsed['normalizedPhrase'] === '' || $scope['counts'] === null) {
            return [];
        }

        $cacheKey = $this->searchScopeCacheKey($baseQuery, $searchTerm, $request) . '|ranked_ids';
        if (isset($this->rankedDocumentIdsCache[$cacheKey])) {
            return $this->rankedDocumentIdsCache[$cacheKey];
        }

        $rankedBuckets = $this->buildRankedBuckets(
            $baseQuery,
            $parsed,
            $scope['counts'],
            $scope['scatteredPlan'],
            $applySort,
            $withAlVariant
        );

        $ids = $this->extractRankedDocumentIds($request, $rankedBuckets);
        $this->rankedDocumentIdsCache[$cacheKey] = $ids;

        return $ids;
    }

    /**
     * @return array{
     *   parsed: array,
     *   counts: ?array{phrase:int,all:int,per_word:array<int,int>,unique:int},
     *   scatteredPlan: ?array<string, mixed>
     * }
     */
    protected function resolveSearchScope(
        Builder $baseQuery,
        string $searchTerm,
        bool $withAlVariant,
        Request $request
    ): array {
        $parsed = $this->parseSearchQuery($searchTerm);
        if ($parsed['singleTokenExcluded'] || $parsed['normalizedPhrase'] === '') {
            return ['parsed' => $parsed, 'counts' => null, 'scatteredPlan' => null];
        }

        $cacheKey = $this->searchScopeCacheKey($baseQuery, $searchTerm, $request);
        if (!isset($this->searchScopeCache[$cacheKey])) {
            $ttl = max(1, (int) config('document_search.scope_cache_minutes', 30));
            $persistentKey = 'doc_search_scope_v2:' . $cacheKey;

            $this->searchScopeCache[$cacheKey] = Cache::remember($persistentKey, now()->addMinutes($ttl), function () use ($baseQuery, $parsed, $withAlVariant) {
                $counts = $this->computeTabCounts(clone $baseQuery, $parsed, $withAlVariant);
                $multiWord = count($parsed['tokensPerWord']) > 1;
                $scatteredPlan = $multiWord
                    ? $this->buildScatteredTabPlan(clone $baseQuery, $parsed, $withAlVariant)
                    : ['mode' => 'per_word', 'tabs' => [], 'word_doc_ids' => []];

                return [
                    'counts' => $counts,
                    'scatteredPlan' => $scatteredPlan,
                ];
            });
        }

        return [
            'parsed' => $parsed,
            'counts' => $this->searchScopeCache[$cacheKey]['counts'],
            'scatteredPlan' => $this->searchScopeCache[$cacheKey]['scatteredPlan'],
        ];
    }

    protected function searchScopeCacheKey(Builder $baseQuery, string $searchTerm, Request $request): string
    {
        $query = clone $baseQuery;

        return md5(
            $query->toSql()
            . '|' . json_encode($query->getBindings())
            . '|' . $searchTerm
            . '|' . (string) $request->input('match_group', '')
        );
    }

    /**
     * @param array{phrase:int,all:int,per_word:array<int,int>} $counts
     * @param array<string, mixed> $scatteredPlan
     * @return array<int, array<string, mixed>>
     */
    protected function buildRankedBuckets(
        Builder $baseQuery,
        array $parsed,
        array $counts,
        array $scatteredPlan,
        callable $applySort,
        bool $withAlVariant
    ): array {
        $phraseQuery = $this->applyPhraseMatch(clone $baseQuery, $parsed['normalizedPhrase'], $withAlVariant, $parsed);

        $rankedBuckets = [];

        if ($counts['phrase'] > 0) {
            $rankedBuckets[] = [
                'key' => 'phrase',
                'label' => 'مطابقة تامة',
                'match_type' => 'exact',
                'tokens' => $parsed['tokens'],
                'word' => null,
                'ids' => $this->collectOrderedIds($phraseQuery, $applySort),
            ];
        }

        if ($scatteredPlan['mode'] === 'grouped') {
            foreach ($scatteredPlan['tabs'] as $tab) {
                if (($tab['count'] ?? 0) === 0 || empty($tab['doc_ids'])) {
                    continue;
                }

                $rankedBuckets[] = [
                    'key' => $tab['key'] ?? $tab['label'],
                    'label' => $tab['label'],
                    'match_type' => 'any',
                    'tokens' => $tab['words'] ?? [],
                    'word' => count($tab['words'] ?? []) === 1 ? ($tab['words'][0] ?? null) : null,
                    'ids' => $this->orderIdsByPreferredList($baseQuery, $tab['doc_ids'], $applySort),
                ];
            }
        } else {
            foreach ($parsed['tokensPerWord'] as $idx => $word) {
                if (($counts['per_word'][$idx] ?? 0) === 0) {
                    continue;
                }

                $preloadedIds = $scatteredPlan['word_doc_ids'][$idx] ?? null;
                if (is_array($preloadedIds)) {
                    $ids = $this->orderIdsByPreferredList($baseQuery, $preloadedIds, $applySort);
                } else {
                    $wordQuery = tap(clone $baseQuery, function (Builder $q) use ($word, $withAlVariant) {
                        $this->applyTokenOrVariants($q, $word, $withAlVariant);
                    });
                    $ids = $this->collectOrderedIds($wordQuery, $applySort);
                }

                $rankedBuckets[] = [
                    'key' => 'word-' . $idx,
                    'label' => $word,
                    'match_type' => 'any',
                    'tokens' => [$word],
                    'word' => $word,
                    'ids' => $ids,
                ];
            }
        }

        return $rankedBuckets;
    }

    /**
     * يرتّب قائمة معرّفات معروفة مسبقاً حسب ترتيب العرض دون إعادة LIKE.
     *
     * @param array<int, int> $ids
     * @return array<int, int>
     */
    protected function orderIdsByPreferredList(Builder $baseQuery, array $ids, callable $applySort): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        return array_values(array_map(
            'intval',
            $applySort((clone $baseQuery)->whereIn('documents.id', $ids))->pluck('documents.id')->all()
        ));
    }

    /**
     * يتجاهل match_group غير الموجود في النتائج الحالية (مثلاً بعد تغيير نص البحث).
     *
     * @param array<int, array<string, mixed>> $buckets
     */
    protected function sanitizeMatchGroup(string $selectedGroup, array $buckets): string
    {
        $selectedGroup = trim($selectedGroup);
        if ($selectedGroup === '') {
            return '';
        }

        foreach ($buckets as $bucket) {
            if (($bucket['key'] ?? '') === $selectedGroup && !empty($bucket['ids'])) {
                return $selectedGroup;
            }
        }

        return '';
    }

    /**
     * @param array<int, array<string, mixed>> $buckets
     * @return array<int, int>
     */
    protected function extractRankedDocumentIds(Request $request, array $buckets): array
    {
        $selectedGroup = $this->sanitizeMatchGroup((string) $request->input('match_group', ''), $buckets);
        $activeBuckets = $buckets;
        if ($selectedGroup !== '') {
            $activeBuckets = array_values(array_filter($buckets, fn (array $bucket) => ($bucket['key'] ?? '') === $selectedGroup));
        }

        $ids = [];
        foreach ($activeBuckets as $bucket) {
            foreach (($bucket['ids'] ?? []) as $id) {
                $ids[(int) $id] = true;
            }
        }

        return array_keys($ids);
    }

    /**
     * @param array<int, array<string, mixed>> $buckets
     * @return array{paginator: LengthAwarePaginator, groups: array<int, array<string, mixed>>}
     */
    protected function buildRankedPaginator(Builder $baseQuery, Request $request, int $perPage, array $buckets): array
    {
        $selectedGroup = $this->sanitizeMatchGroup((string) $request->input('match_group', ''), $buckets);
        $activeBuckets = $buckets;
        if ($selectedGroup !== '') {
            $activeBuckets = array_values(array_filter($buckets, fn (array $bucket) => ($bucket['key'] ?? '') === $selectedGroup));
        }

        $orderedEntries = [];

        foreach ($activeBuckets as $bucket) {
            foreach (($bucket['ids'] ?? []) as $id) {
                $id = (int) $id;
                if (isset($orderedEntries[$id])) {
                    continue;
                }

                $orderedEntries[$id] = [
                    'id' => $id,
                    'group_key' => $bucket['key'] ?? '',
                    'label' => $bucket['label'],
                    'match_type' => $bucket['match_type'],
                    'tokens' => $bucket['tokens'] ?? [],
                    'word' => $bucket['word'] ?? null,
                ];
            }
        }

        $orderedEntries = array_values($orderedEntries);
        $scopeIdSet = [];
        foreach ($orderedEntries as $entry) {
            $scopeIdSet[(int) $entry['id']] = true;
        }

        $pageName = 'page_ranked';
        $total = count($orderedEntries);
        $page = max(1, (int) $request->input($pageName, 1));
        $pageEntries = array_slice($orderedEntries, ($page - 1) * $perPage, $perPage);
        $pageIds = array_map(fn (array $entry) => $entry['id'], $pageEntries);

        $items = [];
        if (!empty($pageIds)) {
            $fetched = (clone $baseQuery)->whereIn('documents.id', $pageIds)->get()->keyBy('id');
            foreach ($pageEntries as $entry) {
                $document = $fetched->get($entry['id']);
                if ($document) {
                    $items[] = array_merge($entry, ['document' => $document]);
                }
            }
        }

        $groups = [];
        foreach ($buckets as $bucket) {
            $bucketIds = array_map('intval', $bucket['ids'] ?? []);
            $count = count(array_filter($bucketIds, fn (int $id) => isset($scopeIdSet[$id])));
            if ($count === 0) {
                continue;
            }

            $groups[] = [
                'key' => $bucket['key'] ?? '',
                'label' => $bucket['label'],
                'count' => $count,
                'match_type' => $bucket['match_type'],
                'active' => ($bucket['key'] ?? '') === $selectedGroup,
            ];
        }

        return [
            'paginator' => new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]),
            'groups' => $groups,
        ];
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
                        $this->excludePhraseMatches($inner, $parsed, $withAlVariant);
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

    protected function resolveActiveTab(Request $request, array $parsed, array $counts, ?array $scatteredPlan = null): string
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

        if (($scatteredPlan['mode'] ?? '') === 'grouped') {
            foreach ($scatteredPlan['tabs'] ?? [] as $scatteredTab) {
                if (($scatteredTab['count'] ?? 0) > 0) {
                    return $scatteredTab['key'];
                }
            }
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

    /**
     * وثائق ذات صلة سريعة: تطابق كلمات من العنوان/search_words داخل نفس القسم،
     * وإن نقص العدد يُكمَّل بوثائق عشوائية من القسم.
     *
     * @return \Illuminate\Support\Collection<int, Document>
     */
    public function findRelatedDocuments(Document $document, int $limit = 6)
    {
        $sectionId = (int) $document->section_id;
        $excludeId = (int) $document->id;
        $tokens = $this->relatedTopicTokens($document);

        $related = collect();

        if ($tokens !== []) {
            $candidates = Document::query()
                ->published()
                ->where('section_id', $sectionId)
                ->where('documents.id', '!=', $excludeId)
                ->where(function (Builder $q) use ($tokens) {
                    foreach ($tokens as $token) {
                        $q->orWhere('documents.search_words', 'like', $this->wholeWordLike($token));
                    }
                })
                ->select([
                    'documents.id',
                    'documents.section_id',
                    'documents.title',
                    'documents.slug',
                    'documents.excerpt',
                    'documents.published_at',
                    'documents.created_at',
                    'documents.updated_at',
                    'documents.views_count',
                    'documents.search_words',
                ])
                ->orderByDesc('documents.published_at')
                ->limit(40)
                ->get();

            $related = $candidates
                ->map(function (Document $candidate) use ($tokens) {
                    $haystack = ' ' . trim((string) $candidate->search_words) . ' ';
                    $score = 0;
                    foreach ($tokens as $token) {
                        if (str_contains($haystack, ' ' . $token . ' ')) {
                            $score++;
                        }
                    }

                    return ['document' => $candidate, 'score' => $score];
                })
                ->filter(fn (array $row) => $row['score'] > 0)
                ->sortByDesc(fn (array $row) => [$row['score'], optional($row['document']->published_at)->timestamp ?? 0])
                ->take($limit)
                ->map(fn (array $row) => $row['document'])
                ->values();
        }

        if ($related->count() < $limit) {
            $needed = $limit - $related->count();
            $filler = Document::query()
                ->published()
                ->where('section_id', $sectionId)
                ->where('documents.id', '!=', $excludeId)
                ->when($related->isNotEmpty(), fn (Builder $q) => $q->whereNotIn('documents.id', $related->pluck('id')))
                ->inRandomOrder()
                ->limit($needed)
                ->get([
                    'documents.id',
                    'documents.section_id',
                    'documents.title',
                    'documents.slug',
                    'documents.excerpt',
                    'documents.published_at',
                    'documents.created_at',
                    'documents.updated_at',
                    'documents.views_count',
                ]);

            $related = $related->concat($filler)->values();
        }

        return $related->take($limit)->values();
    }

    /**
     * @return array<int, string>
     */
    protected function relatedTopicTokens(Document $document): array
    {
        $stopWords = $this->arabicStopWords();
        $fromTitle = array_values(array_filter(
            $this->tokenizeArabic($this->normalizeArabic((string) $document->title)),
            fn (string $token) => mb_strlen($token) >= 4 && !in_array($token, $stopWords, true)
        ));

        $fromIndex = array_values(array_filter(
            preg_split('/\s+/u', trim((string) ($document->search_words ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [],
            fn (string $token) => mb_strlen($token) >= 4 && !in_array($token, $stopWords, true)
        ));

        // أولوية لكلمات العنوان ثم كلمات الفهرس
        return array_slice($this->uniqueTokensPreserveOrder(array_merge($fromTitle, $fromIndex)), 0, 5);
    }
}
